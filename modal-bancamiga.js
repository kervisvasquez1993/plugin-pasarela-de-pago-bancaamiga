jQuery(document).ready(function($) {
    // Abrir ventana modal al hacer clic en el botón
    $('#abrir-modal-bancamiga').on('click', function(event) {
        event.preventDefault();
        $('#modal-bancamiga').show();
    });
    
    // Cerrar ventana modal al hacer clic en el botón de cerrar
    $('.close').on('click', function() {
        $('#modal-bancamiga').hide();
    });
    
    // Generar el botón de pago en la ventana modal
    var url_pago = 'URL para completar el pago con Banca Amiga';
    $('#modal-bancamiga .modal-content').html('<a class="button" href="' + url_pago + '" target="_blank">Completar Pago con Banca Amiga</a>');
});