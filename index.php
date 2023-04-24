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
// Hook to add the custom payment method
// add_filter('woocommerce_payment_gateways', 'add_custom_gateway');

// Registre su método de pago personalizado en WooCommerce
add_filter('woocommerce_payment_gateways', 'agregar_mi_metodo_pago');
function agregar_mi_metodo_pago($gateways)
{
    $gateways[] = 'WC_Mi_Metodo_Pago';
    return $gateways;
}

// Incluya la clase de su método de pago personalizado
add_action('plugins_loaded', 'init_wc_mi_metodo_pago');
function init_wc_mi_metodo_pago()
{

    class WC_Mi_Metodo_Pago extends WC_Payment_Gateway
    {

        // Constructor
        public function __construct()
        {
            $this->id = 'mi_metodo_pago';
            $this->method_title = __('Mi Método de Pago', 'woocommerce');
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
                'enabled' => array(
                    'title' => __('Habilitar/Inhabilitar', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Habilitar Mi Método de Pago', 'woocommerce'),
                    'default' => 'yes',
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
            // Aquí es donde debe enviar la información de la tarjeta al servicio externo utilizando la API.

            // Ejemplo de cómo enviar datos a la API utilizando cURL:
            $url = 'https://devpagos.sitca-ve.com/api/v1/cargo';

            // enviar datos al servicor externo
            // Enviar los datos de pago al servicio externo
            $ch = curl_init();
            $apikey = 'pk4458cf90-2512-300b-b021-722c526f03c3';
            $headers = array(
                'apikey: pk4458cf90-2512-300b-b021-722c526f03c3',
                'Content-Type: application/x-www-form-urlencoded',
            );
            $data = array(
                'numero_de_tarjeta' => $_POST['numero_de_tarjeta'],
                // 'nombre' => $_POST['nombre'],
                'ano' => $_POST['anio'],
                'mes' => $_POST['mes'],
                'cvc' => $_POST['cvc'],
                'referencia' => $_POST['referencia'],
                'direccion' => $_POST['direccion'],
                'monto' => $order->total,
            );
            // var_dump($body);
            $args = array(
                'method' => 'POST',
                'headers' => $headers,
                'body' => http_build_query($data),
            );
            // $response = wp_remote_post($url, $args);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            // var_dump($response);
            var_dump($response);
            curl_close($ch);
            $response_data = json_decode($response);
            // var_dump($response_data);
            var_dump($response_data);
            // if (is_wp_error($response)) {
            //     // var_dump($response);
            //     echo "error no se establecio conexion";
            //     wc_add_notice('Hubo un error al procesar el pago. Por favor, inténtelo de nuevo más tarde.', 'error');
            //     return;
            // } else {
            //     // echo "conexion establecida";
            //     var_dump($response);
            //     // echo "<br/>";
            //     if($response->ok){
            //         echo "pago exitoso";
            //     }
            //     else{
            //         echo "pago fallido, revise los datos de su tarjeta";
            //         // var_dump($response);
            //     }
            // }

            

        }
    }
}
