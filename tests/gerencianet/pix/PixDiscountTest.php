<?php

class PixDiscountTest extends PHPUnit\Framework\TestCase {

    private $pixClass;
    private $gatewayGerencianet;

    public function setUp(): void {
        $this->pixClass = new Pix();
        $this->gatewayGerencianet = new WC_Gerencianet_Oficial_Gateway();
    }

    public function testCalculatePixDiscountTotal() {
        $data = array(
            'total' => 40.00, //valor total do carrinho
            'frete' => 20.00, //valor do frete
            'taxa' => 0, //valor da taxa
            'desconto' => 10, //valor do desconto, 10%
            //aplica desconto no valor total, incluindo taxa e frete
            'tipoDesconto' => 'total'
        );

        $this->setValuesCart($data);

        $discountExpected = 54.00;
        $this->assertEquals($discountExpected, $this->gatewayGerencianet->calculatePixDiscount());
    }

    public function testCalculatePixDiscountProducts() {
        $data = array(
            'total' => 40.00, //valor total do carrinho
            'frete' => 20.00, //valor do frete
            'taxa' => 0, //valor da taxa
            'desconto' => 10, //valor do desconto, 10%
            //aplica desconto apenas no produto, excluindo taxa e frete
            'tipoDesconto' => 'products'
        );

        $this->setValuesCart($data);

        $discountExpected = 36.00;
        $this->assertEquals($discountExpected, $this->gatewayGerencianet->calculatePixDiscount());
    }

    private function setValuesCart($data) {
        global $woocommerce;

        $totalValueCart = $data['total'];
        $totalShipping = $data['frete'];
        $totalTax = $data['taxa'];
        
        //desconto Pix
        $this->gatewayGerencianet->discountPix = $data['desconto'];

        $this->gatewayGerencianet->pix_discount_shipping = $data['tipoDesconto'];

        $woocommerce->cart->set_cart_contents_total($totalValueCart);
        $woocommerce->cart->set_shipping_total($totalShipping);
        $woocommerce->cart->set_cart_contents_tax($totalTax);
    }

}
