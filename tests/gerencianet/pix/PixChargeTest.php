<?php

class PixChargeTest extends PHPUnit\Framework\TestCase {

    private $pixClass;
    private $gatewayGerencianet;
    private $credentials;

    public function setUp(): void {
        $this->pixClass = new Pix();
        $this->gatewayGerencianet = new WC_Gerencianet_Oficial_Gateway();

        $file = file_get_contents(__DIR__.'/config.json');
        $this->credentials = json_decode($file, true);
        
        //inserindo credenciais do usuario na classe gateway
        $this->setValuesCredentials();
    }

    public function testCredentialsGnIntegration() {
        $arrayExpected = [
            'client_id' => $this->credentials['client_id_prod'],
            'client_secret' => $this->credentials['client_secret_prod'],
            'sandbox' => false
        ];

        $this->assertEquals(
            $arrayExpected, 
            $this->gatewayGerencianet->gnIntegration->get_gn_api_credentials()
        );
    }

    public function testGerencianetPayPixError() {
        $returnExpected = '{"message":"An error occurred during your request. Please, try again."}';
        $this->assertEquals($returnExpected, $this->pixClass->gerencianet_pay_pix(null,null,null));
    }

    public function testGerencianetPayPixSucess() {
        $order = wc_create_order();
        $order_id = $order->get_id();

        $address = array(
            'first_name' => '',
            'last_name'  => '',
            'company'    => '',
            'email'      => '',
            'phone'      => '',
            'address_1'  => '',
            'address_2'  => '',
            'city'       => '',
            'state'      => '',
            'postcode'   => '',
            'country'    => ''
        );

        $order->set_address( $address, 'billing' );
        //inserindo valor total do pedido
        $this->setTotalCart(00.01);

        $gateway = $this->gatewayGerencianet;
        $_POST = array(
            'cpf_cnpj' => ''
        );

        $returnContains = 'imagemQrcode';
        $returnResponse = json_decode($this->pixClass->gerencianet_pay_pix(null,$order_id,null), true);

        //testa se o QRcode foi gerado
        $this->assertContains($returnResponse[$returnContains], $returnResponse);
        //testa se o QRcode foi salvo no banco
        $this->testQRcode($returnResponse[$returnContains], $order_id);
        //testa se o txid foi salvo no banco
        $this->testTxid($returnResponse['txid'],$order_id);
    }

    private function testQRcode($QRcode, $post_id) {
        
        $QRcodeExpected = get_post_meta($post_id, 'pix_qr', true);

        $this->assertEquals($QRcodeExpected,$QRcode);
    }

    private function testTxid($txid, $post_id) {
        
        $txidExpected = get_post_meta($post_id, 'txid', true);

        $this->assertEquals($txidExpected,$txid);
    }

    private function setValuesCredentials() {
        $this->gatewayGerencianet->gnIntegration = new GerencianetIntegration(
            $this->credentials['client_id_prod'], 
            $this->credentials['client_secret_prod'], 
            $this->credentials['client_id_dev'], 
            $this->credentials['client_secret_dev'], 
            $this->credentials['sandbox'], 
            $this->credentials['payee_code']
        );
    }

    private function setTotalCart($totalValueCart) {
        global $woocommerce;

        $woocommerce->cart->set_cart_contents_total($totalValueCart);
    }
}
