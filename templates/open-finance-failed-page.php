<?php
/**
 * Template Name: Página Falha Open Finance
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Falha no pagamento</title>
  <style>
      body {
          background-color: #f9f9f9;
          font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
          margin: 0;
          padding: 0;
      }
      .container {
          max-width: 600px;
          background: #fff;
          margin: 10% auto;
          padding: 40px;
          text-align: center;
          border-radius: 8px;
          box-shadow: 0 0 10px rgba(0,0,0,0.1);
      }
      h1 {
          color: #333;
          margin-bottom: 20px;
      }
      p {
          color: #666;
          margin-bottom: 30px;
          line-height: 1.5;
      }
      .button {
          display: inline-block;
          background-color: #0071a1;
          color: #fff;
          text-decoration: none;
          padding: 12px 20px;
          border-radius: 4px;
          margin: 0 10px;
          transition: background-color 0.3s ease;
      }
      .button:hover {
          background-color: #005a82;
      }
  </style>
</head>
<body>
  <div class="container">
      <h1>Ops, algo deu errado!</h1>
      <p>
          <?php if ( $mensagem ) : ?>
             <?php echo esc_html( $mensagem . '.' ); ?><br>
          <?php endif; ?>
        <?php if ( $erro ) : ?>
            <?php echo esc_html( $erro . '.' ); ?><br>
        <?php endif; ?>
        <br>
        Por favor, tente novamente!
      </p>

      <div>
          <!-- Botão "Voltar à loja" -->
          <a class="button" href="<?php echo home_url(); ?>">
              Voltar à loja
          </a>
          <!-- Botão "Meus pedidos" -->
          <a class="button" 
            href="<?php echo wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ); ?>">
              Meus pedidos
          </a>
      </div>
  </div>
</body>
</html>
