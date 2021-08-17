=== Woo Gerencianet Oficial - Pix, boletos e cartão ===
Contributors: Gerencianet
Tags: woocommerce, gerencianet, payment, transparent checkout, pix, Boleto, card, brazil, payments brazil
Requires at least: 5.x
Tested up to: 5.8
Stable tag: 1.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Receba pagamentos por Boleto bancário e cartão de crédito em sua loja WooCommerce com a Gerencianet.

== Suporte Técnico ==
Atenção: Para agilizar o atendimento, abra um ticket informando a falha apresentada. Você pode abrir um ticket [Clicando Aqui!](https://sistema.gerencianet.com.br/tickets/criar/)

== Descrição ==

Este é o Módulo Oficial de integração fornecido pela [Gerencianet](https://gerencianet.com.br/) para WooCommerce. Com ele, o proprietário da loja pode optar por receber pagamentos por boleto bancário, cartão de crédito e/ou Pix. Todo processo é realizado por meio do checkout transparente. Com isso, o comprador não precisa sair do site da loja para efetuar o pagamento.

Caso você tenha alguma dúvida ou sugestão, entre em contato conosco pelo site [Gerencianet](https://gerencianet.com.br/fale-conosco/).

= Requisitos =

* Versão do PHP: 7.x ou 8.x
* Versão do WooCommerce: 5.x
* Versão do WordPress: 5.x

= Instalação automática =

1. Acesse o link em sua loja "Plugins" -> "Adicionar novo" -> No campo de busca, pesquise por "Woo Gerencianet Oficial" ([Link oficial do Plug-in](https://wordpress.org/plugins/woo-gerencianet-official/)).
2. Clique em "Instalar agora".
4. Após a instalação, clique em "Ativar o Plugin".
5. Configure o plugin em "WooCommerce" > "Configurações" > "Finalizar Compra" > "Gerencianet" e comece a receber pagamentos.

= Instalação manual =

1. Faça o download da [última versão](https://downloads.wordpress.org/plugin/woo-gerencianet-official.zip) do plugin.
2. Acesse o link em sua loja "Plugins" -> "Adicionar novo" -> "Fazer o upload do plugin" e envie o arquivo 'woo-gerencianet-official.zip' ou extraia o conteúdo do arquivo dentro do diretório de plugins da loja.
3. Após a instalação, clique em "Ativar o Plugin".
4. Configure o plugin em "WooCommerce" > "Configurações" > "Finalizar Compra" > "Gerencianet" e comece a receber pagamentos.

= Configuração = 

1. Ative o plugin.
2. Configure as credenciais de sua Aplicação Gerencianet. Para criar uma nova Aplicação, entre em sua conta Gerencianet, acesse o menu "API" e clique em "Minhas Aplicações" -> "Nova aplicação". Insira as credenciais disponíveis neste link (Client ID e Client Secret de produção e desenvolvimento) nos respectivos campos de configuração do plugin.
3. Insira o Payee Code (Identificador de Conta) de sua conta Gerencianet. Para encontrar o Payee Code, entre em sua conta Gerencianet, acesse o menu "API" e clique em "Identificador de Conta".
4. Configure as opções de pagamento que deseja receber: Boleto, Cartão de Crédito e/ou Pix.
5. Caso utilize a opção de Pix:
   * Insira sua Chave Pix cadastrada em sua conta Gerencianet.
   * Insira o seu certificado (arquivo .p12 ou .pem).
   * Marque o campo "Validar mTLS" caso deseje utilizar a validação mTLS em seu servidor.
6. Defina se deseja aplicar desconto para pagamentos com Boleto, o modo de aplicar esse desconto e insira o número de dias corridos para vencimento.
7. Defina as instruções para pagamento no Boleto em quatro linhas de até 90 caracteres cada uma. Caso essas linhas não sejam definidas pelo lojista, será exibido no boleto as instruções padrões da Gerencianet.
8. Escolha se deseja que o plugin atualize os status dos pedidos da loja automaticamente, de acordo com as notificações de alteração do status da cobrança Gerencianet.
9. Configure se deseja ativar o Sandbox (ambiente de testes) e Debug.
10. Recomendamos que antes de disponibilizar pagamentos pela Gerencianet, o lojista realize testes de cobrança com o sandbox(ambiente de testes) ativado para verificar se o procedimento de pagamento está acontecendo conforme esperado.

= Changelog =

# 1.4.2
* Fix: Correção de versão da release

= 1.4.1 =
* Fix: Zip atualizado

= 1.4.0 =
* Fix: Correção na exibição do desconto

= 1.3.9 =
* Fix: Correção ao aplicar desconto

= 1.3.8 = 
* Fix: Correção compra two steps

= 1.3.7 = 
* Fix: Correção de chave no Webhook

= 1.3.6 = 
* Fix: Correção na exibição do desconto

= 1.3.5 =
* Fix: Correção no registro do desconto

= 1.3.4 = 
* Fix: Melhoria na tradução

= 1.3.3 =
* Add: Compatibilidade com wordpress 5.8
* Fix: Melhoria na tradução

= 1.3.2 =
* Fix: zip atualizado

= 1.3.1 =
* Fix: Exibição e registro dos descontos Pix

= 1.3.0 =
* Fix: Compatibilidade com PHP 8
* Fix: Adequação de funções depreciadas

= 1.2.1 =
* Add: Ajuste do header da requisição
* Fix: Compatibilidade com Brazilian Market on Woocommerce
* Fix: Certificado recriado internamente

= 1.2.0 =
* Remoção da mensagem "Selecione a bandeira do cartão"
* Correção da mensagem do label das parcelas
* Ajuste no input do cartão
* Correção na máscara do input cartão
* Validar mtls desmarcado por padrão
* Adição do endpoint de devolução do PIX
* Adicionado campo para salvar o e2eid
* Adicionada a funcionalidade de reembolso automátio [SOMENTE PARA PIX]
* Ajuste de compatibilidade com o Brazilian Market on WooCommerce [SOMENTE ONE STEP CHECKOUT]
* Correção dos dados da cobrança para o InfoAdicionais
* Adicionada a descrição do gateway de pagamento
* Correção de warning
* Meios de pagamento desabilitados por padrão
* Melhoria na tradução
* Ajuste nos textos exibidos no checkout
* Remoção consumo desnecessário updateWebhook