import { initialAppointmentsData, initialAvailableTimesData } from 'config';

document.addEventListener('DOMContentLoaded', () => {
    const appointmentsTbody = document.getElementById('appointments-tbody');
    const availableTimesDisplay = document.getElementById('available-times-display');

    // Clonamos los datos para poder modificarlos sin afectar el módulo original si se re-renderiza
    let appointmentsData = JSON.parse(JSON.stringify(initialAppointmentsData));
    const availableTimesData = JSON.parse(JSON.stringify(initialAvailableTimesData));

    function renderAppointments() {
        if (!appointmentsTbody) return;
        appointmentsTbody.innerHTML = ''; // Limpiar contenido previo

        appointmentsData.forEach(appointment => {
            const tr = document.createElement('tr');

            const tdPatient = document.createElement('td');
            tdPatient.textContent = appointment.patient;
            tr.appendChild(tdPatient);

            const tdDate = document.createElement('td');
            tdDate.textContent = appointment.date;
            tr.appendChild(tdDate);

            const tdTime = document.createElement('td');
            tdTime.textContent = appointment.time;
            tr.appendChild(tdTime);

            const tdObservations = document.createElement('td');
            const observationsTextarea = document.createElement('textarea');
            observationsTextarea.value = appointment.observations;
            observationsTextarea.setAttribute('aria-label', `Observaciones para ${appointment.patient}`);
            
            observationsTextarea.addEventListener('input', (event) => {
                const aptToUpdate = appointmentsData.find(a => a.id === appointment.id);
                if (aptToUpdate) {
                    aptToUpdate.observations = event.target.value;
                }
                // console.log('Datos de citas actualizados:', appointmentsData); // Para depuración
            });
            
            tdObservations.appendChild(observationsTextarea);
            tr.appendChild(tdObservations);

            appointmentsTbody.appendChild(tr);
        });
    }

    function renderAvailableTimes() {
        if (!availableTimesDisplay) return;
        availableTimesDisplay.innerHTML = ''; // Limpiar contenido previo

        if (availableTimesData.length === 0) {
            availableTimesDisplay.textContent = "No hay tiempos disponibles actualmente.";
            return;
        }

        availableTimesData.forEach(dateGroup => {
            const dateGroupDiv = document.createElement('div');
            dateGroupDiv.className = 'date-group';

            const dateHeader = document.createElement('h3');
            dateHeader.textContent = formatDate(dateGroup.date); // Formatear fecha si es necesario
            dateGroupDiv.appendChild(dateHeader);

            if (dateGroup.times.length > 0) {
                const timesParagraph = document.createElement('p');
                dateGroup.times.forEach(time => {
                    const timeSlotSpan = document.createElement('span');
                    timeSlotSpan.className = 'time-slot';
                    timeSlotSpan.textContent = time;
                    timesParagraph.appendChild(timeSlotSpan);
                });
                dateGroupDiv.appendChild(timesParagraph);
            } else {
                const noTimesP = document.createElement('p');
                noTimesP.textContent = "No hay horarios disponibles para esta fecha.";
                dateGroupDiv.appendChild(noTimesP);
            }
            availableTimesDisplay.appendChild(dateGroupDiv);
        });
    }

    // Función auxiliar para formatear fechas (opcional, podría ser más compleja)
    function formatDate(dateString) {
        // Simple reformateo, se podría usar una librería para algo más robusto
        const [year, month, day] = dateString.split('-');
        return `${day}/${month}/${year}`;
    }

    // Renderizar contenido inicial
    renderAppointments();
    renderAvailableTimes();
});

