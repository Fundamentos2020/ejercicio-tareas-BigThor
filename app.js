const selectCategoria = document.getElementById('filtro-categoria');
const contenedorTareas = document.getElementById('contenedor-tareas');
cargarDatos();


function cargarDatos(){

    var tareas = JSON.parse(document.querySelector('meta[name="tareas"]').content);
    var categorias = JSON.parse(document.querySelector('meta[name="categorias"]').content);

    // Vaciando las categorias existentes en el select
    for(var key in categorias){
        var nuevaCategoria = `
            <option value="${categorias[key].id}">${categorias[key].nombre}</option>
        `
        selectCategoria.innerHTML += nuevaCategoria;
    };

    // Vaciando las tareas seleccionadas en el cuerpo
    tareas.forEach(function(tarea){
        var nuevaTarea = `
        <div class="col-s-12">
            <hr>
            <p class="text-right m-1"><i>${categorias[tarea.categoria_id].nombre}`
            
        if(tarea.fecha_limite != null){
            nuevaTarea += ` - ${tarea.fecha_limite}`;
        }

        nuevaTarea += `
            </i></p>
            <div class="px-3">
                <h2 class="m-1">${tarea.titulo}</h2>
        `

        if(tarea.descripcion != null){
            nuevaTarea += `<p class="m-2">${tarea.descripcion}</p>`;
        }

        nuevaTarea += `
            </div>
        </div>
        `;

        contenedorTareas.innerHTML += nuevaTarea;
    });
    // funcion que devuelve el valor del select al anterior
    recordarSeleccion();
}

function recordarSeleccion(){
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const seleccion = urlParams.get('categoria_id');
    console.log(seleccion);
    console.log(selectCategoria.selectedIndex);
    console.log(selectCategoria.options);
    selectCategoria.selectedIndex = seleccion;
    console.log(selectCategoria.selectedIndex);
}