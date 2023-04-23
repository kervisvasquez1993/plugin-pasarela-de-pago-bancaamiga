<?php
/*
Plugin Name: BancaAmiga
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

// Agregar el método de pago a la sección de ajustes de pagos de WooCommerce
add_filter('woocommerce_payment_gateways', 'agregar_metodo_pago_personalizado');
function agregar_metodo_pago_personalizado($gateways)
{
    $gateways[] = 'WC_Metodo_Pago_Personalizado';
    return $gateways;
}

// Declarar la clase del método de pago personalizado
add_action('plugins_loaded', 'inicializar_metodo_pago_personalizado');
function inicializar_metodo_pago_personalizado()
{
    class WC_Metodo_Pago_Personalizado extends WC_Payment_Gateway
    {
        public function __construct()
        {
            // Información del método de pago que aparecerá en la página de ajustes de pagos de WooCommerce
            $this->id = 'metodo_pago_personalizado';
            $this->method_title = 'Método de pago personalizado';
            $this->method_description = 'Este método de pago permite cobrar con tarjeta de pago internacional a través de un servicio externo.';
            $this->has_fields = true;

            // Campos del formulario de pago que se mostrarán en la página de finalizar compra
            $this->form_fields = array(
                'numero_tarjeta' => array(
                    'type' => 'text',
                    'label' => 'Número de tarjeta',
                    'placeholder' => 'Numero de tarjeta',
                    'required' => true,
                ),
                'nombre' => array(
                    'type' => 'text',
                    'label' => 'Nombre',
                    'required' => true,
                ),
                'mes' => array(
                    'type' => 'text',
                    'label' => 'Mes',
                    'required' => true,
                ),
                'ano' => array(
                    'type' => 'text',
                    'label' => 'Año',
                    'required' => true,
                ),
                'cvc' => array(
                    'type' => 'text',
                    'label' => 'CVC',
                    'required' => true,
                ),
            );

            // Acción que se ejecutará cuando se envíe el formulario de pago
            add_action('woocommerce_checkout_process', array($this, 'procesar_pago'));

            // Acción que se ejecutará cuando se complete la orden de compra
            add_action('woocommerce_order_status_completed', array($this, 'completar_orden'));
        }

        // Función que procesa el pago
        public function procesar_pago()
        {
            // Obtener los datos del formulario de pago
            $numero_tarjeta = isset($_POST['numero_tarjeta']) ? sanitize_text_field($_POST['numero_tarjeta']) : '';
            $nombre = isset($_POST['nombre']) ? sanitize_text_field($_POST['nombre']) : '';
            $mes = isset($_POST['mes']) ? sanitize_text_field($_POST['mes']) : '';
            $ano = isset($_POST['ano']) ? sanitize_text_field($_POST['ano']) : '';
            $cvc = isset($_POST['cvc']) ? sanitize_text_field($_POST['cvc']) : '';

            // Validar los datos del formulario de pago
            if (empty($numero_tarjeta) || empty($nombre) || empty($mes) || empty($ano) || empty($cvc)) {
                wc_add_notice('Por favor, complete todos los campos del formulario de pago.', 'error');
                return;
            }

            // Enviar los datos de pago al servicio externo
            $url = 'https://devpagos.sitca-ve.com/api/v1/cargo';
            $apikey = 'pk4458cf90-2512-300b-b021-722c526f03c3';
            $headers = array(
                'apikey' => $apikey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
            $body = array(
                'numero_tarjeta' => $numero_tarjeta,
                'nombre' => $nombre,
                'mes' => $mes,
                'ano' => $ano,
                'cvc' => $cvc,
            );
            $args = array(
                'method' => 'POST',
                'headers' => $headers,
                'body' => http_build_query($body),
            );
            $response = wp_remote_post($url, $args);

            // Validar la respuesta del servicio externo
            if (is_wp_error($response)) {
                wc_add_notice('Hubo un error al procesar el pago. Por favor, inténtelo de nuevo más tarde.', 'error');
                return;
            }
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body);
            if ($response_code != 200 || empty($response_data) || !isset($response_data->ok) || !$response_data->ok) {
                wc_add_notice('Hubo un error al procesar el pago. Por favor, inténtelo de nuevo más tarde.', 'error');
                return;
            }

            // Marcar la orden de compra como completada y mostrar un mensaje al usuario
            $orden_id = wc_get_order_id_by_order_key($_POST['woocommerce-order-key']);
            $orden = wc_get_order($orden_id);
            $orden->update_status('completed', 'Pago procesado con éxito mediante el método de pago personalizado.');
            wc_add_notice('¡Gracias por su compra! Su pago ha sido procesado con éxito.', 'success');
        }

        // Función que se ejecuta cuando se completa la orden de compra
        public function completar_orden($orden_id)
        {
            // Agregar aquí cualquier acción adicional que necesite realizar cuando se complete la orden de compra
        }
    }
}
