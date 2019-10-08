<?php

    /*
    Plugin Name: Pend Project
    Description: Notas, tareas o recordatorios pendientes por proyecto.
    Version: 1.0
    Author: José Manuel Ortiz Sánchez
    Author URI: https://www.ortizsanchezdev.es
    License: GPLv3
    */

    /*
    Copyright (C) 2019  Ortiz Sánchez, José Manuel (ortizsanchezdev@gmail.com)

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
    */


    /*
    * Creación de las nuevas tablas de proyectos y tareas
    */

    if(!defined('ABSPATH')) die();

    function pepr_crear_tablas_bd() 
    {
        global $wpdb;
        // Definimos el nombre de la tabla con el prefijo usado en la instalación:
        $notas = $wpdb->prefix . 'pepr_notas';
        $charset_collate = $wpdb->get_charset_collate();

        // Diseñamos la consulta SQL para la nueva tabla:
        $sql = "CREATE TABLE $notas (
             id int(9) NOT NULL AUTO_INCREMENT,
             proyecto varchar(55) NOT NULL,
             titulo varchar(55) NOT NULL,
             descripcion text,
             prioridad varchar(55),
             periodicidad varchar(55),
             UNIQUE KEY id(id)
             ) $charset_collate;";
       
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
       
        // Ejecutamos la consulta:
        dbDelta($sql);
    }

    register_activation_hook(__FILE__, 'pepr_crear_tablas_bd');

    
    /*
    * Creamos la función para insertar datos en la tabla 
    */

    function pepr_insertar_datos_bd() {

        // Verificamos el 'nonce' pasado por formulario, en caso de no ser correcto detiene la ejecución.
        if ( ! wp_verify_nonce($_REQUEST['nonce'], 'pepr-security') ) {
			echo "¡NO PUEDES PASAAAAAAAAAR!";
			wp_die();
		}

        // Comprueba si estamos recibiendo todos los datos, en caso contrario detiene la ejecución de la función.
        if (!isset($_POST['titulo']) || !isset($_POST['proyecto']) || !isset($_POST['descripcion']) || !isset($_POST['prioridad']) || !isset($_POST['periodicidad'])) wp_die();

        global $wpdb;
        $notas = $wpdb->prefix . 'pepr_notas';

        $titulo = (empty($_POST['titulo'])) ? 'Doble click para modificar título' : sanitize_text_field($_POST['titulo']);
        $proyecto = (empty($_POST['proyecto'])) ? 'Doble click para modificar proyecto' : sanitize_text_field($_POST['proyecto']);
        $descripcion = (empty($_POST['descripcion'])) ? 'Doble click para modificar la descripción' : sanitize_textarea_field($_POST['descripcion']);
        $prioridad = (empty($_POST['prioridad'])) ? 'Baja' : sanitize_text_field($_POST['prioridad']);
        $periodicidad = (empty($_POST['periodicidad'])) ? 'Puntual' : sanitize_text_field($_POST['periodicidad']);

        $wpdb->query($wpdb->prepare("INSERT INTO $notas VALUES (NULL, '%s', '%s', '%s', '%s', '%s')", $proyecto, $titulo, $descripcion, $prioridad, $periodicidad));

        wp_die();
    }

    add_action( 'wp_ajax_peprADD', 'pepr_insertar_datos_bd' );


    /*
    * Creamos la función para modificar datos en la tabla 
    */

    function pepr_modificar_datos_bd() {
        
        // Verificamos el 'nonce' pasado por formulario, en caso de no ser correcto detiene la ejecución.
        if ( ! wp_verify_nonce($_REQUEST['nonce'], 'pepr-security') ) {
			echo "¡NO PUEDES PASAAAAAAAAAR!";
			wp_die();
        }
        
        // Comprueba si estamos recibiendo todos los datos, en caso contrario detiene la ejecución de la función.
        if (!isset($_POST['id']) || !isset($_POST['titulo']) || !isset($_POST['proyecto']) || !isset($_POST['descripcion']) || !isset($_POST['prioridad']) || !isset($_POST['periodicidad'])) wp_die();

        global $wpdb;
        $notas = $wpdb->prefix . 'pepr_notas';

        $id = (empty($_POST['id'])) ? wp_die() : sanitize_text_field($_POST['id']);
        $titulo = (empty($_POST['titulo'])) ? 'Doble click para modificar título' : sanitize_text_field($_POST['titulo']);
        $proyecto = (empty($_POST['proyecto'])) ? 'Doble click para modificar proyecto' : sanitize_text_field($_POST['proyecto']);
        $descripcion = (empty($_POST['descripcion'])) ? 'Doble click para modificar la descripción' : sanitize_textarea_field($_POST['descripcion']);
        $prioridad = (empty($_POST['prioridad'])) ? 'Baja' : sanitize_text_field($_POST['prioridad']);
        $periodicidad = (empty($_POST['periodicidad'])) ? 'Puntual' : sanitize_text_field($_POST['periodicidad']);

        $wpdb->query($wpdb->prepare("UPDATE $notas SET proyecto = '%s', titulo = '%s', descripcion = '%s', prioridad = '%s', periodicidad = '%s' WHERE id = '%d'", $proyecto, $titulo, $descripcion, $prioridad, $periodicidad, $id));

        wp_die();
    }

    add_action( 'wp_ajax_peprEDIT', 'pepr_modificar_datos_bd' );


    /*
    * Creamos la función para borrar datos en la tabla 
    */

    function pepr_borrar_datos_bd() {

        // Verificamos el 'nonce' pasado por formulario, en caso de no ser correcto detiene la ejecución.
        if ( ! wp_verify_nonce($_REQUEST['nonce'], 'pepr-security') ) {
			echo "¡NO PUEDES PASAAAAAAAAAR!";
			wp_die();
		}

        // Comprueba si estamos recibiendo todos los datos, en caso contrario detiene la ejecución de la función.
        if (!isset($_POST['id'])) wp_die();

        global $wpdb;
        $notas = $wpdb->prefix . 'pepr_notas';

        $id = (empty($_POST['id'])) ? wp_die() : sanitize_text_field($_POST['id']);

        $wpdb->query($wpdb->prepare("DELETE FROM $notas WHERE id = '%d'", $id));

        wp_die();
    }

    add_action( 'wp_ajax_peprDELL', 'pepr_borrar_datos_bd' );

    
    /*
    * Creamos la función para recuperar un registro en la tabla 
    */

    function pepr_get_registro_bd() {

        // Verificamos el 'nonce' pasado por formulario, en caso de no ser correcto detiene la ejecución.
        if ( ! wp_verify_nonce($_REQUEST['nonce'], 'pepr-security') ) {
			echo "¡NO PUEDES PASAAAAAAAAAR!";
			wp_die();
        }
        
        // Comprueba si estamos recibiendo todos los datos, en caso contrario detiene la ejecución de la función.
        if (!isset($_POST['id'])) wp_die();

        global $wpdb;
        $notas = $wpdb->prefix . 'pepr_notas';

        $id = (empty($_POST['id'])) ? wp_die() : sanitize_text_field($_POST['id']);

        $tarjeta = $wpdb->get_row($wpdb->prepare("SELECT * FROM $notas WHERE id = '%d'", $id));

        $json[] = array(
            'id' => esc_html($tarjeta->id),
            'proyecto' => esc_html($tarjeta->proyecto),
            'titulo' => esc_html($tarjeta->titulo),
            'descripcion' => esc_html($tarjeta->descripcion),
            'prioridad' => esc_html($tarjeta->prioridad),
            'periodicidad' => esc_html($tarjeta->periodicidad)
        );

        $jsonString = json_encode($json);
        echo $jsonString;

        wp_die();
    }

    add_action( 'wp_ajax_peprGET', 'pepr_get_registro_bd' );

    
    /*
    * Creamos la función para recuperar todos los registros pendientes
    */

    function pepr_gets_tarjetas_bd() {

        // Verificamos el 'nonce' pasado por formulario, en caso de no ser correcto detiene la ejecución.
        if ( ! wp_verify_nonce($_REQUEST['nonce'], 'pepr-security') ) {
            echo "¡NO PUEDES PASAAAAAAAAAR!";
            wp_die();
        }
                
        // Comprueba si estamos recibiendo todos los datos, en caso contrario detiene la ejecución de la función.
        if (!isset($_POST['orden'])) wp_die();

        $orden = (empty($_POST['orden'])) ? 'titulo' : sanitize_text_field($_POST['orden']);

        global $wpdb;

        $tabla = $wpdb->prefix . 'pepr_notas';
    
        $notas_pendientes = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tabla ORDER BY $orden", $orden));

        ?>
            <div class="card mr-2">
                <div class="card-header text-center bg-dark text-light">ORDENAR POR...</div>
                <div class="card-body">
                    <h6 class="card-title text-center">Elige una opción:</h6>
                    <div class="btn btn-outline-primary cursor-pointer w-100 mb-1" onclick="ordenar('titulo');">TÍTULO</div>
                    <div class="btn btn-outline-success cursor-pointer w-100 mb-1" onclick="ordenar('proyecto');">PROYECTO</div>
                    <div class="btn btn-outline-warning cursor-pointer w-100 mb-1" onclick="ordenar('prioridad');">PRIORIDAD</div>
                    <div class="btn btn-outline-dark cursor-pointer w-100 mb-2" onclick="ordenar('periodicidad');">PERIODICIDAD</div>
                </div>
            </div>
        <?php
    
        foreach ($notas_pendientes as $nota) 
        {
            $color_prioridad;

            switch($nota->prioridad)
            {
                case "Alta":
                    $color_prioridad = 'btn-danger';
                    break;
                case "Media":
                    $color_prioridad = 'btn-warning';
                    break;
                default:
                    $color_prioridad = 'btn-info';
            }

            ?>

                <div class="card mr-2" title="Doble click para editar tarjeta..." id="<?php echo esc_attr($nota->id) ?>" ondblclick="editarTarjeta(<?php echo esc_attr($nota->id) ?>);" ondrag="estoyMoviendo(<?php echo esc_attr($nota->id) ?>);">
                    <div class="card-header text-center bg-dark text-light"><?php echo esc_html($nota->titulo) ?></div>
                    <div class="card-body">
                        <h6 class="card-title text-center"><?php echo esc_html($nota->proyecto) ?></h6>
                        <p class="card-text text-justify pre-scrollable"><?php echo esc_html($nota->descripcion) ?></p>
                        <div class="pepr_opciones">
                            <div class="btn <?php echo $color_prioridad; ?>" title="Prioridad"><?php echo esc_html($nota->prioridad) ?></div>
                            <div class="btn btn-primary" title="Periodicidad"><?php echo esc_html($nota->periodicidad) ?></div>
                            <div class="btn btn-dark cursor-pointer pepr-eliminar" onclick="eliminarTarjeta(<?php echo esc_attr($nota->id) ?>);">Eliminar</div>
                        </div>
                    </div>
                </div>

            <?php
        }

        ?>

            <div class="papelera">

            <!-- Agregar nueva tarjeta - Funcionalidad: Mostrar modal -->

            <img id="png_agregar" class="mr-5" src="<?php echo esc_url(plugins_url('/pend-project/img/agregar.png')); ?>" title="Pulsa para agregar nueva tarjeta..." data-toggle="modal" data-target="#pepr_add_tarjeta" >

            <!-- Modal -->
            <div class="modal fade" id="pepr_add_tarjeta" tabindex="-1" role="dialog" aria-labelledby="pepr_add_tarjetaTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Agregar nueva tarjeta</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-group">
                                <label for="pepr_titulo">Título</label>
                                <input type="text" class="form-control" id="pepr_titulo" placeholder="Agrega aquí el título de la tarjeta...">
                            </div>
                            <div class="form-group">
                                <label for="pepr_proyecto">Proyecto</label>
                                <input type="text" class="form-control" id="pepr_proyecto" placeholder="Web de la tarea, proyecto, asocidado a...">
                            </div>
                            <div class="form-group">
                                <label for="pepr_descripcion">Descripción</label>
                                <textarea class="form-control" id="pepr_descripcion" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="">Prioridad y Periodicidad</label>
                            </div>
                            <div class="form-group">
                                <select multiple class="form-control float-left w-50 p-2" id="pepr_prioridad">
                                    <option>Baja</option>
                                    <option>Media</option>
                                    <option>Alta</option>
                                </select>
                                <select multiple class="form-control float-left w-50 p-2" id="pepr_periodicidad">
                                    <option>Puntual</option>
                                    <option>Diaria</option>
                                    <option>Semanal</option>
                                    <option>Mensual</option>
                                    <option>Anual</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button id="pepr_btn_agregar" type="button" class="btn btn-primary">Agregar</button>
                    </div>
                    </div>
                </div>
            </div> <!-- /<div class="modal fade"> -->

            <img id="png_papelera" src="<?php echo esc_url(plugins_url('/pend-project/img/papelera.png')); ?>" title="Arrastra aquí cualquier tarjeta que quieras borrar...">

            </div> <!-- .papelera -->

            <script type="text/javascript">
                /*
                ** Vuelvo a cargar los elementos del DOM que han sido sustituidos
                ** y cuyas funciones y asociaciones a eventos han dejado de estar
                ** correctamente "enlazados".
                */
                const agregar_tarjeta = document.querySelector('#pepr_btn_agregar');
                const notas_pendientes = document.querySelectorAll('.card');
                const papelera = document.querySelector('#png_papelera');
                var idTarjetaSeleccionada = 0;
                var tarjetaSeleccionada;
                var editarActivo = false;

                notas_pendientes.forEach(nota => {
                    jQuery( function () {
                        jQuery(nota).draggable();
                        jQuery(nota).resizable();
                    });
                })

                jQuery(papelera).droppable({
                    drop: function(event, ui) {
                        if(confirm('¿Está seguro de que desea borrar esta tarjeta?')) {
                            jQuery(tarjetaSeleccionada).remove();

                            jQuery.ajax({
                                type: 'post',
                                url: ajaxurl,
                                data: {
                                    'action': 'peprDELL',
                                    'nonce': pepr_var.nonce,
                                    'id': idTarjetaSeleccionada
                                },
                                error: function (response) {
                                    console.log(response);
                                },
                                success: function (response) {
                                    console.log(response);
                                    jQuery("#pepr_add_tarjeta").modal('hide');
                                }
                            })

                        }
                    }
                });

                if(agregar_tarjeta) {
                    jQuery(agregar_tarjeta).click(() => {
                        jQuery("#pepr_add_tarjeta").modal('hide');
                        agregaEditaTarjeta();
                    });
                }

                jQuery('#png_agregar').click(function () {
                    reseteaFormulario();
                });

                jQuery("#pepr_add_tarjeta").on("hidden.bs.modal", function () {
                    editarActivo = false;
                });

                compruebaNavegador();
                cargaUbicacionesTarjetas();
            </script>
        <?php

        wp_die();

    }

    add_action( 'wp_ajax_peprALL', 'pepr_gets_tarjetas_bd' );


    // Recuperamos las funciones incluidas en el fichero: includes/pepr-functions.php
    require_once plugin_dir_path(__FILE__) . 'includes/pepr-functions.php';
?>