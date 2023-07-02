<?php
function registrar_bancaamiga_post_type()
{
    $labels = array(
        'name' => 'BancaAmiga',
        'singular_name' => 'BancaAmiga',
        'menu_name' => 'BancaAmiga',
        'name_admin_bar' => 'BancaAmiga',
        'add_new' => 'Agregar nueva',
        'add_new_item' => 'Agregar nueva BancaAmiga',
        'new_item' => 'Nueva BancaAmiga',
        'edit_item' => 'Editar BancaAmiga',
        'view_item' => 'Ver BancaAmiga',
        'all_items' => 'Todas las BancaAmigas',
        'search_items' => 'Buscar BancaAmiga',
        'parent_item_colon' => 'BancaAmiga padre:',
        'not_found' => 'No se encontraron BancaAmigas.',
        'not_found_in_trash' => 'No se encontraron BancaAmigas en la papelera.',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'bancaamiga'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'thumbnail'),
    );

    register_post_type('bancaamiga', $args);
}

add_action('init', 'registrar_bancaamiga_post_type');
