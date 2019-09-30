<?php

    /*
    ** WordPress dispone de un objeto de clase con métodos para trabajar directamente con la base de datos.
    ** Esta clase se denomina "wpdb" y se encuentra en wp-includes/wp-db.php. Esta clase permite ejecutar
    ** consultas con la máxima seguridad posible. 
    ** Bibliografía: WordPress 4.1 Diseño y desarrollo - Anaya Multimedia - Brad Williams, David Damstra y Hal Stern
    ** Capítulo 6 - Administración de datos.
    */

    global $wpdb;

    $tabla = $wpdb->prefix . 'pepr_notas';

    $notas_pendientes = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tabla ORDER BY %s", 'titulo'));

    /*
    ** Realizo la consulta y almaceno los datos en el array $notas_pendientes.
    ** A continuación usaré un bucle foreach para recorrerlo y mostrar la información
    ** que necesitemos. El código PHP estará incrustado entre código HTML5.
    */
?>

<div class="container">

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

    <?php foreach ($notas_pendientes as $nota) { ?>

        <?php
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
    <?php } ?>

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
        </div>

        <img id="png_papelera" src="<?php echo esc_url(plugins_url('/pend-project/img/papelera.png')); ?>" title="Arrastra aquí cualquier tarjeta que quieras borrar...">

    </div> <!-- .papelera -->


</div> <!-- .container -->