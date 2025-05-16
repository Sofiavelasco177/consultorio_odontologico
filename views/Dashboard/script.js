document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-links a');
    const contentTitleElement = document.getElementById('current-section-title');
    const contentAreaElement = document.getElementById('content-area');

    const sectionContent = {
        dashboard: {
            title: 'Dashboard',
            html: `
                <h2>Resumen General</h2>
                <p>Bienvenido al panel de administración. Aquí podrás ver un resumen general de la actividad de la clínica, estadísticas clave y accesos directos a las funciones más utilizadas.</p>
                <!-- Placeholder for charts or summary cards -->
                <div style="display: flex; gap: 20px; margin-top: 20px;">
                    <div style="background: white; padding: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex:1;">
                        <h4>Citas Hoy</h4><p style="font-size: 2em; margin:0;">12</p>
                    </div>
                    <div style="background: white; padding: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex:1;">
                        <h4>Pacientes Activos</h4><p style="font-size: 2em; margin:0;">345</p>
                    </div>
                    <div style="background: white; padding: 15px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex:1;">
                        <h4>Ingresos Mes</h4><p style="font-size: 2em; margin:0;">$7,850</p>
                    </div>
                </div>
            `
        },
        usuarios: {
            title: 'Gestión de Usuarios',
            html: `
                <h2>Usuarios del Sistema</h2>
                <p>Aquí se mostrará una tabla o lista de usuarios (administradores, recepcionistas, etc.) con opciones para agregar, editar y eliminar perfiles y permisos.</p>
                <!-- Placeholder for user table -->
                <button style="padding: 10px 15px; background-color: #3498db; color: white; border: none; border-radius: 3px; cursor: pointer;">Agregar Nuevo Usuario</button>
            `
        },
        pacientes: {
            title: 'Pacientes y Tratamientos',
            html: `
                <h2>Listado de Pacientes</h2>
                <p>Busca y selecciona un paciente para ver su información detallada, historial médico y tratamientos realizados o en curso. Podrás registrar nuevos tratamientos y actualizar la información del paciente.</p>
                <!-- Placeholder for patient list/search and details -->
                <input type="text" placeholder="Buscar paciente..." style="padding: 10px; width: 300px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 3px;">
            `
        },
        citas: {
            title: 'Gestión de Citas',
            html: `
                <h2>Calendario de Citas</h2>
                <p>Visualiza el calendario de citas por día, semana o mes. Agenda nuevas citas, reprograma o cancela existentes. Filtra por odontólogo o consultorio.</p>
                <!-- Placeholder for appointments calendar/list -->
                <div style="background: #ccc; height: 300px; display:flex; align-items:center; justify-content:center; color: #666; margin-top:15px;">Calendario (Placeholder)</div>
            `
        },
        odontologos: {
            title: 'Odontólogos',
            html: `
                <h2>Equipo de Odontólogos</h2>
                <p>Administra la información de los odontólogos: datos personales, especialidades, horarios de atención y asignación a consultorios. Aquí podrás agregar nuevos profesionales o editar los existentes.</p>
                <!-- Placeholder for dentists list -->
            `
        },
        consultorios: {
            title: 'Gestión de Consultorios',
            html: `
                <h2>Consultorios y Equipamiento</h2>
                <p>Consulta la lista de consultorios, su disponibilidad, equipamiento asociado y el calendario de asignación a odontólogos. Puedes añadir nuevos consultorios o modificar los existentes.</p>
                <!-- Placeholder for offices list -->
            `
        }
    };

    function loadContent(sectionKey) {
        const section = sectionContent[sectionKey];
        if (section) {
            contentTitleElement.textContent = section.title;
            contentAreaElement.innerHTML = section.html;
        } else {
            contentTitleElement.textContent = 'Error';
            contentAreaElement.innerHTML = '<p>Contenido no encontrado para la sección: ' + sectionKey + '</p>';
        }
        // Scroll content area to top
        contentAreaElement.scrollTop = 0;
    }

    navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            const sectionKey = link.getAttribute('data-section');
            loadContent(sectionKey);
        });
    });

    // Load initial content (Dashboard) and set active link
    const initialSection = 'dashboard';
    const initialLink = document.querySelector(`.nav-links a[data-section="${initialSection}"]`);
    if (initialLink) {
        initialLink.classList.add('active');
        loadContent(initialSection);
    } else {
        // Fallback if dashboard link isn't found (should not happen with current HTML)
        if (navLinks.length > 0) {
            navLinks[0].classList.add('active');
            loadContent(navLinks[0].getAttribute('data-section'));
        }
    }
});

