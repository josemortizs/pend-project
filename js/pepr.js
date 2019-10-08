/*
** Inicializo variables para trabajar con elementos del DOM y
** y reseteo el formulario contenido en el Modal Bootstrap. 
*/
const agregar_tarjeta = document.querySelector('#pepr_btn_agregar');
const notas_pendientes = document.querySelectorAll('.card');
const papelera = document.querySelector('#png_papelera');
var idTarjetaSeleccionada = 0;
var tarjetaSeleccionada;
var editarActivo = false;
var ordenTabla = 'titulo';
var mensajeMostrado = false;
reseteaFormulario();
compruebaNavegador();

/*
** Esta función agrega una nueva tarjeta o edita una ya existente en función
** al valor de la variable editarActivo. 
** Lo hace mediante una llamada AJAX a las funciones peprADD y peprEDIT.
** Al estar trabajando con AJAX el fichero PHP de destino de la petición es 
** siempre el mismo y la ruta de éste está almacenada en la variable ajaxurl.
** Dicha ruta se almacena automaticamente, lo hace WordPress.
*/

function agregaEditaTarjeta() {
    if(!editarActivo) {
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                'action': 'peprADD',
                'nonce': pepr_var.nonce,
                'titulo': jQuery('#pepr_titulo').val(), 
                'proyecto': jQuery('#pepr_proyecto').val(),
                'descripcion': jQuery('#pepr_descripcion').val(),
                'prioridad': jQuery('#pepr_prioridad option:selected').text(),
                'periodicidad': jQuery('#pepr_periodicidad option:selected').text()
            },
            error: function (response) {
                console.log(response);
            },
            success: function (response) {
                jQuery("#pepr_add_tarjeta").modal('hide');
                actualizaInterfaz();
                reseteaFormulario();
            }
        })
    }
    else {
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                'action': 'peprEDIT',
                'nonce': pepr_var.nonce,
                'id' : idTarjetaSeleccionada,
                'titulo': jQuery('#pepr_titulo').val(), 
                'proyecto': jQuery('#pepr_proyecto').val(),
                'descripcion': jQuery('#pepr_descripcion').val(),
                'prioridad': jQuery('#pepr_prioridad option:selected').text(),
                'periodicidad': jQuery('#pepr_periodicidad option:selected').text()
            },
            error: function (response) {
                console.log(response);
                editarActivo = false;
            },
            success: function (response) {
                jQuery("#pepr_add_tarjeta").modal('hide');
                reseteaFormulario();
                actualizaInterfaz();
                editarActivo = false;
            }
        })

    }
}


/*
** Esta función recibe como parámetro el id de la tarjeta a eliminar
** y lo envía a la función peprDELL para su eliminación, vía AJAX.
*/

function eliminarTarjeta(id) {

    idTarjetaSeleccionada = id;
    tarjetaSeleccionada = document.getElementById(id);

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
                console.log('Error: ' + response);
            },
            success: function (response) {
                jQuery("#pepr_add_tarjeta").modal('hide');
            }
        })

    }
}


/*
** Esta función recibe como parámetro el id de la tarjeta a editar
** y lo envía a la función peprGET para recuperar, vía AJAX, la información
** de esa tarjeta. Tras recuperarla asigna a los valores del formalario MODAL 
** los valores recuperados para que puedan ser modificados.
*/

function editarTarjeta(id) {
    idTarjetaSeleccionada = id;
    tarjetaSeleccionada = document.getElementById(id);
    editarActivo = true;
    
    jQuery.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
            'action': 'peprGET',
            'nonce': pepr_var.nonce,
            'id': id
        },
        error: function (response) {
            console.log('Error: ' + response);
        },
        success: function (response) {
            const tarjeta = JSON.parse(response);
            document.querySelector('#pepr_titulo').value = tarjeta[0].titulo;
            document.querySelector('#pepr_proyecto').value = tarjeta[0].proyecto;
            document.querySelector('#pepr_descripcion').value = tarjeta[0].descripcion;
            document.querySelector('#pepr_prioridad').value = tarjeta[0].prioridad;
            document.querySelector('#pepr_periodicidad').value = tarjeta[0].periodicidad;

            jQuery("#pepr_add_tarjeta").modal('show');
        }
    })
}


/*
** Esta función recibe como parámetro el id de la tarjeta que
** está siendo movida y asigna dicho valor a las variables 
** idTarjetaSeleccionada y tarjetaSeleccionada. Estos valores
** serán usados, a posteriori, si desease eliminar dicha tarjeta
** mediante la acción drag & drop sobre el icono de la papelera.
*/

function estoyMoviendo(id) {
    idTarjetaSeleccionada = id;
    tarjetaSeleccionada = document.getElementById(id);
    actualizaUbicacionTarjeta(idTarjetaSeleccionada);
}


/*
** Esta función resetea los valores del formulario MODAL
** evitando que muestre algunos valores anteriormente contenidos
** en sus campos de texto.
*/

function reseteaFormulario() {
    document.querySelector('#pepr_titulo').value = "";
    document.querySelector('#pepr_proyecto').value = "";
    document.querySelector('#pepr_descripcion').value = "";
}


/*
** Esta función recibe como parámetro el orden que el usuario elige
** para la disposición de las tarjetas. Los almacena en la variable
** orden y actualiza la interfaz mediante la llamada a la función
** actualizaInterfaz()
*/

function ordenar(orden) {
    localStorage.clear();
    ordenTabla = orden;
    actualizaInterfaz();
}


/*
** Esta función realiza una petición, vía AJAX, a la función peprALL
** y asigna el valor devuelto (las tarjetas ordenadas) al DIV cuya 
** clase es container.
*/

function actualizaInterfaz() {
    
    jQuery.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
            'action': 'peprALL',
            'nonce': pepr_var.nonce,
            'orden': ordenTabla
        },
        error: function (response) {
            console.log('Error: ' + response);
        },
        success: function (response) {
            jQuery('.container').html(response);
        }
    })

}


/*
** Comprobamos si Pend Project se está ejectuando en un navegdor mobile para deshabilitar la papelera.
** En caso de ejecutarse en un navegador de PC eliminamos los botones de eliminar ubicados dentro
** de la tarjeta ya que para eliminar podemos arrastrar la tarjeta a la papelera.
*/

function compruebaNavegador() {

    if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

        jQuery('#png_papelera').hide();
        
        if(!mensajeMostrado) {
            alert('Pulsa dos veces sobre el título de cualquier tarjeta para modificar su contenido, ¡Gracias por usar Pend Project!');
            mensajeMostrado = true;
        }
    
    }
    else {
    
        const botones_dell = document.querySelectorAll('.pepr-eliminar');
        
        if(botones_dell) {
            botones_dell.forEach(boton => {
                jQuery( function () {
                    jQuery(boton).css('display', 'none');
                });
            })
        }
    }

}


/*
** Capturamos el evento click realizado sobre al imagen cuya id
** es png_agregar para resetear los campos del formulario antes de
** que se muestre el MODAL con éstos.
*/

jQuery('#png_agregar').click(function () {
    reseteaFormulario();
});


/*
** Capturamos el evento de ocultación del modal y establecemos el
** valor de editarActivo en false.
*/

jQuery("#pepr_add_tarjeta").on("hidden.bs.modal", function () {
    editarActivo = false;
});


/*
** Recorremos notas_pendientes, array que almacena las referencias a 
** las notas existentes en el DOM, mediante un foreach y le asignamos
** a todas ellas la capacidad de que se pueda cambiar su tamaño y 
** puedan ser arrastradas a otra posición.
*/

if(notas_pendientes) {
    notas_pendientes.forEach(nota => {
        jQuery( function () {
            jQuery(nota).draggable();
            jQuery(nota).resizable();
        });
    })
}


/*
** Establezco la papelera como objeto "droppable" para habilitar el que se pueda
** soltar sobre dicha papelera la tarjeta que se quiera borrar. Una vez soltada
** se llama a la función eliminarTarjeta();
*/

if(papelera) {
    jQuery(papelera).droppable({
        drop: function(event, ui) {
            eliminarTarjeta(idTarjetaSeleccionada);
        }
    });
}


/*
** Capturamos el evento click del botón del MODAL: Agregar para 
** ocultar la tarjeta y lanzar la función agregaEditaTarjeta();
*/

if(agregar_tarjeta) {
    jQuery(agregar_tarjeta).click(() => {
        jQuery("#pepr_add_tarjeta").modal('hide');
        agregaEditaTarjeta();
    });
}


/* ***************************************************************************************************** */
/* Versión 1.1 - Agregamos opciones para mantener el posicionamiento de las tarjetas usando LocalStorage */
/* ***************************************************************************************************** */


/*
** Clase: Posiciones
** Creada para almacenar las posiciones de las tarjetas en una determinada resolución.
*/

class Posiciones {
    constructor(id, ancho, alto, top, left) {
        this.id = id;
        this.ancho = ancho;
        this.alto = alto;
        this.top = top;
        this.left = left;
    }
}


/*
** Esta función recibe como parámetro el id de la tarjeta que
** está siendo movida y actualiza, o almacena, la posición de 
** la tarjeta en el Local Storage.
*/

function actualizaUbicacionTarjeta(id) {

    var coordenadas = jQuery('#'+id).offset();
    var ancho = window.innerWidth;
    var alto = window.innerHeight;

    var posicion = new Posiciones(id, ancho, alto, coordenadas.top, coordenadas.left);

    localStorage.setItem(id, JSON.stringify(posicion));
}

function cargaUbicacionesTarjetas() {
    for (let i = 0; i < localStorage.length; i++) {
        
        let id = localStorage.key(i);

        let tarjeta = document.getElementById(id);

        if(tarjeta) {
            let ancho = window.innerWidth;
            let alto = window.innerHeight;
            let posicion = JSON.parse(localStorage.getItem(id));
            if(posicion.ancho == ancho && posicion.alto == alto) jQuery('#'+id).offset({top: posicion.top, left: posicion.left});
        }
                
    }
}

cargaUbicacionesTarjetas();