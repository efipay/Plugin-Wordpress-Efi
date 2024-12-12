<?php

// EXAMPLES
// Append an entry to the wp-content/plugin.log file.
// gn_log( 'Something happened.' );
// Append an array entry to the wp-content/plugin.log file.
// gn_log( ['new_user' => 'benmarshall' ] );
// Write an entry to the wp-content/plugin.log file, deleting the existing entries.
// gn_log( 'Awesome sauce.', 'w' );
// Append an entry to a different log file in the wp-content directory.
// gn_log( 'Simple stuff.', 'a', 'simple-stuff' );



if ( ! function_exists( 'gn_log' ) ) {
	function gn_log( $entry, $payment_method = null) {

		// Get WordPress WP-CONTENT directory.
		$upload_dir = WP_CONTENT_DIR;

		switch ($payment_method) {
			case GERENCIANET_BOLETO_ID:
				$fileName = 'efi-boletos.log';
				break;
			case GERENCIANET_CARTAO_ID:
				$fileName = 'efi-cartao.log';
				break;
			case GERENCIANET_PIX_ID:
				$fileName = 'efi-pix.log';
				break;
			case GERENCIANET_OPEN_FINANCE_ID:
				$fileName = 'efi-open-finance.log';
				break;
			case GERENCIANET_ASSINATURAS_BOLETO_ID:
				$fileName = 'efi-assinaturas-boleto.log';
				break;
			case GERENCIANET_ASSINATURAS_CARTAO_ID:
				$fileName = 'efi-assinaturas-cartao.log';
				break;
			default:
				$fileName = 'gerencianet.log';
				break;
		}
		write_data($upload_dir, $fileName, $entry);
	}

	function write_data($upload_dir, $fileName, $entry, $mode = 'a'){
		// Write the log file.
		$filePath  = $upload_dir . '/' . $fileName;

		// Criar arquivo de versões
		create_versions_file($upload_dir);

		if (is_array($entry)) {
			// Se for um array, converta para string antes de salvar
			$entry = print_r($entry, true); 
		} else {
			// Caso contrário, apenas trate como string
			$entry = (string) $entry;
		}

		$fileName  = fopen( $filePath, $mode );
		$bytes = fwrite( $fileName, "------------------- \n" );
		$bytes = fwrite( $fileName, current_time( 'mysql' ) . ' Efi-Log:: ' . $entry . "\n" );
		$bytes = fwrite( $fileName, "------------------- \n" );
		fclose( $fileName );
		return $bytes;
	}

	function get_active_plugins() {
		// Obtém a lista de plugins ativos
		$active_plugins = get_option('active_plugins');
		
		// Se não houver plugins ativos, retorna uma string vazia
		if (!$active_plugins) {
			return '';
		}
		
		// Array para armazenar os nomes dos plugins
		$plugins = [];
		
		// Percorre a lista de plugins ativos e adiciona ao array
		foreach ($active_plugins as $plugin) {
			$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
			$plugins[] = $plugin_data['Name'] . ' (' . $plugin_data['Version'] . ')';
		}
		
		// Junta os nomes dos plugins em uma string com quebras de linha
		return implode("\n", $plugins);
	}

	function create_versions_file($upload_dir){
		// Atualiza arquivo de versões
		$textToSave = "---------------------------------- \n";
		$textToSave .= " VERSÃO DO PHP: ".phpversion();
		$textToSave .= "\n VERSÃO DO WORDPRESS: ".get_bloginfo("version");
		$textToSave .= "\n VERSÃO DO PLUGIN EFÍ: ".GERENCIANET_OFICIAL_VERSION;
		$textToSave .= "\n VERSÃO DO WOOCOMMERCE: ".WC()->version;
		$textToSave .= "\n --------- PLUGINS ATIVOS --------- ";
		$textToSave .= "\n".get_active_plugins();
		$textToSave .= "\n ---------------------------------- \n";
		$fileNameVersions  = fopen( $upload_dir . '/efi-versions.log', 'w' );
		fwrite( $fileNameVersions, $textToSave );
		fclose( $fileNameVersions );
	}
}
