# Módulo de Integração Gerencianet para WooCommerce Oficial - Versão 1.0.0 #

O módulo Gerencianet para WooCommerce permite receber pagamentos por meio do checkout transparente da nossa API.
Compatível com as versões 5.x do WooCommerce.

Este é o Módulo Oficial de integração fornecido pela [Gerencianet](https://gerencianet.com.br/) para WooCommerce. Com ele, o proprietário da loja pode optar por receber pagamentos por boleto bancário, cartão de crédito e/ou Pix. Todo processo é realizado por meio do checkout transparente. Com isso, o comprador não precisa sair do site da loja para efetuar o pagamento.

Caso você tenha alguma dúvida ou sugestão, entre em contato conosco pelo site [Gerencianet](https://gerencianet.com.br/fale-conosco/).

## Instalação automática
1. Acesse o link em sua loja "Plugins" -> "Adicionar novo" -> No campo de busca, pesquise por "Woo Gerencianet Oficial" ([Link oficial do Plug-in](https://wordpress.org/plugins/woo-gerencianet-official/)).
2. Clique em "Instalar agora".
4. Após a instalação, clique em "Ativar o Plugin".
5. Configure o plugin em "WooCommerce" > "Configurações" > "Finalizar Compra" > "Gerencianet" e comece a receber pagamentos.


## Instalação manual

1. Faça o download da [última versão](auto/) do plugin.
2. Acesse o link em sua loja "Plugins" -> "Adicionar novo" -> "Fazer o upload do plugin" e envie o arquivo 'woo-gerencianet-official.zip' ou extraia o conteúdo do arquivo dentro do diretório de plugins da loja.
3. Após a instalação, clique em "Ativar o Plugin".
4. Configure o plugin em "WooCommerce" > "Configurações" > "Finalizar Compra" > "Gerencianet" e comece a receber pagamentos.


## Configuração

1. Ative o plugin.
2. Configure as credenciais de sua Aplicação Gerencianet. Para criar uma nova Aplicação, entre em sua conta Gerencianet, acesse o menu "API" e clique em "Minhas Aplicações" -> "Nova aplicação". Insira as credenciais disponíveis neste link (Client ID e Client Secret de produção e desenvolvimento) nos respectivos campos de configuração do plugin.
3. Insira o Payee Code (Identificador de Conta) de sua conta Gerencianet. Para encontrar o Payee Code, entre em sua conta Gerencianet, acesse o menu "API" e clique em "Identificador de Conta".
4. Configure as opções de pagamento que deseja receber: Boleto, Cartão de Crédito e/ou Pix.
5. Caso utilize a opção de Pix:
   1. Insira sua Chave Pix cadastrada em sua conta Gerencianet.
   2. Insira o seu certificado (arquivo .pem).
   3. Marque o campo "Validar mTLS" caso deseje utilizar a validação mTLS em seu servidor.
6. Defina se deseja aplicar desconto para pagamentos com Boleto, o modo de aplicar esse desconto e insira o número de dias corridos para vencimento.
7. Defina as instruções para pagamento no Boleto em quatro linhas de até 90 caracteres cada uma. Caso essas linhas não sejam definidas pelo lojista, será exibido no boleto as instruções padrões da Gerencianet.
8. Escolha se deseja que o plugin atualize os status dos pedidos da loja automaticamente, de acordo com as notificações de alteração do status da cobrança Gerencianet.
9. Configure se deseja ativar o Sandbox (ambiente de testes) e Debug.
10. Recomendamos que antes de disponibilizar pagamentos pela Gerencianet, o lojista realize testes de cobrança com o sandbox(ambiente de testes) ativado para verificar se o procedimento de pagamento está acontecendo conforme esperado.


## Requisitos

* Versão do PHP: 7.x
* Versão do WooCommerce: 5.x
* Versão do WordPress: 5.x
