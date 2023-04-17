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

add_shortcode('mi_plugin_form', 'mi_plugin_formulario');

// Función que muestra el formulario

// Función para procesar el formulario
function mi_plugin_procesar_formulario()
{
    if (isset($_POST['monto']) && isset($_POST['numero']) && isset($_POST['mes']) && isset($_POST['ano']) && isset($_POST['cvc']) && isset($_POST['direccion_postal']) && isset($_POST['referencia'])) {
        $url = 'http://devpagos.sitca-ve.com/api/v1/cargo';
        $args = array(
            'method' => 'POST',
            'headers' => array(
                'apikey' => 'pk4458cf90-2512-300b-b021-722c526f03c3',
            ),
            'body' => array(
                'monto' => $_POST['monto'],
                'numero' => $_POST['numero'],
                'mes' => $_POST['mes'],
                'ano' => $_POST['ano'],
                'cvc' => $_POST['cvc'],
                'direccion_postal' => $_POST['direccion_postal'],
                'referencia' => $_POST['referencia'],
            ),
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            // Error al enviar la solicitud
            echo strval($response);
            // echo 'Ocurrió un error al enviar la solicitud.';
        } else {
            // Solicitud enviada correctamente
            $body = wp_remote_retrieve_body($response);
            echo '<h2>La solicitud se envió correctamente. Respuesta: ' . $body.'</h2>';
        }
    }
}

add_action('init', 'mi_plugin_procesar_formulario');

function mi_plugin_formulario()
{
    $output = '';

    // Agrega el encabezado personalizado del formulario
    $output .= '<div class="mi-plugin-header">';
    $output .= '<h2>Mi formulario de pagos</h2>';
    $output .= '<p>Por favor complete los siguientes campos:</p>';
    $output .= '</div>';

    // Agrega los campos del formulario
    $output = '<form method="post">
    <label for="monto">Monto:</label>
    <input type="text" name="monto" id="monto"><br>
    <label for="numero">Número de tarjeta:</label>
    <input type="text" name="numero" id="numero"><br>
    <label for="mes">Mes de expiración:</label>
    <input type="text" name="mes" id="mes"><br>
    <label for="ano">Año de expiración:</label>
    <input type="text" name="ano" id="ano"><br>
    <label for="cvc">CVC:</label>
    <input type="text" name="cvc" id="cvc"><br>
    <label for="direccion_postal">Dirección postal:</label>
    <input type="text" name="direccion_postal" id="direccion_postal"><br>
    <label for="referencia">Referencia:</label>
    <input type="text" name="referencia" id="referencia"><br>
    <input type="submit" value="Enviar">
</form>';

    // Agrega el estilo CSS para el encabezado personalizado
    $output .= '<style>';
    $output .= '.mi-plugin-header {';
    $output .= '  background-color: #f2f2f2;';
    $output .= '  padding: 20px;';
    $output .= '  margin-bottom: 20px;';
    $output .= '}';
    $output .= '.mi-plugin-header h2 {';
    $output .= '  margin: 0;';
    $output .= '}';
    $output .= '</style>';

    return $output;
}





