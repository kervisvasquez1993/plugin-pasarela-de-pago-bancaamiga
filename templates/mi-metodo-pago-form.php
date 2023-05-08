<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="form-row form-row-wide">
    <label for="numero_de_tarjeta"><?php _e('Número de tarjeta', 'woocommerce'); ?> <span class="required">*</span></label>
    <input id="numero_de_tarjeta" name="numero_de_tarjeta" class="input-text wc-credit-card-form-card-number" type="text" autocomplete="off" placeholder="<?php _e('•••• •••• •••• ••••', 'woocommerce'); ?>" required>
</div>
<div class="form-row form-row-wide">
    <label for="nombre"><?php _e('Nombre', 'woocommerce'); ?></label>
    <input id="nombre" name="nombre" class="input-text wc-credit-card-form-name" type="text" autocomplete="off" placeholder="<?php _e('Nombre', 'woocommerce'); ?>">
</div>
<div class="form-row form-row-wide">
    <label for="apellido"><?php _e('Apellido', 'woocommerce'); ?></label>
    <input id="apellido" name="apellido" class="input-text wc-credit-card-form-last-name" type="text" autocomplete="off" placeholder="<?php _e('Apellido', 'woocommerce'); ?>">
</div>


<div class="form-row form-row-wide">
    <label><?php _e('Fecha de vencimiento', 'woocommerce'); ?> <span class="required">*</span></label>
    <input id="mes" name="mes" class="input-text wc-credit-card-form-card-expiry-month" type="text" autocomplete="off" placeholder="<?php _e('MM', 'woocommerce'); ?>" required>
    <span> / </span>
    <input id="anio" name="anio" class="input-text wc-credit-card-form-card-expiry-year" type="text" autocomplete="off" placeholder="<?php _e('AA', 'woocommerce'); ?>" required>
</div>


<div class="form-row form-row-wide">
    <label for="cvc"><?php _e('Código de seguridad (CVC)', 'woocommerce'); ?> <span class="required">*</span></label>
    <input id="cvc" name="cvc" class="input-text wc-credit-card-form-card-cvc" type="password" autocomplete="off" placeholder="<?php _e('•••', 'woocommerce'); ?>" required>
</div>
<div class="form-row form-row-wide">
    <label for="direccion"><?php _e('Dirección postal', 'woocommerce'); ?> <span class="required">*</span></label>
    <input id="direccion" name="direccion" class="input-text wc-credit-card-form-card-direccion" type="text" autocomplete="off" placeholder="<?php _e('Direccion', 'woocommerce'); ?>">
</div>

