=== WooCommerce Gerencianet Oficial ===
Contributors: Gerencianet
Tags: woocommerce, gerencianet, payment, transparent checkout, card, billet, brazil, payments brazil
Requires at least: 4.1
Tested up to: 4.3
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Receba pagamentos através de Boleto bancário e cartão de crédito em sua loja WooCommerce através da Gerencianet.

== Description ==

O módulo Gerencianet para WooCommerce permite receber pagamentos através do checkout transparente da nossa API.

= Descrição em Português =

Este é o Módulo Oficial de integração fornecido pela [Gerencianet](https://gerencianet.com.br/) para WooCommerce. Com ele, o proprietário da loja pode optar por receber pagamentos através de boleto bancário e/ou cartão de crédito. Todo processo é realizado através do checkout transparente e o comprador não precisa sair do site da loja para efetuar o pagamento.

O plugin é compatível com o plugin [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/). Com esse módulo instalado, algumas informações como "CPF", "número do endereço" e "bairro" não serão solicitados no momento do pagamento.

Caso você tenha alguma dúvida ou sugestão, entre em contato conosco através do site [Gerencianet](https://gerencianet.com.br/).

= Compatibilidade =

Compatível com as versões 2.1.x, 2.2.x, 2.3.x e 2.4.x do WooCommerce.

= Instalação =

1. Faça o download da última versão do plugin através do repositório Wordpress.org ou através do [repositório no GitHub](https://github.com/gerencianet/gn-api-woocommerce)
2. Extraia o conteúdo do arquivo 'woocommerce-gerencianet-oficial.zip' dentro do diretório '/wp-content/plugins/' da loja.
3. Configure o plugin em WooCommerce > Settings > Checkout > Gerencianet e comece a receber pagamentos.

Opcional: Instale o plugin [WooCommerce Extra Checkout Fields for Brazil](https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/) para evitar que o comprador tenha que inserir CPF, número do endereço e bairro no momento do pagamento.

= Configuração = 

1. Ative o módulo
2. Configure as credenciais de sua Aplicação Gerencianet. Para criar uma nova Aplicação, entre em sua conta Gerencianet, navegue até o menu API e clique em Minhas Aplicações -> Nova aplicação. Insira as credenciais Client ID e Client Secret de produção e desenvolvimento nos respectivos campos de configuração do plugin.
3. Insira o Payee Code (identificador) de sua conta Gerencianet.
4. Configure as opções de pagamento que deseja receber: Boleto e/ou Cartão de Crédito.
5. Defina se deseja aplicar desconto para pagamentos através de Boleto e dias corridos para vencimento.
6. Escolha se deseja que o plugin atualize o status dos pedidos da loja automaticamente, de acordo com as notificações de alteração do status da cobrança Gerencianet.
7. Configure se deseja ativar o Sandbox (ambiente de testes).
8. Recomendamos que antes de disponibilizar pagamentos através da Gerencianet, o logista realize testes de cobrança com o sandbox(ambiente de testes) ativado para verificar se está tudo conforme esperado.

= Changelog =

= 0.1.0 =
* Versão Beta disponibilizada.

== Installation ==

1. Faça o download da última versão do plugin através do repositório Wordpress.org ou através do [repositório no GitHub](https://github.com/gerencianet/gn-api-woocommerce)
2. Extraia o conteúdo do arquivo 'woocommerce-gerencianet-oficial.zip' dentro do diretório '/wp-content/plugins/' da loja.
3. Configure o plugin em WooCommerce > Settings > Checkout > Gerencianet e comece a receber pagamentos.

Opcional: Instale o plugin [WooCommerce Extra Checkout Fields for Brazil](https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/) para evitar que o comprador tenha que inserir CPF, número do endereço e bairro no momento do pagamento.

= Configuração = 

1. Ative o módulo
2. Configure as credenciais de sua Aplicação Gerencianet. Para criar uma nova Aplicação, entre em sua conta Gerencianet, navegue até o menu API e clique em Minhas Aplicações -> Nova aplicação. Insira as credenciais Client ID e Client Secret de produção e desenvolvimento nos respectivos campos de configuração do plugin.
3. Insira o Payee Code (identificador) de sua conta Gerencianet.
4. Configure as opções de pagamento que deseja receber: Boleto e/ou Cartão de Crédito.
5. Defina se deseja aplicar desconto para pagamentos através de Boleto e dias corridos para vencimento.
6. Escolha se deseja que o plugin atualize o status dos pedidos da loja automaticamente, de acordo com as notificações de alteração do status da cobrança Gerencianet.
7. Configure se deseja ativar o Sandbox (ambiente de testes).
8. Recomendamos que antes de disponibilizar pagamentos através da Gerencianet, o logista realize testes de cobrança com o sandbox(ambiente de testes) ativado para verificar se está tudo conforme esperado.

== Changelog ==

= 0.1.0 =
* Versão Beta disponibilizada.
