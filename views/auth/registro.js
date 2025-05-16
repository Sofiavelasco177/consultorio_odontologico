document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const registerMessage = document.getElementById('registerMessage');

    // Ensure localStorage is initialized for users (copied from script.js for standalone use if needed)
    if (!localStorage.getItem('dental_clinic_users')) {
        localStorage.setItem('dental_clinic_users', JSON.stringify([])); 
        // Initialize with default admin/odont/asist if this page could be loaded before index.html
        // For simplicity, we assume index.html's script.js runs first or this is fine.
        // A more robust solution would be a shared initialization module.
    }


    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const fullName = document.getElementById('fullName').value;
            const username = document.getElementById('regUsername').value;
            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!fullName || !username || !password || !confirmPassword) {
                displayMessage('Por favor, complete todos los campos.', 'error', registerMessage);
                return;
            }

            if (password !== confirmPassword) {
                displayMessage('Las contraseñas no coinciden.', 'error', registerMessage);
                return;
            }

            if (password.length < 6) {
                displayMessage('La contraseña debe tener al menos 6 caracteres.', 'error', registerMessage);
                return;
            }

            let users = JSON.parse(localStorage.getItem('dental_clinic_users')) || [];
            
            const existingUser = users.find(user => user.username === username);
            if (existingUser) {
                displayMessage('El nombre de usuario ya existe. Por favor, elija otro.', 'error', registerMessage);
                return;
            }

            const newUser = {
                username: username,
                password: password,
                role: 'Paciente', // New users from this form are always 'Paciente'
                fullName: fullName
            };

            users.push(newUser);
            localStorage.setItem('dental_clinic_users', JSON.stringify(users));

            displayMessage(`¡Cuenta de paciente creada exitosamente para ${username}! Ahora puede iniciar sesión.`, 'success', registerMessage);
            registerForm.reset();
            
            // Optionally redirect to login page after a short delay
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 3000);
        });
    }
});

function displayMessage(message, type, element) {
    if (!element) return;
    element.textContent = message;
    element.className = `message ${type}`; // Ensure 'message' base class is always present
    element.style.display = 'block';

    setTimeout(() => {
        if (element.className.includes(type)) {
            // element.style.display = 'none';
            // element.textContent = '';
        }
    }, 5000); // Message visible for 5 seconds. For registration success, it persists until redirect.
}

