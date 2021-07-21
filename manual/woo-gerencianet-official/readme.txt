=== Woo Gerencianet Oficial - Pix, boletos e cartão ===
Contributors: Gerencianet
Tags: woocommerce, gerencianet, payment, transparent checkout, pix, bank slip, card, brazil, payments brazil
Requires at least: 5.x
Tested up to: 5.7
Stable tag: 1.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Receba pagamentos por Boleto bancário e cartão de crédito em sua loja WooCommerce com a Gerencianet.

== Description ==

O módulo Gerencianet para WooCommerce permite receber pagamentos por meio do checkout transparente da nossa API. Compatível com as versões 5.x do WooCommerce.

= Descrição =

Este é o Módulo Oficial de integração fornecido pela [Gerencianet](https://gerencianet.com.br/) para WooCommerce. Com ele, o proprietário da loja pode optar por receber pagamentos por boleto bancário, cartão de crédito e/ou Pix. Todo processo é realizado por meio do checkout transparente. Com isso, o comprador não precisa sair do site da loja para efetuar o pagamento.

Caso você tenha alguma dúvida ou sugestão, entre em contato conosco pelo site [Gerencianet](https://gerencianet.com.br/fale-conosco/).

= Requisitos =

* Versão do PHP: 7.x
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

= 1.1.1 =
* Correção na converção do certificado
* Melhoria na exibição de falhas da conversão

= 1.1.0 =
* Adicionado suporte ao certificado em formato .p12
* Melhoria Pix copia e cola, botão adicionado
* Identificando bandeira do cartão pelo número
* Novo arquivo de tradução gerado
* Correção chamada de textos localizados/traduzidos
* Correção cobrança do frete no PIX

= 1.0.2 =
* Exibir linha "Copia e Cola" do Pix
* Descrição da cobrança com nome da loja e número do pedido no PSP que ler o Qr Code.
* Correção de alerta de pagamentos desabilitados.
* Ícone Pix adicionado na página de checkout.
* Melhorias na tradução.

= 1.0.1 =
* Remoção de mensagem de contestação em pagamentos via cartão de crédito

= 1.0.0 =
* Adição da funcionalidade Pix.
* Atualização das versões das dependências.

= 0.7.3 =
* Add: Realiza verificação da versão do TLS do servidor.

= 0.7.2 =
* Fix: Erro na máscara de Telefone.

= 0.7.1 =
* Fix: Erro na máscara de CPF/CNPJ no checkout em um passo do plugin.
* Fix: Atualização no link do boleto gerado, agora o link encaminha para um PDF.

= 0.7.0 =
* Delete: Bandeiras jcb, aura e discover do checkout da Gerencianet.
* Add: Bandeira hipertcard no checkout da Gerencianet.
* Fix: Layout quebrado da tela de checkout tradicional da Gerencianet.

= 0.6.4 =
* Fix: Layout quebrado nos campos de detalhes da cobrança.

= 0.6.3 =
* Add: Internacionalization and translation template.

= 0.6.2 =
* Fix: Erro na máscara de CPF/CNPJ no checkout em um passo do plugin.

= 0.6.1 =
* Fix: Erros ao carregar objeto jquery da Gerencianet no checkout.

= 0.6.0 =
* Fix: Compatibilidade do módulo com WooCommerce 3.x e PHP 7.x
* Add: Atualização da identidade visual da Gerencianet.

= 0.5.3 =
* Fix: Layout quebrado da tela de checkout em um passo.
* Fix: Preenchimento automático de campos em compras exclusivas para Pessoa Física ou compras exlusivas para Pessoa Jurídica.

= 0.5.2 =
* Fix: Considera impostos do WooCommerce no boleto Gerencianet.
* Fix: Retira obrigatoriedade do campo referente a CPF nas compras de Pessoal Jurídica.

= 0.5.1 =
* Fix: Link para pagamento da cobrança.

= 0.5.0 =
* Added: Configuração das linhas de instrução presentes no Boleto bancário.
* Added: Opção da forma de aplicar desconto no boleto.

= 0.4.4 =
* Fix: Compatibilidade com versão 2.6.0 do WooCommerce.

= 0.4.3 =
* Fix: Correção layout responsivo.
* Fix: Compatibilidade.

= 0.4.2 =
* Fix: Bandeira dos cartões.
* Added: Validação da versão do PHP.

= 0.4.1 =
* Added: Opção de checkout na tela de Finalizar Compra.
* Added: Validação de credenciais na configuração do plugin.

= 0.3.1 =
* Added: Validações antes de exibir campos no formulário de pagamento e otimização no preenchimento dos dados.
* Fix: Máscara de campos obrigatórios.

= 0.3.0 =
* Modificação na atualização do status do pedido de acordo com o pagamento: A partir da versão 0.3.0, o status do pedido será modificado para "Aguardando" quando o cliente gerar a cobrança com cartão de crédito ou boleto bancário. Caso a configuração de atualização de status automática estiver ativa, quando o pagamento for confirmado o status do pedido será alterado para "Processando".

= 0.2.3 =
* Fix: especificações de mensagem de erros durante pagamento.

= 0.2.2 =
* Fix: correção de versão

= 0.2.1 =
* Fix: mensagens de erros durante pagamento.
* Fix: estilos no formulário de pagamento.

= 0.2.0 =
* Fix: erros de javascript e styles; optimização de layout de pagamento via cartão de crédito.

= 0.1.2 =
* Fix: erro de compatibilidade

= 0.1.1 =
* Versão Beta disponibilizada.
