<?php
    
    /*
    * Agregar nuevo menú al Panel de Control de WordPress
    */
    
    function pepr_agregar_link_menu()
    {
        add_menu_page(
            'Pend Project', // Título de la página
            'Gestionar Pend Project', // Texto de link
            'manage_options', // Para administradores...
            plugin_dir_path(__FILE__) . '/pepr-administrator.php' // Página a mostrar
        );
    }

    add_action( 'admin_menu', 'pepr_agregar_link_menu' );

    /*
    * Agregar ficheros CSS y JS propios del Plugin
    */

    function pepr_load_custom_wp_admin_style($hook) {
        /* 
        **Cargar sólo en el panel de administración de WordPress
        ** y sólo para la opción de gestión de Pend Project, estos
        ** ficheros CSS y JS no se cargarán en el menú de administración
        ** de POST o páginas, Menús, etc.
        */
        
        if($hook != 'pend-project/includes/pepr-administrator.php') {
                return;
        }

        wp_enqueue_style( 'pepr_admin_css_bootstrap', plugins_url('../css/bootstrap.min.css', __FILE__) );
        wp_enqueue_style( 'pepr_admin_css', plugins_url('../css/styles.css', __FILE__) );

        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-resizable');

        wp_enqueue_script(
            'pepr_popper_js',
            plugins_url( '../js/popper.min.js', __FILE__ ),
            array( 'jquery' ),
            '1.0',
            true
        );

        wp_enqueue_script(
            'pepr_bootstrap_js',
            plugins_url( '../js/bootstrap.min.js', __FILE__ ),
            array( 'jquery' ),
            '4.3.1',
            true
        );

        wp_enqueue_script(
            'pepr_js',
            plugins_url( '../js/pepr.js', __FILE__ ),
            array( 'jquery' ),
            '1.0',
            true
        );

        wp_localize_script('pepr_js', 'pepr_var', ['nonce' => wp_create_nonce('pepr-security')]);

    }

    add_action( 'admin_enqueue_scripts', 'pepr_load_custom_wp_admin_style' );

?>