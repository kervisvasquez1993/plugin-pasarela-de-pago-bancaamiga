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
// require_once(plugin_dir_path(__FILE__) . 'includes/formularioBanco.php');
add_action('woocommerce_checkout_before_order_review', 'mi_plugin_formulario_de_pago');
function mi_plugin_formulario_de_pago()
{
    // Obtiene el monto total del carrito de Woocommerce
    $monto = WC()->cart->total;
    ?>
    <div id="mi-plugin-pago">
        <h3><?php _e('Pago seguro con tarjeta', 'mi-plugin');?></h3>
        <form method="post" id="mi-plugin-pago-formulario">
            <div class="form-row form-row-wide">
                <label for="mi-plugin-pago-monto"><?php _e('Monto', 'mi-plugin');?> <span class="required">*</span></label>
                <input type="text" class="input-text" name="mi-plugin-pago-monto" id="mi-plugin-pago-monto" value="<?php echo esc_attr($monto); ?>" readonly>
            </div>
            <div class="form-row form-row-wide">
                <label for="mi-plugin-pago-numero"><?php _e('Número de tarjeta', 'mi-plugin');?> <span class="required">*</span></label>
                <input type="text" class="input-text" name="mi-plugin-pago-numero" id="mi-plugin-pago-numero" placeholder="<?php esc_attr_e('Número de tarjeta', 'mi-plugin');?>" required>
            </div>
            <div class="form-row form-row-wide">
                <label for="mi-plugin-pago-mes"><?php _e('Mes de expiración', 'mi-plugin');?> <span class="required">*</span></label>
                <input type="text" class="input-text" name="mi-plugin-pago-mes" id="mi-plugin-pago-mes" placeholder="<?php esc_attr_e('MM', 'mi-plugin');?>" required>
            </div>
            <div class="form-row form-row-wide">
                <label for="mi-plugin-pago-ano"><?php _e('Año de expiración', 'mi-plugin');?> <span class="required">*</span></label>
                <input type="text" class="input-text" name="mi-plugin-pago-ano" id="mi-plugin-pago-ano" placeholder="<?php esc_attr_e('YYYY', 'mi-plugin');?>" required>
            </div>
            <div class="form-row form-row-wide">
                <label for="mi-plugin-pago-cvc"><?php _e('CVC', 'mi-plugin');?> <span class="required">*</span></label>
                <input type="text" class="input-text" name="mi-plugin-pago-cvc" id="mi-plugin-pago-cvc" placeholder="<?php esc_attr_e('CVC', 'mi-plugin');?>" required>
            </div>
            <div class="form-row form-row-wide">
                <label for="mi-plugin-pago-direccion"><?php _e('Dirección de facturación', 'mi-plugin');?> <span class="required">*</span></label>
                <input type="text" class="input-text" name="mi-plugin-pago-direccion" id="mi-plugin-pago-direccion" placeholder="<?php esc_attr_e('Dirección de facturación', 'mi-plugin');?>" required>
            </div>
            <div class="form-row form-row-wide">
                <label for="mi-plugin-pago-referencia"><?php _e('Referencia', 'mi-plugin');?> <span class="required">*</span></label>
                <input type="text" class="input-text" name="mi-plugin-pago-referencia" id="mi-plugin-pago-referencia" placeholder="<?php esc_attr_e('Referencia', 'mi-plugin');?>" required>
            </div>
            <?php wp_nonce_field('mi-plugin-pago', 'mi-plugin-pago-nonce');?>
            <button type="submit" class="button alt" id="mi-plugin-pago-submit"><?php _e('Pagar', 'mi-plugin');?></button>
        </form>
    </div>
    <?php
}

// Procesa el pago cuando se envía el formulario
add_action('init', 'mi_plugin_procesa_pago');
function mi_plugin_procesa_pago()
{
    if (isset($_POST['mi-plugin-pago-nonce']) && wp_verify_nonce($_POST['mi-plugin-pago-nonce'], 'mi-plugin-pago')) {
        // Obtiene los datos del formulario
        $monto = $_POST['mi-plugin-pago-monto'];
        $numero = $_POST['mi-plugin-pago-numero'];
        $mes = $_POST['mi-plugin-pago-mes'];
        $ano = $_POST['mi-plugin-pago-ano'];
        $direccion = $_POST['mi-plugin-pago-direccion'];
        $cvc = $_POST['mi-plugin-pago-cvc'];
        $referencia = $_POST['mi-plugin-pago-referencia'];
        // Consume el servicio adicional para procesar el pago
        $url = 'http://devpagos.sitca-ve.com/api/v1/cargo';
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'apikey' => 'pk4458cf90-2512-300b-b021-722c526f03c3',
            ),
            'body' => array(
                'monto' => $monto,
                'numero' => $numero,
                'mes' => $mes,
                'ano' => $ano,
                'direccion' => $direccion,
                'cvc' => $cvc,
                'referencia' => $referencia,
            ),
        );
        $response = wp_safe_remote_post($url, $args);
        // echo strval($response);
        if (is_wp_error($response)) {
            echo 'Ocurrió un error al enviar la solicitud.';
            // Error al enviar la solicitud
            // echo strval($response);
            // $error_message = $response->get_error_message();
            // Muestra un mensaje de error con el cuerpo de la respuesta
            // echo '<p>Error: ' . $error_message . '</p>';
            // echo 'Ocurrió un error al enviar la solicitud.';
        } else {
            // Solicitud enviada correctamente
            $body = wp_remote_retrieve_body($response);
            $respuesta = json_decode($body);
            if ($respuesta->ok) {
                $order = wc_create_order();

                // Agrega los productos del carrito a la orden
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    $product = $cart_item['data'];
                    $quantity = $cart_item['quantity'];
                    $order->add_product($product, $quantity);
                }

                // // Agrega los datos del cliente y actualiza la orden
                $order->set_address($datos_del_cliente, 'billing');
                // $order->update_meta_data('payment_method_title', 'Pago Procesado por BancaAmiga');
                $order->set_address($datos_del_cliente, 'shipping');
                $order->calculate_totals();
                // $order->save();

                // // 2. Establece el estado de la orden como "Completada" y añade una nota
                $order->update_status('completed', 'Transacción aprobada: ');

                // // 3. Limpia el carrito de compra
                // WC()->cart->empty_cart();

                // // 4. Redirige al usuario a la página de agradecimiento
                // $redirect_url = $order->get_checkout_order_received_url();
                // wp_redirect($redirect_url);
                // exit;
                // echo "procesado";
                // $details = wc_get_order($order->id);
                echo $order;
                // echo ($details);


            } else {
                // Transacción rechazada

                // Agrega un mensaje de error en la página de finalizar compra
                wc_add_notice('Error en la transacción: ');

                // Redirige al usuario a la página de finalizar compra
                $checkout_url = wc_get_checkout_url();
                wp_redirect($checkout_url);
                exit;
            }
        }
    }
}

