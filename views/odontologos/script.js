import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction'; // for selectable, draggable, etc.
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('eventModal');
    const closeModalButton = modal.querySelector('.close-button');
    const modalTitle = document.getElementById('modalTitle');
    const modalPatientName = document.getElementById('modalPatientName');
    const modalReason = document.getElementById('modalReason');
    const modalTime = document.getElementById('modalTime');
    const modalContact = document.getElementById('modalContact');
    const modalNotes = document.getElementById('modalNotes');

    const navLinks = document.querySelectorAll('.navbar ul li a');
    const pageContents = document.querySelectorAll('.page-content');
    const calendarWrapper = document.getElementById('calendar-wrapper');
    const calendarPageTitleEl = document.getElementById('calendar-view-title');

    let earningsChartInstance = null; // To keep track of the earnings chart

    const today = new Date();
    const todayStrFn = (d) => d.toISOString().slice(0,10);
    const addDays = (date, days) => {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
    };

    const day0 = todayStrFn(today); // Current day
    const day1 = todayStrFn(addDays(today, 1)); // Tomorrow
    const day2 = todayStrFn(addDays(today, 2)); // Day after tomorrow

    // For appointments in different months (for testing filtering)
    let currentMonthNextYearDay;
    // Ensure nextMonthDay is actually in the next month, not just +30 days
    const tempNextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 5); // 5th of next month
    const nextMonthDay = todayStrFn(tempNextMonth);

    const tempPrevMonth = new Date(today.getFullYear(), today.getMonth() - 1, 15); // 15th of prev month
    const prevMonthDay = todayStrFn(tempPrevMonth);

    const sampleAppointments = [
        {
            id: '1', 
            title: 'Limpieza Dental', 
            start: `${day0}T09:00:00`, 
            end: `${day0}T10:00:00`,
            extendedProps: { 
                patientName: 'Ana Pérez', 
                reason: 'Limpieza Dental Rutinaria', 
                contact: '555-123-4567', 
                notes: 'Paciente refiere sensibilidad en molares inferiores.', 
                cost: 60, 
                currency: '€' 
            },
            backgroundColor: '#3498db', 
            borderColor: '#2980b9'
        },
        {
            id: '2', 
            title: 'Consulta Ortodoncia', 
            start: `${day0}T11:00:00`, 
            end: `${day0}T11:45:00`,
            extendedProps: { 
                patientName: 'Carlos López', 
                reason: 'Ajuste de Brackets', 
                contact: '555-987-6543', 
                notes: 'Revisar alineación y cambiar ligas.', 
                cost: 80, 
                currency: '€' 
            },
            backgroundColor: '#2ecc71', 
            borderColor: '#27ae60'
        },
        {
            id: '3', 
            title: 'Extracción Muela', 
            start: `${day0}T14:30:00`, 
            end: `${day0}T15:30:00`,
            extendedProps: { 
                patientName: 'Sofía Rodríguez', 
                reason: 'Extracción Muela del Juicio Inferior Derecha', 
                contact: '555-456-7890', 
                notes: 'Paciente ansiosa, considerar sedación leve. Premedicación indicada.', 
                cost: 120, 
                currency: '€' 
            },
            backgroundColor: '#e74c3c', 
            borderColor: '#c0392b'
        },
        {
            id: '4', 
            title: 'Blanqueamiento Dental', 
            start: `${day1}T10:00:00`, 
            end: `${day1}T10:45:00`,
            extendedProps: { 
                patientName: 'Luis Gómez', 
                reason: 'Sesión de Blanqueamiento', 
                contact: '555-111-2222', 
                notes: 'Primera sesión de blanqueamiento. Verificar sensibilidad.', 
                cost: 200, 
                currency: '€' 
            },
            backgroundColor: '#f39c12', 
            borderColor: '#e67e22'
        },
        {
            id: '5', 
            title: 'Revisión General', 
            start: `${day2}T16:00:00`, 
            end: `${day2}T17:00:00`,
            extendedProps: { 
                patientName: 'Laura Torres', 
                reason: 'Chequeo semestral', 
                contact: '555-333-4444', 
                notes: 'Sin problemas reportados.', 
                cost: 50, 
                currency: '€' 
            },
            backgroundColor: '#9b59b6', 
            borderColor: '#8e44ad'
        },
        // Appointment for next month (for testing filter)
        {
            id: '6', 
            title: 'Implante Consulta', 
            start: `${nextMonthDay}T09:30:00`, 
            end: `${nextMonthDay}T10:15:00`,
            extendedProps: { 
                patientName: 'Miguel Ángel Sanz', 
                reason: 'Consulta para Implante', 
                contact: '555-555-5555', 
                notes: 'Radiografías necesarias.', 
                cost: 70, 
                currency: '€' 
            },
            backgroundColor: '#1abc9c', 
            borderColor: '#16a085'
        },
        // Appointment for previous month (for testing filter)
        {
            id: '7', 
            title: 'Endodoncia', 
            start: `${prevMonthDay}T14:00:00`, 
            end: `${prevMonthDay}T15:30:00`,
            extendedProps: { 
                patientName: 'Elena Vargas', 
                reason: 'Tratamiento de conducto', 
                contact: '555-666-7777', 
                notes: 'Dolor agudo.', 
                cost: 250, 
                currency: '€' 
            },
            backgroundColor: '#d35400', 
            borderColor: '#c24100'
        }
    ];

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: sampleAppointments,
        editable: true,
        selectable: true,
        slotMinTime: '08:00:00',
        slotMaxTime: '20:00:00',
        allDaySlot: false,
        height: 'auto',
        locale: 'es',
        buttonText: {
            today:    'Hoy',
            month:    'Mes',
            week:     'Semana',
            day:      'Día',
            list:     'Lista'
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        eventContent: function(arg) {
            let patientName = arg.event.extendedProps.patientName || arg.event.title;
            let reason = arg.event.extendedProps.reason || '';
            let html = `
                <div class="fc-event-main-custom">
                    <strong>${patientName}</strong>
                    <em>${reason.substring(0, 30)}${reason.length > 30 ? '...' : ''}</em>
                </div>
            `;
            return { html: html };
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault(); 
            const event = info.event;
            modalTitle.textContent = event.title;
            modalPatientName.textContent = event.extendedProps.patientName || 'N/A';
            modalReason.textContent = event.extendedProps.reason || 'N/A';
            const startTime = event.start ? event.start.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: false }) : 'N/A';
            const endTime = event.end ? event.end.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: false }) : 'N/A';
            modalTime.textContent = `${startTime} - ${endTime}`;
            modalContact.textContent = event.extendedProps.contact || 'N/A';
            modalNotes.textContent = event.extendedProps.notes || 'Sin notas adicionales.';
            modal.style.display = 'flex';
        },
        select: function(info) {
            console.log('Selected: ' + info.startStr + ' to ' + info.endStr);
            // Here you could open a form to create a new event
            calendar.unselect();
        }
    });

    calendar.render();

    function displayPatientsData() {
        const patientListTableContainer = document.getElementById('patientListTableContainer');
        const earningsSummaryEl = document.getElementById('earningsSummary');
        const earningsChartCanvas = document.getElementById('earningsChart');

        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth(); // 0-11

        const appointmentsThisMonth = sampleAppointments.filter(appt => {
            const apptDate = new Date(appt.start);
            return apptDate.getFullYear() === currentYear && apptDate.getMonth() === currentMonth;
        });

        appointmentsThisMonth.sort((a, b) => new Date(a.start) - new Date(b.start));

        patientListTableContainer.innerHTML = ''; 
        if (appointmentsThisMonth.length === 0) {
            patientListTableContainer.innerHTML = '<p style="padding: 15px; text-align: center;">No hay pacientes programados para este mes.</p>';
        } else {
            const table = document.createElement('table');
            table.className = 'patients-table';
            const thead = table.createTHead();
            const headerRow = thead.insertRow();
            ['Paciente', 'Fecha y Hora', 'Motivo', 'Costo'].forEach(text => {
                const th = document.createElement('th');
                th.textContent = text;
                headerRow.appendChild(th);
            });

            const tbody = table.createTBody();
            appointmentsThisMonth.forEach(appt => {
                const row = tbody.insertRow();
                const startDate = new Date(appt.start);
                const currencySymbol = appt.extendedProps.currency || '$';
                row.insertCell().textContent = appt.extendedProps.patientName;
                row.insertCell().textContent = `${startDate.toLocaleDateString('es-ES', {day: '2-digit', month: '2-digit', year: 'numeric'})} ${startDate.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: false })}`;
                row.insertCell().textContent = appt.extendedProps.reason;
                row.insertCell().textContent = `${(appt.extendedProps.cost || 0).toFixed(2)} ${currencySymbol}`;
            });
            patientListTableContainer.appendChild(table);
        }

        const totalEarnings = appointmentsThisMonth.reduce((sum, appt) => sum + (appt.extendedProps.cost || 0), 0);
        const mainCurrency = sampleAppointments.length > 0 ? (sampleAppointments[0].extendedProps.currency || '$') : '$';
        earningsSummaryEl.innerHTML = `<h3>Ganancias Totales del Mes: ${totalEarnings.toFixed(2)} ${mainCurrency}</h3>`;

        const earningsByService = appointmentsThisMonth.reduce((acc, appt) => {
            const service = appt.title || 'Desconocido';
            acc[service] = (acc[service] || 0) + (appt.extendedProps.cost || 0);
            return acc;
        }, {});

        const chartLabels = Object.keys(earningsByService);
        const chartDataValues = Object.values(earningsByService);

        if (earningsChartInstance) {
            earningsChartInstance.destroy();
            earningsChartInstance = null;
        }
        
        const ctx = earningsChartCanvas.getContext('2d');
        if (chartLabels.length > 0) {
          earningsChartInstance = new Chart(ctx, {
              type: 'bar',
              data: {
                  labels: chartLabels,
                  datasets: [{
                      label: `Ganancias por Servicio (${mainCurrency})`,
                      data: chartDataValues,
                      backgroundColor: [
                          'rgba(54, 162, 235, 0.7)', 'rgba(75, 192, 192, 0.7)',
                          'rgba(255, 206, 86, 0.7)', 'rgba(153, 102, 255, 0.7)',
                          'rgba(255, 159, 64, 0.7)', 'rgba(255, 99, 132, 0.7)',
                          'rgba(100, 100, 205, 0.7)' 
                      ],
                      borderColor: [
                          'rgba(54, 162, 235, 1)', 'rgba(75, 192, 192, 1)',
                          'rgba(255, 206, 86, 1)', 'rgba(153, 102, 255, 1)',
                          'rgba(255, 159, 64, 1)', 'rgba(255, 99, 132, 1)',
                          'rgba(100, 100, 205, 1)'
                      ],
                      borderWidth: 1
                  }]
              },
              options: {
                  responsive: true,
                  maintainAspectRatio: false,
                  scales: {
                      y: {
                          beginAtZero: true,
                           ticks: {
                              callback: function(value) { return value + ` ${mainCurrency}`; }
                          }
                      }
                  },
                  plugins: {
                      legend: { display: chartLabels.length > 1, position: 'bottom' },
                      title: { display: true, text: 'Distribución de Ganancias por Servicio (Mes Actual)', font: { size: 16 } }
                  }
              }
          });
        } else {
            ctx.clearRect(0, 0, earningsChartCanvas.width, earningsChartCanvas.height);
            ctx.font = "16px Segoe UI";
            ctx.fillStyle = "#777";
            ctx.textAlign = "center";
            ctx.fillText("No hay datos de ganancias para graficar este mes.", earningsChartCanvas.width / 2, earningsChartCanvas.height / 2);
        }
    }

    function showSection(sectionId) {
        pageContents.forEach(content => {
            content.classList.remove('active');
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.dataset.section === sectionId) {
                link.classList.add('active');
            }
        });

        let targetContentElement;
        let isCalendarSection = false;

        if (sectionId === 'patients') {
            targetContentElement = document.getElementById(sectionId + '-content');
            if (targetContentElement) {
                targetContentElement.classList.add('active');
                displayPatientsData(); 
            }
        } else if (sectionId === 'appointments' || sectionId === 'full-calendar') {
            targetContentElement = calendarWrapper;
            isCalendarSection = true;
            if (sectionId === 'appointments') {
                calendarPageTitleEl.textContent = 'Agenda de Citas';
            } else { 
                calendarPageTitleEl.textContent = 'Calendario Completo';
            }
        } else {
            targetContentElement = document.getElementById(sectionId + '-content');
        }
        
        if (targetContentElement && sectionId !== 'patients') { // patients already handled
            targetContentElement.classList.add('active');
            if (isCalendarSection) {
                setTimeout(() => { // Ensure calendar is visible before resizing
                    calendar.updateSize();
                }, 50); 
            }
        }
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.dataset.section;
            showSection(sectionId);
        });
    });

    const initiallyActiveLink = document.querySelector('.navbar ul li a.active');
    if (initiallyActiveLink && initiallyActiveLink.dataset.section) {
        showSection(initiallyActiveLink.dataset.section);
    } else {
        showSection('dashboard'); 
    }

    closeModalButton.onclick = function() {
        modal.style.display = 'none';
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});