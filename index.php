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

        // Muestre los campos del método de pago en el checkout
        public function payment_fields()
        {
            ?>
            <p><?php _e('Cree la orden:', 'woocommerce');?></p>
            <fieldset id="wc-<?php echo esc_attr($this->id); ?>-cc-form">
                <?php

            wc_get_template('mi-metodo-pago-form.php', array(), '', plugin_dir_path(__FILE__) . 'templates/');

            ?>
            </fieldset>
            <?php
}

public function process_payment( $order_id ) {
    $order = wc_get_order( $order_id );
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
                    "Urldone" => "https://bancamiga.com/done",
                    "Urlcancel" => "https://bancamiga.com/cancel",
                    "Dni" => "V00000000",
                    "Ref" => "321654987",
                    "Name" => "kervis"
                ),
    );
    $response = wp_remote_post( $url, $args );
    // var_dump( $response );
    if ( is_wp_error( $response ) ) {
        // Si se produjo un error al crear la orden de compra en el servidor externo
        wc_add_notice( $response->get_error_message(), 'error' );
        return;
    } else {
        // Si la orden de compra se creó correctamente en el servidor externo
        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body, true );
        if ( $data['status'] == 200 ) {
            // Si el servidor externo devolvió un código de estado 200 OK
            $mensaje_success = $data['mensaje'];
            wc_add_notice( $mensaje_success, 'success' );
            $url_pago = str_replace( '{{ TOKEN }}', $data['data']['Token'], $data['data']['url'] );
            // Mostrar la ventana modal con la URL de pago
            wc_enqueue_js( "
                jQuery('#modal-pago-bancamiga').html('<p>Haga clic en el botón para completar el pago:</p><p><a class=\'button\' href=\'$url_pago\' target=\'_blank\'>Completar Pago</a></p>');
                jQuery('#modal-pago-bancamiga').dialog({
                    modal: true,
                    width: 600,
                    height: 400,
                    close: function(event, ui) {
                        // Si se cierra la ventana modal, redirigir al usuario a la página de agradecimiento de compra
                        window.location.href = '" . $this->get_return_url( $order ) . "';
                    }
                });
            " );
        } else {
            // Si el servidor externo devolvió un código de estado distinto de 200 OK
            $mensaje_error = $data['mensaje'];
            wc_add_notice( $mensaje_error, 'error' );
            return;
        }
    }
}

// Agregar el contenido de la ventana modal al pie de página
public function add_modal_content() {
    echo '<div id="modal-pago-bancamiga" title="Completar Pago"></div>';
}

// Agregar el botón de "Realizar Pedido" que abre la ventana modal
public function add_pay_button() {
    echo '<button type="submit" class="button alt" id="boton-pago-bancamiga">' . __( 'Realizar Pedido', 'woocommerce' ) . '</button>';
    wc_enqueue_js( "
        jQuery('#boton-pago-bancamiga').click(function(event) {
            event.preventDefault();
            jQuery('#order_review').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
            jQuery('form.checkout').submit();
        });
    " );
}


        // public function process_payment($order_id)
        // {
        //     $order = wc_get_order($order_id);
        //     echo $order;
        //     $descripcion = $_POST['billing']['descripcion_compra'];
        //     $monto = $order->get_total();
        //     $url = 'https://payments3ds.bancamiga.com/sandbox/init/cdd69752-1dd2-48d7-b2f0-f3b15cb2476b';
        //     $args = array(
        //         'headers' => array(
        //             'Authorization' => 'Bearer sandbox_7625372:1',
        //             'Content-Type' => 'application/x-www-form-urlencoded',
        //         ),
        //         'body' => array(
        //             'Descripcion' => "descripcion",
        //             'Monto' => $monto,
        //             "Externalid" => "A-100001",
        //             "Urldone" => "https://bancamiga.com/done",
        //             "Urlcancel" => "https://bancamiga.com/cancel",
        //             "Dni" => "V00000000",
        //             "Ref" => "321654987",
        //             "Name" => "kervis"
        //         ),
        //     );
        //     $response = wp_remote_post($url, $args);
        //     if (is_wp_error($response)) {
        //         // Si se produjo un error al crear la orden de compra en el servidor externo
        //         wc_add_notice($response->get_error_message(), 'error');
        //         return;
        //     } else {
        //         // Si la orden de compra se creó correctamente en el servidor externo
        //         $response_body = wp_remote_retrieve_body($response);
        //         $data = json_decode($response_body, true);
        //         if ($data['status'] == 200) {
        //             // Si el servidor externo devolvió un código de estado 200 OK
        //             $url_pago = str_replace('{{ TOKEN }}', $data['data']['Token'], $data['data']['url']);
        //             // Redirigir al usuario al formulario de pago en el servidor externo
        //             return array(
        //                 'result' => 'success',
        //                 'redirect' => $url_pago,
        //             );
        //         } else {
        //             //Continuación del código anterior:

        //             // Si el servidor externo devolvió un código de estado distinto de 200 OK
        //             $mensaje_error = $data['mensaje'];
        //             wc_add_notice($mensaje_error, 'error');
        //             return;
        //         }
        //     }
        // }
    }
    // // Registrar la clase de la pasarela de pago
    // add_filter('woocommerce_payment_gateways', 'agregar_pasarela_pago');
    // function agregar_pasarela_pago($methods)
    // {
    //     $methods[] = 'WC_Pasarela_Pago_External';
    //     return $methods;
    // }

}
