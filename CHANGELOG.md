# 1.3.3
* Add: Compatibilidade com wordpress 5.8
* Fix: Melhoria na tradução

# 1.3.2
* zip atualizado

# 1.3.1
* Fix: Exibição e registro dos descontos Pix

# 1.3.0
* Fix: Compatibilidade com PHP 8
* Fix: Adequação de funções depreciadas

# 1.2.1
* Add: Ajuste do header da requisição
* Fix: Compatibilidade com Brazilian Market on Woocommerce
* Fix: Certificado recriado internamente

# 1.2.0
* Delete: Remoção da mensagem "Selecione a bandeira do cartão"
* Fix: Correção da mensagem do label das parcelas
* Fix: Ajuste no input do cartão
* Fix: Correção na máscara do input cartão
* Fix: Validar mtls desmarcado por padrão
* Add: Adição do endpoint de devolução do PIX
* Add: Adicionado campo para salvar o e2eid
* Add: Adicionada a funcionalidade de reembolso automátio [SOMENTE PARA PIX]
* Fix: Ajuste de compatibilidade com o Brazilian Market on WooCommerce [SOMENTE ONE STEP CHECKOUT]
* Fix: Correção dos dados da cobrança para o InfoAdicionais
* Add: Adicionada a descrição do gateway de pagamento
* Fix: Correção de warning
* Add: Meios de pagamento desabilitados por padrão
* Fix: Melhoria na tradução
* Add: Ajuste nos textos exibidos no checkout
* Fix: Remoção consumo desnecessário updateWebhook

# 1.1.1
* Correção na converção do certificado
* Melhoria na exibição de falhas da conversão

# 1.1.0
* Add: Adicionado suporte ao certificado em formato .p12
* Add: Melhoria Pix copia e cola, botão adicionado
* Add: Identificando bandeira do cartão pelo número
* Add: Novo arquivo de tradução gerado
* Fix: Correção chamada de textos localizados/traduzidos
* Fix: Correção cobrança do frete no PIX

# 1.0.2
* Add: Exibir linha "Copia e Cola" do Pix
* Add: Descrição da cobrança com nome da loja e número do pedido no PSP que ler o Qr Code.
* Fix: Correção de alerta de pagamentos desabilitados.
* Add: Ícone Pix adicionado na página de checkout.
* Fix: Melhorias na tradução.

# 1.0.1
* Fix: Remoção de mensagem de contestação em pagamentos via cartão de crédito

# 1.0.0
* Add: Adição da funcionalidade Pix.
* Fix: Atualização das versões das dependências.

# 0.7.3
* Fix: Realiza verificação da versão do TLS do servidor.

# 0.7.2
* Fix: Erro na máscara de Telefone.

# 0.7.1
* Fix: Erro na máscara de CPF/CNPJ no checkout em um passo do plugin.
* Fix: Atualização no link do boleto gerado, agora o link encaminha para um PDF.

# 0.7.0
* Delete: Bandeiras jcb, aura e discover do checkout da Gerencianet.
* Add: Bandeira hipertcard no checkout da Gerencianet.
* Fix: Layout quebrado da tela de checkout tradicional da Gerencianet.

# v0.6.4
* Fix: Layout quebrado nos campos de detalhes da cobrança.

# v0.6.3
* Add: Internacionalization and translation template.

# v0.6.2
* Fix: Erro na máscara de CPF/CNPJ no checkout em um passo do plugin.

# v0.6.1
* Fix: Erros ao carregar objeto jquery da Gerencianet no checkout.

# v0.6.0
* Fix: Compatibilidade do módulo com WooCommerce 3.x e PHP 7.x
* Add: Atualização da identidade visual da Gerencianet.

# v0.5.3 
* Fix: Layout quebrado da tela de checkout em um passo.
* Fix: Preenchimento automático de campos em compras exclusivas para Pessoa Física ou compras exlusivas para Pessoa Jurídica.

# v0.5.2
* Fix: Considera impostos do WooCommerce no boleto Gerencianet.
* Fix: Retira obrigatoriedade do campo referente a CPF nas compras de Pessoal Jurídica.

# v0.5.1
* Fix: Link para pagamento da cobrança.
* Added: Opção da forma de aplicar desconto no boleto.

# v0.5.0
* Added: Configuração das linhas de instrução presentes no Boleto bancário

# v0.4.4
* Fix: Compatibilidade com versão 2.6.0 do WooCommerce

# v0.4.3
* Fix: Correção layout responsivo.
* Fix: Compatibilidade.

# v0.4.2
* Fix: Bandeira dos cartões.
* Added: Validação da versão do PHP.

# v0.4.1
* Added: Opção de checkout na tela de Finalizar Compra.
* Added: Validação de credenciais na configuração do plugin.

# v0.3.1
* Added: Validações antes de exibir campos no formulário de pagamento e otimização no preenchimento dos dados.
* Fix: Máscara de campos olbrigatórios

# v0.3.0
* Modificação na atualização do status do pedido de acordo com o pagamento: A partir da versão 0.3.0, o status do pedido será modificado para "Aguardando" quando o cliente gerar a cobrança com cartão de crédito ou boleto bancário. Caso a configuração de atualização de status automática estiver ativa, quando o pagamento for confirmado o status do pedido será alterado para "Processando".

# v0.2.3
* Fix: especificações de mensagem de erros durante pagamento.

# v0.2.2
* Fix: correção de versão

# v0.2.1
* Fix: mensagens de erros durante pagamento.
* Fix: bugs no formulário de pagamento.
* Modificação: Status do pedido no WooCommerce - Quando o cliente gera um pagamento, o status do pedido no WooCommerce será atualizado para "Aguardando" e quando o pagamento for confirmado o status do pedido será alterado para "Processando".

# v0.2.0

* Fix: erros de javascript e styles
* Otimização de layout de pagamento via cartão de crédito.

# v0.1.2

* Fix: erro de compatibilidade

# v0.1.1

* Fix Style
* Fix Javascript
* Fix Compatibility

# v0.1.0

* Versão Beta
