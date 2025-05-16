// This script will be imported as a module due to:
// <script type="importmap"> { "imports": { "app": "./script.js" } } </script>
// <script type="module"> import 'app'; </script>

function main() {
    const citaForm = document.getElementById('cita-form');

    if (citaForm) {
        citaForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(citaForm);
            const citaData = {};
            formData.forEach((value, key) => {
                // For checkbox groups or multi-selects, you might need special handling
                // For this form, direct assignment is fine.
                citaData[key] = value;
            });

            // Basic validation for date and time
            const fechaCita = new Date(citaData.fecha_cita + 'T' + citaData.hora_cita);
            const hoy = new Date();
            
            // Set hours to 0 to compare dates only for past date validation
            const hoyDateOnly = new Date();
            hoyDateOnly.setHours(0,0,0,0);
            const fechaCitaDateOnly = new Date(citaData.fecha_cita);
            fechaCitaDateOnly.setHours(0,0,0,0); // Adjust for timezone offset issues

            if (fechaCitaDateOnly.getTime() < hoyDateOnly.getTime()) {
                 alert('No puede seleccionar una fecha de cita en el pasado.');
                 return;
            }


            console.log('Datos de la Cita:', citaData);
            // In a real application, you would send this data to a server.
            // For example: fetch('/api/agendar-cita', { method: 'POST', body: JSON.stringify(citaData), headers: {'Content-Type': 'application/json'} })
            
            alert('¡Cita solicitada con éxito!\nNos pondremos en contacto para confirmar.\n(Los datos han sido registrados en la consola del navegador)');
            
            // Optionally, reset the form after successful submission
            // citaForm.reset();
        });
    }

    // Set min date for date inputs to today
    const today = new Date().toISOString().split('T')[0];
    const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
    if (fechaNacimientoInput) {
        fechaNacimientoInput.max = today; // Cannot be born in the future
    }
    const fechaCitaInput = document.getElementById('fecha_cita');
    if (fechaCitaInput) {
        fechaCitaInput.min = today;
    }
}

// Run the main function when the module is loaded
main();

