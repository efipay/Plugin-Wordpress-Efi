<?php

class PixWebhookTest extends PHPUnit\Framework\TestCase {

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

    public function testUpdateWebhookLocalhost() {
        $credential = $this->gatewayGerencianet->gnIntegration->get_gn_api_credentials();
        $credential['pix_cert'] = Pix::getCertPath();

        $pix_key = $this->credentials['pix_key'];
        $skip_mtls = 'true';

        $url = 'http://localhost';
        $gnApi = GerencianetIntegration::update_webhook($credential, $pix_key, $skip_mtls, $url);

        //transformando o resultado em um array de strings
        $gnApi = explode('"', $gnApi);
        $resultExpected = 'webhookUrl';

        $this->assertNotContains($resultExpected, $gnApi);
    }
    
    public function testUpdateWebhookValidUrl() {
        $credential = $this->gatewayGerencianet->gnIntegration->get_gn_api_credentials();
        $credential['pix_cert'] = Pix::getCertPath();

        $pix_key = $this->credentials['pix_key'];
        $skip_mtls = 'true';

        $url = 'https://url-valida';
        $gnApi = GerencianetIntegration::update_webhook($credential, $pix_key, $skip_mtls, $url);

        //transformando o resultado em um array de strings
        $gnApi = explode('"', $gnApi);
        $resultExpected = 'webhookUrl';

        $this->assertContains($resultExpected, $gnApi);
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
}
