// Initialize mock users in localStorage if not already present
function initializeUsers() {
    if (!localStorage.getItem('dental_clinic_users')) {
        const users = [
            { username: 'admin', password: 'password123', role: 'Administrador', fullName: 'Admin User' },
            { username: 'odontologo1', password: 'password123', role: 'Odontologo', fullName: 'Dr. Smith' },
            { username: 'asistente1', password: 'password123', role: 'Asistente', fullName: 'Asistente Paty' }
        ];
        localStorage.setItem('dental_clinic_users', JSON.stringify(users));
    }
}

// Call initialization on script load
initializeUsers();

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const loginMessage = document.getElementById('loginMessage');

    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;

            if (!username || !password || !role) {
                displayMessage('Por favor, complete todos los campos.', 'error', loginMessage);
                return;
            }

            const users = JSON.parse(localStorage.getItem('dental_clinic_users')) || [];
            const foundUser = users.find(user => 
                user.username === username && 
                user.password === password && 
                user.role === role
            );

            if (foundUser) {
                displayMessage(`¡Bienvenido ${foundUser.role} ${foundUser.fullName || foundUser.username}! Acceso concedido.`, 'success', loginMessage);
                // Here you would typically redirect to a dashboard or role-specific page
                // For example: window.location.href = `/${role.toLowerCase()}_dashboard.html`;
                
                // For now, just show a message and clear the form.
                loginForm.reset();

                // Display role capabilities (conceptual, not functional in this demo)
                let capabilities = '';
                switch(role) {
                    case 'Administrador':
                        capabilities = 'Usted tiene control total del sistema.';
                        break;
                    case 'Odontologo':
                        capabilities = 'Usted puede gestionar pacientes y citas.';
                        break;
                    case 'Asistente':
                        capabilities = 'Usted puede gestionar la agenda de citas y recepción.';
                        break;
                    case 'Paciente':
                        capabilities = 'Usted puede ver sus citas y gestionar su perfil.';
                        break;
                }
                setTimeout(() => { // Show capabilities after welcome message
                     displayMessage(`¡Bienvenido ${foundUser.role} ${foundUser.fullName || foundUser.username}! ${capabilities}`, 'success', loginMessage);
                }, 1000);


            } else {
                displayMessage('Nombre de usuario, contraseña o rol incorrectos.', 'error', loginMessage);
            }
        });
    }
});

function displayMessage(message, type, element) {
    if (!element) return;
    element.textContent = message;
    element.className = `message ${type}`; // Ensure 'message' base class is always present
    element.style.display = 'block';

    setTimeout(() => {
        if (element.className.includes(type)) { // only hide if it's still the same message
             // element.style.display = 'none';
             // element.textContent = '';
        }
    }, 5000); // Hide message after 5 seconds
}

