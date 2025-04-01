let grafica = null; // Variable para almacenar la gráfica

// Obtener datos de la base de datos
async function obtenerDatos(fechaInicio = '', fechaFin = '') {
    const response = await fetch(`datos.php?fechaInicio=${fechaInicio}&fechaFin=${fechaFin}`);
    if (!response.ok) {
        throw new Error('Error al obtener los datos del servidor');
    }
    return await response.json();
}

// Crear gráfica de barras
async function crearGraficaBarras(fechaInicio = '', fechaFin = '') {
    const datos = await obtenerDatos(fechaInicio, fechaFin);
    const etiquetas = datos.map(d => d.producto);
    const valores = datos.map(d => d.cantidad_vendida);

    const ctx = document.getElementById('graficaBarras').getContext('2d');

    // Si ya existe una gráfica, destrúyela antes de crear una nueva
    if (grafica) {
        grafica.destroy();
    }

    // Crear una nueva instancia de la gráfica
    grafica = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Cantidad de productos vendidos ✔️',
                data: valores,
                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                borderColor: 'black',
                borderWidth: 2
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Manejar el formulario de fechas
document.getElementById('rangoFechasForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;

    // Crear la gráfica con los filtros aplicados
    crearGraficaBarras(fechaInicio, fechaFin);
});

// Inicializar la gráfica con todos los datos
crearGraficaBarras();
