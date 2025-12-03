const API = 'api.php';
const TOKEN = 'e1538579-28dc-4dda-b5c0-ab5555fe6616';
const url = 'https://wheelofnames.com/api/v2/wheels';

const headers = {
    'Content-Type': 'application/json',
    'x-api-key': TOKEN,
}

// ===============================
// Cargar (GET)
// ===============================
function cargar() {
    // Usamos fetch(API) por defecto es GET
    fetch(API)
        .then(res => {
            // Manejar errores de respuesta no 2xx
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        })
        .then(data => {
            const tabla = document.getElementById("lista");
            tabla.innerHTML = "";
            
            // Renderizar datos en la tabla
            data.forEach(item => {
                // Asegurarse de que el ID es tratado como número
                const itemId = parseInt(item.id); 
                const itemNombre = item.nombre.replace(/'/g, "\\'"); // Escapar comillas para onclick

                tabla.innerHTML += `
                <tr>
                    <td>${item.id}</td>
                    <td>${item.nombre}</td>
                    <td>${item.disponibilidad == 1 ? "Sí" : "No"}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="mostrarEditar(${itemId}, '${itemNombre}', ${item.disponibilidad})">
                            Editar
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="eliminar(${itemId})">
                            Eliminar
                        </button>
                    </td>
                </tr>`;
            });
        })
        .catch(error => {
            console.error("Error al cargar los datos:", error);
            alert("Error al cargar los datos. Revisa la consola.");
        });
}
cargar();

// ===============================
// Agregar (POST)
// ===============================
function agregar() {
    const nombreInput = document.getElementById("nombre");
    const body = {
        nombre: nombreInput.value.trim(),
        // Convertir a 0 o 1, asumiendo un select de ID 'disp'
        disponibilidad: parseInt(document.getElementById("disp").value) 
    };

    if (body.nombre === "") {
        alert("El nombre no puede estar vacío.");
        return;
    }

    fetch(API, {
        method: "POST", // CORRECTO para crear
        headers,
        body: JSON.stringify(body)
    })
    .then(res => {
        if (!res.ok) return res.json().then(err => { throw new Error(err.error || "Error al agregar") });
        return res.json();
    })
    .then(() => {
        nombreInput.value = "";
        cargar();
    })
    .catch(error => {
        console.error("Error en POST:", error);
        alert(`Error al agregar: ${error.message}`);
    });
}

// ===============================
// Eliminar (DELETE) 
// ===============================
function eliminar(id) {
    if (!confirm(`¿Estás seguro de que quieres eliminar la opción con ID ${id}?`)) return;

    fetch(API, {
        method: "DELETE", 
        headers,
        // El body solo necesita el ID para la operación DELETE
        body: JSON.stringify({ id: id }) 
    })
    .then(res => {
        if (!res.ok) return res.json().then(err => { throw new Error(err.error || "Error al eliminar") });
        return res.json();
    })
    .then(() => cargar())
    .catch(error => {
        console.error("Error en DELETE:", error);
        alert(`Error al eliminar: ${error.message}`);
    });
}


// ===============================
// Mostrar modal editar
// ===============================
function mostrarEditar(id, nombre, disp) {
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_nombre").value = nombre;
    document.getElementById("edit_disp").value = disp;

    // Asegurarse de que Bootstrap esté cargado para usar Modal
    const modalElement = document.getElementById('modalEditar');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// ===============================
// Guardar cambios (PUT)
// ===============================
document.getElementById("btnGuardarCambios").addEventListener("click", () => {
    
    const body = {
        id: document.getElementById("edit_id").value,
        nombre: document.getElementById("edit_nombre").value.trim(),
        disponibilidad: parseInt(document.getElementById("edit_disp").value),
    };

    if (body.nombre === "") {
        alert("El nombre no puede estar vacío.");
        return;
    }
    
    fetch(API, {
        method: "PUT", 
        headers,
        body: JSON.stringify(body)
    })
    .then(res => {
        if (!res.ok) return res.json().then(err => { throw new Error(err.error || "Error al guardar cambios") });
        return res.json();
    })
    .then(() => {
        cargar();
        // Obtener la instancia y ocultar, es más robusto que crearla de nuevo
        bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide(); 
    })
    .catch(error => {
        console.error("Error en PUT:", error);
        alert(`Error al guardar cambios: ${error.message}`);
    });
});

// ===============================
// Generar Ruleta (Usando Wheel of Names API v2)
// ===============================
async function generarRuleta() {
    try {
        const response = await fetch('/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            // Enviar un cuerpo que PHP identifique como la acción de generación
            body: JSON.stringify({ action: 'generate_wheel' }) 
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        // 🟢 ÉXITO: La respuesta 'data' contendrá el enlace de Wheel of Names
        if (data && data.url) {
            alert(`¡Ruleta creada! URL: ${data.url}`);
            // Aquí puedes abrir el enlace o incrustarlo
            window.open(data.url, '_blank'); 
        } else {
            // Manejar errores de la API externa (si la respuesta no tiene URL)
            console.error("Error en la respuesta de la API externa:", data);
            alert("Error al crear la ruleta. Revisa la consola.");
        }

    } catch (error) {
        console.error("Error general en generarRuleta:", error);
        alert("Error al intentar crear la ruleta.");
    }
}


document.getElementById("btnAgregar").addEventListener("click", agregar);
document.getElementById("btnRuleta").addEventListener("click", generarRuleta);