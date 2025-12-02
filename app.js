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
        // 1. Obtener lista actual de opciones de tu API (GET /api.php)
        const res = await fetch(API); 
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        const items = await res.json();

        // 2. Filtrar y mapear entradas al formato requerido { text: "string" }
        const entries = items
            .filter(i => i.disponibilidad == 1)
            .map(i => ({ text: i.nombre }));

        if (entries.length === 0) {
            document.getElementById("iframeRuleta").src = "";
            return alert("No hay opciones activas para la ruleta. Agrega o activa algunas.");
        }
        
        // --- 3. CONFIGURACIÓN DE ESTILO VIBRANTE Y DRAMÁTICO ---
        
        // Definir una paleta de colores vibrantes para la ruleta
        const vibrantColors = [
            { color: "#FF0077", enabled: true }, // Rosa Fucsia
            { color: "#00A8FF", enabled: true }, // Azul Eléctrico
            { color: "#FFEA00", enabled: true }, // Amarillo Brillante
            { color: "#8B00FF", enabled: true }, // Púrpura Intenso
            { color: "#FF5733", enabled: true }, // Naranja Corall
            { color: "#33FF57", enabled: true }, // Verde Neón
        ];

        // 4. CONSTRUIR EL PAYLOAD CUMPLIENDO LA ESTRUCTURA DE LA DOCUMENTACIÓN
        const wheelData = {
            shareMode: 'copyable',
            wheelConfig: {
                // Propiedades básicas y de contenido
                title: '🎉 ¡Ruleta de Opciones Dinámicas!',
                description: 'Generada automáticamente.',
                entries: entries,
                
                // Propiedades de Estilo y Comportamiento (Directamente en wheelConfig)
                spinTime: 12, // Duración del giro más larga (dramático)
                centerText: "¡GANADOR!", // Texto en el centro de la ruleta
                launchConfetti: true, // Efecto de confeti al ganar
                animateWinner: true, // Animación del ganador
                displayWinnerDialog: true, // Mostrar cuadro de diálogo de ganador
                
                // Sonidos
                duringSpinSound: "ticking-sound", // Sonido de tictac durante el giro
                duringSpinSoundVolume: 70, 
                afterSpinSound: "clapping-sound", // Sonido de aplausos al ganar
                afterSpinSoundVolume: 80,
                
                // Colores (usando la propiedad correcta: colorSettings)
                colorSettings: vibrantColors,

                // Otras propiedades para mejorar la apariencia
                drawShadow: true,
                pointerChangesColor: true,
                pageGradient: true
            },
        };

        // --- 5. Llamar a la API de Wheel of Names (POST) ---
        const apiResponse = await fetch(url, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(wheelData),
        });

        const jsonResponse = await apiResponse.json();

        if (jsonResponse?.data?.path) {
            const path = jsonResponse.data.path;
            const wheelUrl = `https://wheelofnames.com/${path}`;
            
            document.getElementById("iframeRuleta").src = wheelUrl;
            console.log(`Ruleta creada con éxito: ${wheelUrl}`);
        } else {
            console.error("Error al crear la ruleta en Wheel of Names API:", jsonResponse);
            alert("Error al crear la ruleta. Revisa el TOKEN y los logs de la consola.");
        }
    } catch (err) {
        console.error("Error general en generarRuleta:", err);
        alert(`Error al generar la ruleta: ${err.message}`);
    }
}


document.getElementById("btnAgregar").addEventListener("click", agregar);
document.getElementById("btnRuleta").addEventListener("click", generarRuleta);