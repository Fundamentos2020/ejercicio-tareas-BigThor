const selectCategoria = document.getElementById('filtro-categoria');
const contenedorTareas = document.getElementById('contenedor-tareas');

var categorias = [];

cargarCategorias();

function cargarCategorias(){
    const xhr = new XMLHttpRequest();

    xhr.open('GET', './Controllers/categoriasController.php', true);

    xhr.onload = function() {
        if(this.status == 200){
            categorias = JSON.parse(this.responseText);

            // Vaciando las categorias existentes en el select
            for(var key in categorias){
                var nuevaCategoria = `
                    <option value="${categorias[key].id}">${categorias[key].nombre}</option>
                `
                selectCategoria.innerHTML += nuevaCategoria;
            };
        }
    };

    xhr.send();

    cargarTareas();
}

function cargarTareas(){
    const xhr = new XMLHttpRequest();
    const categoria_selec = selectCategoria.value;

    // No hay categoria seleccionada
    if(categoria_selec == -1){
        xhr.open('GET', './Controllers/tareasController.php', true);
    }
    else {
        xhr.open('GET', './Controllers/tareasController.php?categoria_id=' + categoria_selec, true);
    }

    xhr.onload = function() {
        if(this.status == 200){
            const tareas = JSON.parse(this.responseText);

            // Vaciando las tareas seleccionadas en el cuerpo
            contenedorTareas.innerHTML = "";
            tareas.forEach(function(tarea){
                var nuevaTarea = `
                <div class="col-s-12">
                    <hr>
                    <p class="text-right m-1"><i>${categorias.find(c => c.id == tarea.categoria_id).nombre}`
                    
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
        }
    };

    xhr.send();
}