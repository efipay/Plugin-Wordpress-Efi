<?php


class Gerencianet_Assinaturas
{

    public function __construct()
    {
        add_action('init', array($this, 'register_efi_assinaturas_post_type'), 0);
        add_filter('manage_efi_assinaturas_posts_columns', array($this, 'adicionar_colunas_efi_assinaturas'));
        add_action('manage_efi_assinaturas_posts_custom_column', array($this, 'exibir_meta_dados_colunas_efi_assinaturas'), 10, 2);
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'add_sub_metabox'));
        add_action('admin_enqueue_scripts', array($this, 'load_cancel_script'));
        add_action('wp_ajax_woocommerce_gerencianet_cancel_subscription', array($this, 'woocommerce_gerencianet_cancel_subscription'));
        add_action('wp_ajax_nopriv_woocommerce_gerencianet_cancel_subscription', array($this, 'woocommerce_gerencianet_cancel_subscription'));
        add_action('edit_form_after_title', array($this, 'adiciona_tag_status'));
        add_action('woocommerce_order_details_after_order_table', array($this, 'adiciona_tag_status_cliente'));
        add_action('woocommerce_order_status_cancelled', array($this, 'cancela_assinatura_efi'), 10, 1);
        add_action('woocommerce_order_status_changed', array($this, 'verificar_status_assinatura_cancelada'), 10, 4);
    }

    //Não permite alterar o status de uma assinatura após ela ser cancelada.
    public function verificar_status_assinatura_cancelada($order_id, $old_status, $new_status, $order)
    {
        $subs_id = Gerencianet_Hpos::get_meta($order_id, '_subscription_id', true);
        $subscription_id = Gerencianet_Hpos::get_meta($subs_id, '_id_da_assinatura', true);
        $payment_method = Gerencianet_Hpos::get_meta($subs_id, '_subs_payment_method', true);

        if (isset($payment_method) && ($payment_method == GERENCIANET_ASSINATURAS_BOLETO_ID || $payment_method == GERENCIANET_ASSINATURAS_CARTAO_ID)) {
            if ($old_status == 'cancelled') {
                $order->set_status('cancelled');
                $order->save();
            }
        }
    }

    //Ao cancelar o status do pedido, cancelar a assinatura também na Efí
    public function cancela_assinatura_efi($order_id)
    {

        $subs_id = Gerencianet_Hpos::get_meta($order_id, '_subscription_id', true);
        $subscription_id = Gerencianet_Hpos::get_meta($subs_id, '_id_da_assinatura', true);
        $payment_method = Gerencianet_Hpos::get_meta($subs_id, '_subs_payment_method', true);

        if (isset($payment_method) && ($payment_method == GERENCIANET_ASSINATURAS_BOLETO_ID || $payment_method == GERENCIANET_ASSINATURAS_CARTAO_ID)) {
            $gerencianetSDK = new Gerencianet_Integration();
            $response = $gerencianetSDK->get_subscription($payment_method, $subscription_id);

            if (isset($response)) {
                $status = json_decode($response, true);
                if ($status['data']['status'] != 'canceled' && $status['data']['status'] != 'cancelled') {
                    $gerencianetSDK->cancel_subscription($payment_method, $subscription_id);
                }
            }
        }
    }

    public function adiciona_tag_status($post)
    {
        if ('efi_assinaturas' === get_post_type()) {
            $this->load_cancel_script_view_order();
            include dirname(__FILE__) . '/../../../templates/subscription-charges.php';
        }
    }

    public function adiciona_tag_status_cliente($post)
    {
        $type = Gerencianet_Hpos::get_meta($post, '_is_subscription', true);
        if (isset($type) && $type == "yes") {
            $this->load_cancel_script_view_order();
            include dirname(__FILE__) . '/../../../templates/subscription-charges-customer.php';
        }
    }

    // Cancela uma assinatura na Efí se a mesma for cancelada a partir da página personalizada.
    public function woocommerce_gerencianet_cancel_subscription()
    {
        $subs_id = isset($_POST['subs_id']) ? sanitize_text_field($_POST['subs_id']) : '';
        $order_id = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : '';

        if ($subs_id != '' && $order_id != '') {
            $subscription_id = Gerencianet_Hpos::get_meta($subs_id, '_id_da_assinatura', true);
            $payment_method = Gerencianet_Hpos::get_meta($subs_id, '_subs_payment_method', true);
            $gerencianetSDK = new Gerencianet_Integration();
            $response = $gerencianetSDK->cancel_subscription($payment_method, $subscription_id);

            if (isset($response)) {
                $order = wc_get_order($order_id);
                $status = json_decode($response, true);
                if ($status['code'] == 200) {
                    $order->update_status('cancelled');
                }
            }
        }
    }


    public function register_efi_assinaturas_post_type()
    {
        $labels = array(
            'name' => 'Assinaturas',
            'singular_name' => 'Assinatura',
            'menu_name' => 'Assinaturas',
            'name_admin_bar' => 'Assinatura',
            'archives' => 'Item Archives',
            'attributes' => 'Item Attributes',
            'parent_item_colon' => 'Parent Item:',
            'all_items' => 'Efí - Assinaturas',
            'add_new_item' => 'Adicionar Nova Assinatura',
            'add_new' => 'Adicionar Nova',
            'new_item' => 'Nova Assinatura',
            'edit_item' => 'Editar Assinatura',
            'update_item' => 'Atualizar Assinatura',
            'view_item' => 'Ver Assinatura',
            'view_items' => 'Ver Assinaturas',
            'search_items' => 'Procurar Assinatura',
            'not_found' => 'Não encontrado',
            'not_found_in_trash' => 'Não encontrado no Lixo',
            'featured_image' => 'Imagem Destacada',
            'set_featured_image' => 'Definir imagem destacada',
            'remove_featured_image' => 'Remover imagem destacada',
            'use_featured_image' => 'Usar como imagem destacada',
            'insert_into_item' => 'Inserir na assinatura',
            'uploaded_to_this_item' => 'Carregado para esta assinatura',
            'items_list' => 'Lista de assinaturas',
            'items_list_navigation' => 'Navegação na lista de assinaturas',
            'filter_items_list' => 'Filtrar lista de assinaturas',
        );

        $args = array(
            'label'                 => 'Efí - Assinaturas',
            'description'           => 'Efí - Assinaturas',
            'labels'                => $labels,
            'supports'              => array('title'),
            'hierarchical'          => true,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'menu_position'         => 5,
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'rewrite'               => false,
            'capability_type'       => 'page',
            'map_meta_cap' => true,
        );

        register_post_type('efi_assinaturas', $args);
    }

    public function adicionar_colunas_efi_assinaturas($columns)
    {

        $newColumns['id_da_assinatura'] = "ID Assinatura";
        $newColumns['title'] = "Cliente";
        $newColumns['status'] = "Status";
        $newColumns['plano'] = "Plano";
        $newColumns['periodo_de_teste'] = "Período de teste";
        $newColumns['data_de_inicio'] = "Início";
        $newColumns['data_fim'] = "Fim";
        $newColumns['date'] = "Data";

        return $newColumns;
    }

    // Exibe os valores de meta data nas novas colunas para 'efi_assinaturas'
    public function exibir_meta_dados_colunas_efi_assinaturas($column, $post_id)
    {
        echo Gerencianet_Hpos::get_meta($post_id, $column, true);
    }

    public function add_sub_metabox($order)
    {

        $subscription_id = Gerencianet_Hpos::get_meta($order->get_id(), '_subscription_id', true);
        if (isset($subscription_id) && $subscription_id != '') {
?>
            <div class="subscription">
                <?php echo '<a href="' . get_edit_post_link($subscription_id) . '" style="background: #EB6608; color:#ffff; border:none;" class="button">'; ?>
                <span class="dashicons dashicons-visibility" style="margin-top:4px;"></span>
                Visualizar Assinatura
                </a>
            </div>
<?php
        }
    }

    function load_cancel_script($hook_suffix)
    {
        if ('post.php' == $hook_suffix) {
            wp_enqueue_script('wc-gerencianet-edit-sub', plugins_url('../../assets/js/cancelSubscription.js', plugin_dir_path(__FILE__)), array('jquery'));
            wp_localize_script(
                'wc-gerencianet-edit-sub',
                'woocommerce_gerencianet_api',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'security' => wp_create_nonce('woocommerce_gerencianet'),
                    'loading_img' => GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/loading.gif'
                )
            );
        }
    }

    function load_cancel_script_view_order()
    {
        wp_enqueue_script('wc-gerencianet-edit-sub', plugins_url('../../assets/js/cancelSubscription.js', plugin_dir_path(__FILE__)), array('jquery'));
        wp_localize_script(
            'wc-gerencianet-edit-sub',
            'woocommerce_gerencianet_api',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'security' => wp_create_nonce('woocommerce_gerencianet'),
                'loading_img' => GERENCIANET_OFICIAL_PLUGIN_URL . 'assets/img/loading.gif'
            )
        );
    }
}
