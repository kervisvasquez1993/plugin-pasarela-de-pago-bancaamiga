<?php
/*
Plugin Name: BancaAmigaApi
Plugin URI:
Description: Integracion con api para pasarela de pago en banca amiga
Version: 1.0
Author: KERVIS VASQUEZ
Author URI:
License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Registre su método de pago personalizado en WooCommerce
add_filter('woocommerce_payment_gateways', 'banca_amiga_payment_gateway');
function banca_amiga_payment_gateway($gateways)
{
    $gateways[] = 'wc_banca_amiga';
    return $gateways;
}
function cargar_script_modal_bancamiga()
{
    if (is_checkout()) {
        wp_enqueue_script('modal-bancamiga', plugins_url('/modal-bancamiga.js', __FILE__), array('jquery'), '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'cargar_script_modal_bancamiga');
add_action('woocommerce_checkout_before_order_review', 'mi_plugin_formulario_de_pago');
function  mi_plugin_formulario_de_pago()
{
    echo '<div id="order_review">Boton</div>';
}
// Incluya la clase de su método de pago personalizado
add_action('plugins_loaded', 'init_wc_banca_amiga');
function init_wc_banca_amiga()
{

    class wc_banca_amiga extends WC_Payment_Gateway
    {

        // Constructor
        public function __construct()
        {
            $this->id = 'mi_metodo_pago_banca_amiga';
            $this->method_title = __('Banca Amiga', 'woocommerce');
            $this->method_description = __('Integracion con api para pasarela de pago en banca amiga', 'woocommerce');
            $this->has_fields = true;

            // Cargue los ajustes del método de pago
            $this->init_form_fields();
            $this->init_settings();

            // Guarde los cambios de configuración
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        // Inicialice los campos del formulario
        public function init_form_fields()
        {
            $this->form_fields = array(
                'apikey' => array(
                    'title' => __('API Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Ingrese su API Key.', 'woocommerce'),
                    'default' => '',
                ),
                'headers' => array(
                    'title' => __('Tipo de Encabezado', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Ingrese el tipo de encabezado.', 'woocommerce'),
                    'default' => '',
                ),
            );
        }
      
        // get_template_part('templates/mi-metodo-pago-form');
        // Muestre los campos del método de pago en el checkout

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            $descripcion = $_POST['billing']['descripcion_compra'];
            $monto = $order->get_total();
            $url = 'https://payments3ds.bancamiga.com/sandbox/init/cdd69752-1dd2-48d7-b2f0-f3b15cb2476b';
            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer sandbox_7625372:1',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ),
                'body' => array(
                    'Descripcion' => "descripcion",
                    'Monto' => $monto,
                    "Externalid" => "A-100001",
                    "Urldone" => "http://localhost:8080/wordpress/finalizar-compra/",
                    "Urlcancel" => "https://bancamiga.com/cancel",
                    "Dni" => "V00000000",
                    "Ref" => "321654987",
                    "Name" => "kervis",
                ),
            );
            $response = wp_remote_post($url, $args);
            // var_dump( $response );
            if (is_wp_error($response)) {
                // Si se produjo un error al crear la orden de compra en el servidor externo
                wc_add_notice($response->get_error_message(), 'error');
                return;
            } else {

                // Si la orden de compra se creó correctamente en el servidor externo
                $response_body = wp_remote_retrieve_body($response);
                $data = json_decode($response_body, true);
                if ($data['status'] == 200) {
                    $mensaje_success = $data['mensaje'];
                    wc_add_notice($mensaje_success, 'success');
                    $url_pago = $data['data']['url'];
                    header("Location: $url_pago");
 
                    // Mostrar el botón con el enlace de pago
                    // wc_enqueue_js("
                    //     jQuery('#order_review').html('<a class=\'button\' href=\'$url_pago\' target=\'_blank\' id=\'completar-pago-bancamiga\'>Completar Pago</a>');
                    // ");

                    // Agregar el botón de pago después del botón de realizar pedido de WooCommerce
                    // add_action('woocommerce_review_order_after_submit', array($this, 'add_pay_button'));
                } else {
                    // Si el servidor externo devolvió un código de estado distinto de 200 OK
                    $mensaje_error = $data['mensaje'];
                    wc_add_notice($mensaje_error, 'error');
                    return;
                }
            }
        }

        // Agregar el botón de "Completar Pago"
        public function add_pay_button()
        {
            echo '<div id="order_review" style="margin-top: 20px;"><a class="button" id="abrir-modal-bancamiga" href="#">Completar Pago con Banca Amiga</a></div>';
            echo '<div id="modal-bancamiga" class="modal" style="display: none;"><div class="modal-content"><span class="close">&times;</span><p>Contenido para la ventana modal aquí.</p></div></div>';
        }
    }
}
