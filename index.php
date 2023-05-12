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
            <p><?php _e('Ingrese los detalles de su tarjeta de pago internacional:', 'woocommerce');?></p>
            <fieldset id="wc-<?php echo esc_attr($this->id); ?>-cc-form">
                <?php

            wc_get_template('mi-metodo-pago-form.php', array(), '', plugin_dir_path(__FILE__) . 'templates/');

            ?>
            </fieldset>
            <?php
}

        // Procese el pago y devuelva el resultado
        public function process_payment($order_id)
        {
            global $woocommerce;
            $order = wc_get_order($order_id);
            // Verificar que se hayan enviado todos los datos necesarios
            if (!isset($_POST['numero_de_tarjeta']) || !isset($_POST['anio']) || !isset($_POST['mes']) || !isset($_POST['cvc']) || !isset($_POST['direccion'])) {
                wc_add_notice('Por favor, complete todos los campos requeridos.', 'error');
                return;
            }
            // Validar los datos de la tarjeta de crédito
            $card_number = preg_replace('/\s+/', '', $_POST['numero_de_tarjeta']);
            if (!ctype_digit($card_number) || strlen($card_number) !== 16) {
                wc_add_notice('El número de tarjeta de crédito es inválido.', 'error');
                return;
            }
            $card_month = (int) $_POST['mes'];
            $card_year = (int) $_POST['anio'];
            // if (!ctype_digit((string) $card_month) || !ctype_digit((string) $card_year) || $card_month < 1 || $card_month > 12 || $card_year < date('Y') || $card_year > date('Y') + 10) {
            //     wc_add_notice('La fecha de expiración de la tarjeta de crédito es inválida.', 'error');
            //     return;
            // }
            $card_cvc = $_POST['cvc'];
            if (!ctype_digit($card_cvc) || strlen($card_cvc) < 3 || strlen($card_cvc) > 4) {
                wc_add_notice('El código de seguridad de la tarjeta de crédito es inválido.', 'error');
                return;
            }
            $amount = (float) $order->get_total();
            // $apikey = ;

            // Enviar los datos de pago al servicio externo
            $url = 'https://devpagos.sitca-ve.com/api/v1/cargo';

            $headers = array(
                'apikey' => $apikey = $this->get_option('apikey'),
                'Content-Type' => $this->get_option('headers'),
            );
            $data = array(
                'numero' => $card_number,
                'ano' => $card_year,
                'nombre' => $_POST['nombre'],
                'apellido' => $_POST['apellido'],
                'mes' => $card_month,
                'cvc' => $card_cvc,
                'referencia' => $order_id,
                'direccion' => $_POST['direccion'],
                'monto' => $amount,
            );
            $args = array(
                'method' => 'POST',
                'headers' => $headers,
                'body' => http_build_query($data),
                'sslverify' => false,
            );
            $response = wp_remote_post($url, $args);
            if (is_wp_error($response)) {
                var_dump($response);
                wc_add_notice('Hubo un error al procesar el pago. Por favor, inténtelo de nuevo más tarde.', 'error');
                return;
            } else {
                $response_data = json_decode(wp_remote_retrieve_body($response));
                var_dump($response_data->mensaje);
                if (!$response_data->ok) {
                    wc_add_notice($response_data->mensaje, 'error');
                    return;
                }
                if ($response_data->monto != $amount) {
                    wc_add_notice('El monto del pago no coincide con el monto del pedido.', 'error');
                    return;
                }
                // El pago se procesó correctamente, actualizamos el estado del pedido en WooCommerce y redirigimos al usuario a la página de confirmación
                $order->payment_complete();
                $order->reduce_order_stock();
                $woocommerce->cart->empty_cart();
                
                wp_safe_redirect($this->get_return_url($order));
            }
        }

    }

    
}
