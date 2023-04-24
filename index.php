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
// A continuación, agregue los campos del formulario aquí, como "Número de tarjeta", "Nombre", "Año", "Mes" y "CVC".
            // Utilice la función wc_get_template() para cargar una plantilla personalizada que contenga estos campos.
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
            // $url = 'https://devpagos.sitca-ve.com/api/v1/cargo';

            // enviar datos al servicor externo
            // Enviar los datos de pago al servicio externo
            $url = 'https://devpagos.sitca-ve.com/api/v1/cargo';
            $apikey = 'pk4458cf90-2512-300b-b021-722c526f03c3';
            $headers = array(
                'apikey' => $apikey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
            $body = array(
                'numero_de_tarjeta' => $_POST['numero_de_tarjeta'],
                'nombre' => $_POST['nombre'],
                'anio' => $_POST['anio'],
                'mes' => $_POST['mes'],
                'cvc' => $_POST['cvc'],
                'referencia' => "cerca de la bomba",
                'direccion' => "calle 1",
                'monto' => "1,50",
            );
          
            $args = array(
                'method' => 'POST',
                'headers' => $headers,
                'body' => http_build_query($body),
            );

            $response = wp_remote_post($url, $args);

            // $ch = curl_init();
            // echo $response;
            if (is_wp_error($response)) {
                // echo implode(',',body);
                echo "error no se pudo conectar";
                wc_add_notice('Hubo un error al procesar el pago. Por favor, inténtelo de nuevo más tarde.', 'error');
                return;
            }
            else{
                echo "pago exitoso";
            }

            // $headers = array(
            //     'apikey: pk4458cf90-2512-300b-b021-722c526f03c3',
            //     'Content-Type: application/x-www-form-urlencoded',
            // );

            // curl_setopt($ch, CURLOPT_URL, $url);
            // curl_setopt($ch, CURLOPT_POST, true);
            // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // $response = curl_exec($ch);
            // curl_close($ch);

            // Analice la respuesta y verifique si el pago fue exitoso
            // $response_data = json_decode($response);
            // var_dump($response_data);

            // if ($response_data->ok === true) {
            //     // Marque la orden como completada y vacíe el carrito
            //     $order->update_status('completed');
            //     $woocommerce->cart->empty_cart();

            //     // Devuelva un mensaje de éxito
            //     return array(
            //         'result' => 'success',
            //         'redirect' => $this->get_return_url($order),
            //     );
            // } else {
            //     // Muestre un mensaje de error en el frontend de WooCommerce
            //     wc_add_notice(__('Error en el pago. Por favor, inténtelo de nuevo o utilice un método de pago diferente.', 'woocommerce'), 'error');
            //     return;
            // }
        }
    }
}

// // Function to add the custom payment method
// function add_custom_gateway($gateways)
// {
//     $gateways[] = 'WC_Custom_Payment_Method';
//     return $gateways;
// }

// // Custom Payment Gateway Class

// add_action('plugins_loaded', 'inicializar_metodo_pago_personalizado');
// function inicializar_metodo_pago_personalizado()
// {

//     class WC_Custom_Payment_Method extends WC_Payment_Gateway
//     {

//         // Constructor
//         public function __construct()
//         {
//             // Define the ID and Name of the payment method
//             $this->id = 'custom_payment_method';
//             $this->method_title = __('Custom Payment Method', 'woocommerce');
//             // $this->method_description = __('This is a custom payment method', 'woocommerce');
//             // Define the fields for the payment method
//             $this->init_form_fields();

//             // Define the settings for the payment method
//             $this->init_settings();

//             // Define the title and description for the payment method
//             $this->title = $this->get_option('title');
//             $this->description = $this->get_option('description');

//             // Hook to process the payment
//             add_action('woocommerce_checkout_process', array($this, 'process_payment'));

//             // Hook to display the payment method form
//             add_action('woocommerce_credit_card_form_fields', array($this, 'payment_fields'));
//         }

//         // Function to initialize the form fields for the payment method
//         public function init_form_fields()
//         {
//             // Define the fields for the payment method
//             $this->form_fields = array(
//                 'enabled' => array(
//                     'title' => __('Enable/Disable', 'woocommerce'),
//                     'type' => 'checkbox',
//                     'label' => __('Enable Custom Payment Method', 'woocommerce'),
//                     'default' => 'yes',
//                 ),
//                 'title' => array(
//                     'title' => __('Title', 'woocommerce'),
//                     'type' => 'text',
//                     'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
//                     'default' => __('Custom Payment Method', 'woocommerce'),
//                     'desc_tip' => true,
//                 ),
//                 'description' => array(
//                     'title' => __('Description', 'woocommerce'),
//                     'type' => 'textarea',
//                     'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
//                     'default' => '',
//                     'desc_tip' => true,
//                 ),
//             );
//         }

//         // Function to display the payment method form
//         public function payment_fields()
//         {
//             // Display the payment method form
//             echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form">';
//             echo '<p class="form-row form-row-wide">';
//             echo '<label for="' . esc_attr($this->id) . '-card-number">' . __('Card Number', 'woocommerce') . ' <span class="required"></span></label>';
//             echo '<input id="' . esc_attr($this->id) . '-card-number" name="' . esc_attr($this->id) . '-card-number" type="text" autocomplete="off" placeholder="' . __('1234 5678 9012 3456', 'woocommerce') . '" />';
//             echo '</p>';
//             echo '<p class="form-row form-row-first">';
//             echo '<label for="' . esc_attr($this->id) . '-card-expiry-month">' . __('Expiry Month (MM)', 'woocommerce') . ' <span class="required"></span></label>';
//             echo '<input id="' . esc_attr($this->id) . '-card-expiry-month" name="' . esc_attr($this->id) . '-card-expiry-month" type="text" autocomplete="off" placeholder="' . __('MM', 'woocommerce') . '" />';
//             echo '</p>';
//             echo '<p class="form-row form-row-last">';
//             echo '<label for="' . esc_attr($this->id) . '-card-expiry-year">' . __('Expiry Year (YYYY)', 'woocommerce') . ' <span class="required">*</span></label>';
//             echo '<input id="' . esc_attr($this->id) . '-card-expiry-year" name="' . esc_attr($this->id) . '-card-expiry-year" type="text" autocomplete="off" placeholder="' . __('YYYY', 'woocommerce') . '" />';
//             echo '</p>';
//             echo '<div class="clear"></div>';

//             echo '<p class="form-row form-row-wide">';
//             echo '<label for="' . esc_attr($this->id) . '-card-cvc">' . __('Card Code', 'woocommerce') . ' <span class="required">*</span></label>';
//             echo '<input id="' . esc_attr($this->id) . '-card-cvc" name="' . esc_attr($this->id) . '-card-cvc" type="text" autocomplete="off" placeholder="' . __('CVC', 'woocommerce') . '" />';
//             echo '</p>';

//             echo '<div class="clear"></div>';
//             echo '</fieldset>';
//         }

//         // Function to process the payment
//         public function process_payment($order_id)
//         {
//             global $woocommerce;
//             // Get the order data
//             $order = new WC_Order($order_id);

//             // Get the card details
//             $card_number = $_POST['custom_payment_method-card-number'];
//             $card_expiry_month = $_POST['custom_payment_method-card-expiry-month'];
//             $card_expiry_year = $_POST['custom_payment_method-card-expiry-year'];
//             $card_cvc = $_POST['custom_payment_method-card-cvc'];

//             // Send the payment data to the external service
//             $url = 'https://devpagos.sitca-ve.com/api/v1/cargo';
//             $data = array(
//                 'numero_de_tarjeta' => $card_number,
//                 'nombre' => $order->billing_first_name . ' ' . $order->billing_last_name,
//                 'ano' => $card_expiry_year,
//                 'mes' => $card_expiry_month,
//                 'cvc' => $card_cvc,
//             );
//             $headers = array(
//                 'apikey: pk4458cf90-2512-300b-b021-722c526f03c3',
//                 'Content-Type: application/x-www-form-urlencoded',
//             );
//             $response = wp_remote_post($url, array(
//                 'method' => 'POST',
//                 'headers' => $headers,
//                 'body' => $data,
//             ));

//             // Check if the payment was successful
//             if (is_wp_error($response)) {
//                 // Payment failed
//                 $order->update_status('failed', __('Payment error', 'woocommerce'));
//                 wc_add_notice(__('Payment error:', 'woocommerce') . ' ' . $response->get_error_message(), 'error');
//                 return;
//             } else {
//                 $response_body = json_decode($response['body']);
//                 if ($response_body->ok) {
//                     // Payment successful
//                     $order->payment_complete();
//                     $woocommerce->cart->empty_cart();
//                     wc_add_notice(__('Payment successful', 'woocommerce'), 'success');
//                 } else {
//                     // Payment failed
//                     $order->update_status('failed', __('Payment error', 'woocommerce'));
//                     wc_add_notice(__('Payment error:', 'woocommerce') . ' ' . $response_body->error, 'error');
//                     return;
//                 }
//             }
//         }

//     }
// }

// // Declarar la clase del método de pago personalizado
// add_action('plugins_loaded', 'inicializar_metodo_pago_personalizado');
// function inicializar_metodo_pago_personalizado()
// {
//     class WC_Metodo_Pago_Personalizado extends WC_Payment_Gateway
//     {
//         public function __construct()
//         {
//             // Información del método de pago que aparecerá en la página de ajustes de pagos de WooCommerce
//             $this->id = 'metodo_pago_personalizado';
//             $this->method_title = 'Método de pago personalizado';
//             $this->method_description = 'Este método de pago permite cobrar con tarjeta de pago internacional a través de un servicio externo.';
//             $this->has_fields = true;

//             // Campos del formulario de pago que se mostrarán en la página de finalizar compra
//             $this->form_fields = array(
//                 'numero_tarjeta' => array(
//                     'type' => 'text',
//                     'label' => 'Número de tarjeta',
//                     'placeholder' => 'Numero de tarjeta',
//                     'required' => true,
//                 ),
//                 'nombre' => array(
//                     'type' => 'text',
//                     'label' => 'Nombre',
//                     'required' => true,
//                 ),
//                 'mes' => array(
//                     'type' => 'text',
//                     'label' => 'Mes',
//                     'required' => true,
//                 ),
//                 'ano' => array(
//                     'type' => 'text',
//                     'label' => 'Año',
//                     'required' => true,
//                 ),
//                 'cvc' => array(
//                     'type' => 'text',
//                     'label' => 'CVC',
//                     'required' => true,
//                 ),
//             );

//             // Acción que se ejecutará cuando se envíe el formulario de pago
//             add_action('woocommerce_checkout_process', array($this, 'procesar_pago'));

//             // Acción que se ejecutará cuando se complete la orden de compra
//             add_action('woocommerce_order_status_completed', array($this, 'completar_orden'));
//         }

//         // Función que procesa el pago
//         public function procesar_pago()
//         {
//             // Obtener los datos del formulario de pago
//             $numero_tarjeta = isset($_POST['numero_tarjeta']) ? sanitize_text_field($_POST['numero_tarjeta']) : '';
//             $nombre = isset($_POST['nombre']) ? sanitize_text_field($_POST['nombre']) : '';
//             $mes = isset($_POST['mes']) ? sanitize_text_field($_POST['mes']) : '';
//             $ano = isset($_POST['ano']) ? sanitize_text_field($_POST['ano']) : '';
//             $cvc = isset($_POST['cvc']) ? sanitize_text_field($_POST['cvc']) : '';

//             // Validar los datos del formulario de pago
//             if (empty($numero_tarjeta) || empty($nombre) || empty($mes) || empty($ano) || empty($cvc)) {
//                 wc_add_notice('Por favor, complete todos los campos del formulario de pago.', 'error');
//                 return;
//             }

//             // Enviar los datos de pago al servicio externo
//             $url = 'https://devpagos.sitca-ve.com/api/v1/cargo';
//             $apikey = 'pk4458cf90-2512-300b-b021-722c526f03c3';
//             $headers = array(
//                 'apikey' => $apikey,
//                 'Content-Type' => 'application/x-www-form-urlencoded',
//             );
//             $body = array(
//                 'numero_tarjeta' => $numero_tarjeta,
//                 'nombre' => $nombre,
//                 'mes' => $mes,
//                 'ano' => $ano,
//                 'cvc' => $cvc,
//             );
//             $args = array(
//                 'method' => 'POST',
//                 'headers' => $headers,
//                 'body' => http_build_query($body),
//             );
//             $response = wp_remote_post($url, $args);

//             // Validar la respuesta del servicio externo
//             if (is_wp_error($response)) {
//                 wc_add_notice('Hubo un error al procesar el pago. Por favor, inténtelo de nuevo más tarde.', 'error');
//                 return;
//             }
//             $response_code = wp_remote_retrieve_response_code($response);
//             $response_body = wp_remote_retrieve_body($response);
//             $response_data = json_decode($response_body);
//             if ($response_code != 200 || empty($response_data) || !isset($response_data->ok) || !$response_data->ok) {
//                 wc_add_notice('Hubo un error al procesar el pago. Por favor, inténtelo de nuevo más tarde.', 'error');
//                 return;
//             }

//             // Marcar la orden de compra como completada y mostrar un mensaje al usuario
//             $orden_id = wc_get_order_id_by_order_key($_POST['woocommerce-order-key']);
//             $orden = wc_get_order($orden_id);
//             $orden->update_status('completed', 'Pago procesado con éxito mediante el método de pago personalizado.');
//             wc_add_notice('¡Gracias por su compra! Su pago ha sido procesado con éxito.', 'success');
//         }

//         // Función que se ejecuta cuando se completa la orden de compra
//         public function completar_orden($orden_id)
//         {
//             // Agregar aquí cualquier acción adicional que necesite realizar cuando se complete la orden de compra
//         }
//     }
// }
