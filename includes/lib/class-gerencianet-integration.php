<?php
/**
 * Gerencianet Integration Class.
 */

require_once 'gerencianet/autoload.php';
use Gerencianet\Gerencianet;

class Gerencianet_Integration {

	public $client_id_production;
	public $client_secret_production;
	public $client_id_development;
	public $client_secret_development;
	public $sandbox;
	public $payee_code;
	public $wcSettings;

	public function __construct(){}

	public function get_credentials( $paymentMethod ) {
		$wcSettings = maybe_unserialize( get_option( 'woocommerce_' . $paymentMethod . '_settings' ) );

		$isSandbox      = $wcSettings['gn_sandbox'] == 'yes' ? true : false;
		$gn_credentials = array(
			'client_id'     => $isSandbox ? $wcSettings['gn_client_id_homologation'] : $wcSettings['gn_client_id_production'],
			'client_secret' => $isSandbox ? $wcSettings['gn_client_secret_homologation'] : $wcSettings['gn_client_secret_production'],
			'sandbox'       => $isSandbox,
		);

		if ( $paymentMethod == GERENCIANET_PIX_ID || $paymentMethod == GERENCIANET_OPEN_FINANCE_ID ) {

			if ( $paymentMethod == GERENCIANET_PIX_ID) {
				$gn_credentials['headers']  = array( 'x-skip-mtls-checking' => $wcSettings['gn_pix_mtls'] == 'yes' ? 'false' : 'true' );
			}
			
			$certsFolder = md5($gn_credentials['client_id'].$gn_credentials['client_secret']);
			
			$certsFolder = plugin_dir_path( __FILE__ ).'../../'.$certsFolder.'/'.$paymentMethod.'/';

			
			$certificatePath = $certsFolder.$wcSettings['gn_certificate_file_name'];

			if(!file_exists($certificatePath)){
				try {
						if(!is_dir($certsFolder)){
							mkdir($certsFolder, 0777, true);
						}

					$file = fopen( $certificatePath, 'w+' );
					if ( $file ) {
						$a = fwrite( $file, $wcSettings['gn_certificate_file'] );
						$b = fclose( $file );
					}


				} catch (Error $e) {
					new gn_log('Falha ao recriar certificado');
					new gn_log($e);
					return self::result_api('<div class="error"><p><strong> Falha ao encontrar Certificado, entre em contato com o administrador da loja. </strong></div>', false);
				}
				
			}
			$gn_credentials['certificate'] = $certificatePath;

			if ( $paymentMethod == GERENCIANET_OPEN_FINANCE_ID) {
				$gn_credentials['headers']  = array( 'x-idempotency-key' => substr(uniqid(rand(), true), 0, 72) );
			}
		}
		return $gn_credentials;
	}

	public function max_installments( $total ) {
		$params = array(
			'total' => $total,
			'brand' => 'visa',
		);

		try {
			$api              = new Gerencianet( $this->get_credentials( GERENCIANET_CARTAO_ID ) );
			$installments     = $api->getInstallments( $params, array() );
			$max_installments = end( $installments['data']['installments'] )['installment'] . 'x de ' . self::formatCurrencyBRL( end( $installments['data']['installments'] )['value'] );

			return $max_installments;
		} catch ( GerencianetException $e ) {
			$errorResponse = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
			return self::result_api( $errorResponse, false );
		} catch ( Exception $e ) {
			$errorResponse = array(
				'code'    => 0,
				'message' => $e->getMessage(),
			);
			return self::result_api( $errorResponse, false );
		}
	}

	public function get_installments( $total, $brand ) {
		$params = array(
			'total' => $total,
			'brand' => $brand,
		);

		try {
			$api          = new Gerencianet( $this->get_credentials( GERENCIANET_CARTAO_ID ) );
			$installments = $api->getInstallments( $params, array() );

			return self::result_api( $installments, true );
		} catch ( GerencianetException $e ) {
			$errorResponse = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
			return self::result_api( $errorResponse, false );
		} catch ( Exception $e ) {
			$errorResponse = array(
				'code'    => 0,
				'message' => $e->getMessage(),
			);
			return self::result_api( $errorResponse, false );
		}
	}

	public function one_step_billet( $order_id, $items, $shipping, $notification_url, $customer, $expirationDate, $discount = false ) {
		$payment = array(
			'banking_billet' => array(
				'expire_at' => $expirationDate,
				'customer'  => $customer,
			),
		);

		if(isset($discount['value']) && $discount['value'] > 0){
				$discount['value']                     = intval( $discount['value'] );
				$payment['banking_billet']['discount'] = $discount;
		}

		$body = array(
			'items'    => $items,
			'metadata' => array(
				'custom_id'        => strval( $order_id ),
				'notification_url' => $notification_url,
			),
			'payment'  => $payment,
		);

		if ( $shipping ) {
			$body ['shippings'] = $shipping;
		}

		try {
			$api      = new Gerencianet( $this->get_credentials( GERENCIANET_BOLETO_ID ) );
			$response = $api->createOneStepCharge( array(), $body );
			return self::result_api( $response, true );
		} catch ( GerencianetException $e ) {
			$errorResponse = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
			return self::result_api( $errorResponse, false );
		} catch ( Exception $e ) {
			$errorResponse = array(
				'message' => $e->getMessage(),
			);
			return self::result_api( $errorResponse, false );
		}
	}

	public function cancel_charge( $charge_id ) {
		$params = array( 'id' => $charge_id );

		try {
			$api    = new Gerencianet( $this->get_credentials( GERENCIANET_BOLETO_ID ) );
			$charge = $api->cancelCharge( $params, array() );

			return self::result_api( $charge, true );
		} catch ( GerencianetException $e ) {
			$errorResponse = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
			return self::result_api( $errorResponse, false );
		} catch ( Exception $e ) {
			$errorResponse = array(
				'message' => $e->getMessage(),
			);
			return self::result_api( $errorResponse, false );
		}
	}

	public function pay_pix( $body ) {
		$response = false;
		try {
			$api      = new Gerencianet( $this->get_credentials( GERENCIANET_PIX_ID ) );
			$data     = $api->pixCreateImmediateCharge( array(), $body );
			$response = true;
		} catch ( GerencianetException $e ) {
			$data = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
		} catch ( Exception $e ) {
			$data = array( 'message' => $e->getMessage() );
		}
		return self::result_api( $data, $response );
	}

	public function generate_qrcode( $locationId ) {
		$response = false;
		$params   = array( 'id' => $locationId );

		try {
			$api      = new Gerencianet( $this->get_credentials( GERENCIANET_PIX_ID ) );
			$data     = $api->pixGenerateQRCode( $params, array() );
			$response = true;
		} catch ( GerencianetException $e ) {
			$data = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
		} catch ( Exception $e ) {
			$data = array( 'message' => $e->getMessage() );
		}
		return self::result_api( $data, $response );
	}

	public function update_webhook( $pix_key, $url ) {
		$response = false;
		$params   = array( 'chave' => $pix_key );
		$credentials = $this->get_credentials( GERENCIANET_PIX_ID );

		try {
			$api      = new Gerencianet( $credentials );
			$body     = array( 'webhookUrl' => strval( $url ).'?hmac='.md5($credentials['client_id']).'&ignore=' );
			$data     = $api->pixConfigWebhook( $params, $body );
			$response = true;
		} catch ( GerencianetException $e ) {
			$data = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
		} catch ( Exception $e ) {
			$data = array( 'message' => $e->getMessage() );
		}

		return self::result_api( $data, $response );
	}

	public function one_step_card( $order_id, $items, $shipping, $notification_url, $customer, $paymentToken, $installments, $billingAddress, $discount = false ) {
		$body = array(
			'items'    => $items,
			'metadata' => array(
				'custom_id'        => strval( $order_id ),
				'notification_url' => $notification_url,
			),
			'payment'  => array(
				'credit_card' => array(
					'customer'        => $customer,
					'installments'    => $installments,
					'billing_address' => $billingAddress,
					'payment_token'   => $paymentToken,
				),
			),
		);

		if ( $discount ) {
			$body['payment']['credit_card']['discount'] = $discount;
		}

		if ( $shipping ) {
			$body ['shippings'] = $shipping;
		}

		try {
			$api         = new Gerencianet( $this->get_credentials( GERENCIANET_CARTAO_ID ) );
			$card_charge = $api->createOneStepCharge( array(), $body );
			return self::result_api( $card_charge, true );
		} catch ( GerencianetException $e ) {
			$errorResponse = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
			return self::result_api( $errorResponse, false );
		} catch ( Exception $e ) {
			$errorResponse = array(
				'message' => $e->getMessage(),
			);
			return self::result_api( $errorResponse, false );
		}
	}

	public function getNotification( $paymentMethod, $notificationToken ) {
		$params = array(
			'token' => $notificationToken,
		);

		try {
			$api          = new Gerencianet( $this->get_credentials( $paymentMethod ) );
			$notification = $api->getNotification( $params, array() );

			return self::result_api( $notification, true );
		} catch ( GerencianetException $e ) {
			$errorResponse = array(
				'message' => 'Error retrieving notification: ' . $notificationToken,
			);
			return self::result_api( $errorResponse, false );
		} catch ( Exception $e ) {
			$errorResponse = array(
				'message' => 'Error retrieving notification: ' . $notificationToken,
			);
			return self::result_api( $errorResponse, false );
		}
	}

	public function pix_refund( $order_id, $amount = null ) {
		$order = new WC_Order( $order_id );

		if ( ! $order ) {
			new gn_log( "Pedido #{$order_id} não encontrado" );
			return self::result_api( "Pedido #{$order_id} não encontrado", false );
		}

			$e2eid = Hpos_compatibility::get_meta( $order_id, '_gn_pix_E2EID' );
			$txid  = Hpos_compatibility::get_meta( $order_id, '_gn_pix_txid' );

		if ( isset( $e2eid ) && $e2eid != '' ) {

			if ( ! is_null( $amount ) ) {
				$value = str_replace( ',', '.', $amount );
			}

			new gn_log( "Iniciando reembolso de R$ {$value} do pedido {$order_id} | TXID: {$txid} | E2EID: {$e2eid}" );

			$params = array(
				'e2eId' => $e2eid,
				'id'    => $this->generateRandomId(),
			);

			$body = array(
				'valor' => $value,
			);

			try {
				$api = new Gerencianet( $this->get_credentials( GERENCIANET_PIX_ID ) );
				$pix = $api->pixDevolution( $params, $body );
				$order->update_status( 'refund' );
				new gn_log( 'Devolução concluída com sucesso!' );

				return self::result_api( true, true );
			} catch ( Exception $e ) {
				return self::result_api( false, false );
			}
		} elseif ( isset( $txid ) ) {
			new gn_log( "Tentativa de reembolso sem e2eid pedido {$order_id} valor {$amount}" );
			return self::result_api( false, false );
		} else {
			new gn_log( 'Não foi encontrado E2EID ou TXID nesse pedido. Ele pode ter sido pago por outro meio de pagamento.' );
			return self::result_api( false, false );
		}
	}

	public function result_api( $result, $success ) {
		if ( $success ) {
			return json_encode( $result );
		} else {
			if ( isset( $result['code'] ) ) {
				$messageShow = $this->getErrorMessage( intval( $result['code'] ) );
			} else {
				if ( isset( $result['message'] ) ) {
					$messageShow['message'] = $result['message'];
					$messageShow['code']    = 0;
				} else {
					$messageShow = $this->getErrorMessage( 4699999 );
				}
			}

			$errorResponse = array(
				'code'    => $messageShow['code'],
				'message' => $messageShow['message'],
			);
			new gn_log( $errorResponse );
			throw new Exception( $errorResponse['message'], 1 );
		}
	}

	public function generateRandomId() {
		$length           = 6;
		$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		$randomString     = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
		}
		return $randomString;
	}

	public function getErrorMessage( $error_code ) {
		// $message = 'O campo ' . $this->getFieldName( $property ) . ' não está preenchido corretamente.';
		$message = array();
		try {
			$messagesJson = file_get_contents( plugin_dir_path( __FILE__ ) . '/gerencianet/errors.json' );
			$messages     = json_decode( $messagesJson, true );

			$messageIndex       = array_search( $error_code, array_column( $messages, 'code' ) );
			$message['message'] = $messages[ $messageIndex ]['message'];
			$message['code']    = $messages[ $messageIndex ]['code'];
		} catch ( \Throwable $th ) {
			new gn_log( $th );
			$message['message'] = __( 'Ocorreu um erro ao tentar realizar a sua requisição. Entre em contato com o proprietário da loja.', Gerencianet_I18n::getTextDomain() );
			$message['code']    = 0;
		}
		return $message;
	}

	public function formatMoney( $value, $gnFormat ) {
		$cleanString       = preg_replace( '/([^0-9\.,])/i', '', $value );
		$onlyNumbersString = preg_replace( '/([^0-9])/i', '', $value );

		$separatorsCountToBeErased = strlen( $cleanString ) - strlen( $onlyNumbersString ) - 1;

		$stringWithCommaOrDot     = preg_replace( '/([,\.])/', '', $cleanString, $separatorsCountToBeErased );
		$removedThousendSeparator = preg_replace( '/(\.|,)(?=[0-9]{3,}$)/', '', $stringWithCommaOrDot );

		if ( $gnFormat ) {
			return (int) ( ( (float) str_replace( ',', '.', $removedThousendSeparator ) ) * 100 );
		} else {
			return ( (float) str_replace( ',', '.', $removedThousendSeparator ) );
		}
	}

	public static function formatCurrencyBRL( $value ) {
		$formated = 'R$' . number_format( $value / 100, 2, ',', '.' );

		return $formated;
	}

	public function get_participants() {
		$response = false;
		try {
			$api      = new Gerencianet( $this->get_credentials( GERENCIANET_OPEN_FINANCE_ID ) );
			$data     = $api->ofListParticipants();
			$arrayParticipantes = $data['participantes'];
			$identificadorEfi =  "ebbed125-5cd7-42e3-965d-2e7af8e3b7ae";
			$objEfi = null;
			foreach($arrayParticipantes as $participante) {
				if($participante["identificador"] === $identificadorEfi){
					$objEfi = $participante;
					break;
				}
			}
			$indice = array_search($objEfi, $arrayParticipantes);
			if($indice !== false) {
				array_splice($arrayParticipantes, $indice, 1);
				array_splice($arrayParticipantes, 0, 0 , (object) array($objEfi));
			}
			$response = true;
		} catch ( GerencianetException $e ) {
			$data = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
		} catch ( Exception $e ) {
			$data = array( 'message' => $e->getMessage() );
		}

		return self::result_api( $arrayParticipantes, $response );
	}

	public function pay_open_finance($body) {
		$response = false;
		new gn_log($body);
		try {
			$api      = new Gerencianet( $this->get_credentials( GERENCIANET_OPEN_FINANCE_ID ) );
			$data     = $api->ofStartPixPayment($params = [], $body);
			$response = true;
		} catch ( GerencianetException $e ) {
			$data = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
		} catch ( Exception $e ) {
			$data = array( 'message' => $e->getMessage() );
		}
		new gn_log($data);
		return self::result_api( $data, $response );
	}

	public function update_webhook_open_finance( $url, $redirectUrl ) {
		$response = false;

		try {
			$credentials = $this->get_credentials( GERENCIANET_OPEN_FINANCE_ID );
			$api      = new Gerencianet($credentials);
			$body     = array( 
				'webhookURL' => strval( $url ), 
				'redirectURL' => $redirectUrl,
				'webhookSecurity'=> array(
					'type' => 'hmac',
					'hash' =>  md5($credentials['client_id'])
  				),
			);
			$data     = $api->ofConfigUpdate($params = [], $body);
			$response = true;
		} catch ( GerencianetException $e ) {
			$data = array(
				'code'    => $e->getCode(),
				'error'   => $e->error,
				'message' => $e->errorDescription,
			);
		} catch ( Exception $e ) {
			$data = array( 'message' => $e->getMessage() );
		}

		return self::result_api( $data, $response );
	}

    public function open_finance_refund( $order_id, $amount = null ) {
		$order = new WC_Order( $order_id );

		if ( ! $order ) {
			new gn_log( "Pedido #{$order_id} não encontrado" );
			return self::result_api( "Pedido #{$order_id} não encontrado", false );
		}

			$e2eid = Hpos_compatibility::get_meta( $order_id, '_gn_open_finance_E2EID' );
			$identificadorPagamento  = Hpos_compatibility::get_meta( $order_id, '_gn_of_identificador_pagamento' );

		if ( isset( $e2eid ) && $e2eid != '' ) {

			if ( ! is_null( $amount ) ) {
				$value = str_replace( ',', '.', $amount );
			}

			new gn_log( "Iniciando reembolso de R$ {$value} do pedido {$order_id} | Identificador do Pagamento: {$identificadorPagamento} | E2EID: {$e2eid}" );

			$params = array(
				'identificadorPagamento' => $identificadorPagamento,
			);

			$body = array(
				'valor' => $value,
			);

			try {
				$api = new Gerencianet( $this->get_credentials( GERENCIANET_OPEN_FINANCE_ID ) );
				$devolution = $api->ofDevolutionPix( $params, $body );
				new gn_log( 'Devolução em processamento!' );

				return self::result_api( true, true );
			} catch ( Exception $e ) {
				return self::result_api( false, false );
			}
		} elseif ( isset( $identificadorPagamento ) ) {
			new gn_log( "Tentativa de reembolso sem e2eid pedido {$order_id} valor {$amount}" );
			return self::result_api( false, false );
		} else {
			new gn_log( 'Não foi encontrado E2EID ou Identificador de Pagamento nesse pedido. Ele pode ter sido pago por outro meio de pagamento.' );
			return self::result_api( false, false );
		}
	}

}
