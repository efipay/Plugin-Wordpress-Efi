<?php


class Gerencianet_Planos
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'adicionar_metabox_produto'));
        add_action('save_post_product', array($this, 'salvar_metabox_produto'));
        add_filter('woocommerce_get_price_html', array($this, 'custom_price_message'));
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_cart'), 10, 3);
        add_action('woocommerce_check_cart_items', array($this, 'restrict_subscription_quantity_in_cart'));

    }


    /**
     * Altera o HTML que exibe o preço do produto. Tratativa para as recorrências.
     */
    public function custom_price_message($price)
    {

        global $post;
        $product_id = $post->ID;

        if (Gerencianet_Hpos::get_meta($product_id, '_habilitar_recorrencia', true) == 'yes') {
            $recorrency = $this->get_recorrency(Gerencianet_Hpos::get_meta($product_id, '_gerencianet_interval', true));
            return $price . '<span class="price-description">' . $recorrency . '</span>';
        } else {
            return $price;
        }
    }

    /**
     * Retorna a recorrencia de um plano de assinatura. 
     */
    public function get_recorrency($recorrency)
    {
        $textafter = '';
        switch ($recorrency) {
            case '1':
                $textafter = ' / Mensal';
                break;
            case '2':
                $textafter = ' / Bimestral';
                break;
            case '3':
                $textafter = ' / Trimestral';
                break;
            case '4':
                $textafter = ' / Quadrimestral';
                break;
            case '6':
                $textafter = ' / Semestral';
                break;
            case '12':
                $textafter = ' / Anual';
                break;
        }
        return $textafter;
    }

    /*
       Valida o carrinho antes da finalização da compra
    */
    public function restrict_subscription_quantity_in_cart(){
        // Variável para verificar se já existe um plano de assinatura no carrinho
        $subscription_exists = false;

        // Percorre os itens do carrinho
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];

            // Verifica se o item no carrinho é um plano de assinatura
            $is_subscription = Gerencianet_Hpos::get_meta($product->get_id(), '_habilitar_recorrencia', true);
            if ($is_subscription === 'yes') {

                // Se um plano de assinatura já foi encontrado, ajustar a quantidade para 1
                if ($subscription_exists) {
                    wc_add_notice(__('Você só pode comprar um único plano de assinatura por pedido. A quantidade foi ajustada para 1.', 'woocommerce'), 'error');
                    WC()->cart->set_quantity($cart_item_key, 1); // Ajusta a quantidade para 1
                } else {
                    // Define a flag indicando que já existe um plano de assinatura no carrinho
                    $subscription_exists = true;

                    // Se a quantidade for maior que 1, ajusta para 1
                    if ($cart_item['quantity'] > 1) {
                        wc_add_notice(__('Você só pode comprar um único plano de assinatura por pedido. A quantidade do plano de assinatura foi ajustada para 1.', 'woocommerce'), 'error');
                        WC()->cart->set_quantity($cart_item_key, 1); // Ajusta a quantidade para 1
                    }
                }
            }
        }
    }

    /**
     * Valida se não há produto simples e assinatura no mesmo carrinho.
     */
    public function validate_cart($passed, $product_id, $quantity)
    {

        // Verificar se a validação já falhou, se sim, retornar sem fazer nada
        if (!$passed) {
            return $passed;
        }

        // Verificar se o produto atende à condição _habilitar_recorrencia === 'yes'
        $habilitar_recorrencia = Gerencianet_Hpos::get_meta($product_id, '_habilitar_recorrencia', true);

        if ($habilitar_recorrencia === 'yes') {
            // Verificar se há outros produtos no carrinho
            if (WC()->cart->get_cart_contents_count() > 0) {
                // Verificar se já há produtos com recorrência no carrinho
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    $cart_product_id = $cart_item['product_id'];
                    $cart_habilitar_recorrencia = Gerencianet_Hpos::get_meta($cart_product_id, '_habilitar_recorrencia', true);

                    // Se houver um produto com recorrência no carrinho, mostrar uma mensagem de erro
                    if ($cart_habilitar_recorrencia === 'yes') {
                        wc_add_notice('Você só pode adicionar um produto com recorrência ao carrinho.', 'error');
                        return false; // Impedir a adição do produto ao carrinho
                    } else {
                        wc_add_notice('Você não pode adicionar um produto com recorrência ao carrinho com outros produtos.', 'error');
                        return false; // Impedir a adição do produto ao carrinho
                    }
                }
            }
        } else {
            // Verificar se há produtos com recorrência no carrinho
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $cart_product_id = $cart_item['product_id'];
                $cart_habilitar_recorrencia = Gerencianet_Hpos::get_meta($cart_product_id, '_habilitar_recorrencia', true);

                // Se houver um produto com recorrência no carrinho, mostrar uma mensagem de erro
                if ($cart_habilitar_recorrencia === 'yes') {
                    wc_add_notice('Você não pode adicionar um produto com recorrência ao carrinho com outros produtos.', 'error');
                    return false; // Impedir a adição do produto ao carrinho
                }
            }
        }

        // Se chegou até aqui, significa que a validação passou
        return $passed;
    }

    /**
     * Adiciona um metabox no editor de produtos WooCommerce com um checkbox e um select condicional.
     */
    function adicionar_metabox_produto()
    {
        add_meta_box(
            'woocommerce_gerencianet_assinaturas',       // ID do metabox
            'Efí Bank - Configurações de Recorrência',          // Título do metabox
            array($this, 'show_metabox'),       // Callback para exibir o conteúdo do metabox
            'product',                               // Tipo de post (produto)
            'side',                                // Contexto onde será exibido
            'high'                                   // Prioridade de exibição
        );
    }

    /**
     * Exibe o conteúdo do metabox no editor de produtos.
     */
    function show_metabox($post)
    {
        echo '<script src="' . plugins_url("../../assets/js/assinaturas.js", plugin_dir_path(__FILE__)) . '"></script>';

        $is_subscription = Gerencianet_Hpos::get_meta($post->ID, '_habilitar_recorrencia', true);
        $interval = Gerencianet_Hpos::get_meta($post->ID, '_gerencianet_interval', true);
        $repeats = Gerencianet_Hpos::get_meta($post->ID, '_gerencianet_repeats', true);

        // Nonce field para validar ao salvar
        wp_nonce_field('recorrencia_produto_nonce', 'recorrencia_produto_nonce_field');

        // Checkbox para habilitar a recorrência
        echo '<label for="habilitar_recorrencia">';
        echo '<input type="checkbox" id="habilitar_recorrencia" name="habilitar_recorrencia" value="yes"' . checked($is_subscription, 'yes', false) . '>';
        echo ' Esse produto é um plano de assinatura</label><br><hr>';


        // Select para escolher a recorrência
        echo '<label for="gerencianet_interval">Recorrência: </label><br>
            <select id="gerencianet_interval" name="gerencianet_interval" style="width:100%;">';
        echo '<option value="1"' . selected($interval, '1', false) . '>Mensal</option>';
        echo '<option value="2"' . selected($interval, '2', false) . '>Bimestral</option>';
        echo '<option value="3"' . selected($interval, '3', false) . '>Trimestral</option>';
        echo '<option value="4"' . selected($interval, '4', false) . '>Quadrimestral</option>';
        echo '<option value="6"' . selected($interval, '6', false) . '>Semestral</option>';
        echo '<option value="12"' . selected($interval, '12', false) . '>Anual</option>';
        echo '</select>';

        echo '<br><small>Determina o intervalo, em meses, que a cobrança da assinatura deve ser gerada.</small>';

        echo '<br><hr><label for="gerencianet_repeats">Duração da assinatura: </label><br>
            <input id="gerencianet_repeats" type="number" min="1" max="120" name="gerencianet_repeats" value="' . $repeats . '" style="width:100%;">
            <br><small>Determina o número de vezes, <b>em ciclos do tempo de recorrência</b>, que a cobrança deve ser gerada. <b>Obs.: Defina como 1 caso queira que a cobrança ocorra até que o plano seja cancelado.</b></small>';
    }

    /**
     * Salva os metadados do metabox quando o produto é salvo.
     */
    function salvar_metabox_produto($post_id)
    {
        // Verifica o nonce, se o post está sendo salvo automaticamente, ou se o usuário atual tem permissão
        if (
            !isset($_POST['recorrencia_produto_nonce_field']) ||
            !wp_verify_nonce($_POST['recorrencia_produto_nonce_field'], 'recorrencia_produto_nonce') ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
            !current_user_can('edit_post', $post_id)
        ) {
            return;
        }

        // Atualiza o campo checkbox
        if (isset($_POST['habilitar_recorrencia'])) {
            Gerencianet_Hpos::update_meta($post_id, '_habilitar_recorrencia', 'yes');
            // Atualiza o campo select, se necessário
            if (isset($_POST['gerencianet_interval'])) {
                Gerencianet_Hpos::update_meta($post_id, '_gerencianet_interval', sanitize_text_field($_POST['gerencianet_interval']));
            }

            if (isset($_POST['gerencianet_repeats'])) {
                Gerencianet_Hpos::update_meta($post_id, '_gerencianet_repeats', sanitize_text_field($_POST['gerencianet_repeats']));
            }

        } else {
            Gerencianet_Hpos::update_meta($post_id, '_habilitar_recorrencia', 'no');
        }


    }

}