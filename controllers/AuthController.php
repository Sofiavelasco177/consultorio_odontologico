<?php
class AuthController {
    private $authModel;
    private $rolModel;
    
    public function __construct() {
        $this->authModel = new Auth();
        $this->rolModel = new Rol();
    }
    
    public function login() {
        // Verifica si ya está logueado
        if(Session::isLoggedIn()) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Procesa formulario
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitizar datos POST
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Datos de formulario
            $data = [
                'username' => trim($_POST['username']),
                'password' => trim($_POST['password']),
                'username_err' => '',
                'password_err' => '',
            ];
            
            // Valida nombre de usuario
            if(empty($data['username'])) {
                $data['username_err'] = 'Por favor ingrese su nombre de usuario';
            }
            
            // Valida contraseña
            if(empty($data['password'])) {
                $data['password_err'] = 'Por favor ingrese su contraseña';
            }
            
            // Verifica si no hay errores
            if(empty($data['username_err']) && empty($data['password_err'])) {
                // Intentar login
                $loggedInUser = $this->authModel->login($data['username'], $data['password']);
                
                if($loggedInUser) {
                    // Crear sesión
                    $this->createUserSession($loggedInUser);
                } else {
                    $data['password_err'] = 'Contraseña o nombre de usuario incorrecto';
                    
                    // Cargar vista con errores
                    require_once 'views/auth/login.php';
                }
            } else {
                // Cargar vista con errores
                require_once 'views/auth/login.php';
            }
        } else {
            // Inicializar datos
            $data = [
                'username' => '',
                'password' => '',
                'username_err' => '',
                'password_err' => '',
            ];
            
            // Cargar vista
            require_once 'views/auth/login.php';
        }
    }
    
    public function register() {
        // Verificar si ya está logueado
        if(Session::isLoggedIn()) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Procesar formulario
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitizar datos POST
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Datos de formulario
            $data = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'role_id' => 4, // Rol de paciente por defecto
                'username_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];
            
            // Validar nombre de usuario
            if(empty($data['username'])) {
                $data['username_err'] = 'Por favor ingrese un nombre de usuario';
            } elseif($this->authModel->checkUsernameExists($data['username'])) {
                $data['username_err'] = 'Nombre de usuario ya registrado';
            }
            
            // Validar email
            if(empty($data['email'])) {
                $data['email_err'] = 'Por favor ingrese un correo electrónico';
            } elseif($this->authModel->checkEmailExists($data['email'])) {
                $data['email_err'] = 'Correo electrónico ya registrado';
            }
            
            // Validar contraseña
            if(empty($data['password'])) {
                $data['password_err'] = 'Por favor ingrese una contraseña';
            } elseif(strlen($data['password']) < 6) {
                $data['password_err'] = 'La contraseña debe tener al menos 6 caracteres';
            }
            
            // Validar confirmación de contraseña
            if(empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'Por favor confirme la contraseña';
            } elseif($data['password'] != $data['confirm_password']) {
                $data['confirm_password_err'] = 'Las contraseñas no coinciden';
            }
            
            // Verificar si no hay errores
            if(empty($data['username_err']) && empty($data['email_err']) && 
               empty($data['password_err']) && empty($data['confirm_password_err'])) {
                
                // Registrar usuario
                $userId = $this->authModel->createUser($data);
                
                if($userId) {
                    // Registrado con éxito
                    Session::set('message', 'Registro exitoso. Puede iniciar sesión');
                    Session::set('message_type', 'success');
                    header('Location: ' . BASE_URL . 'auth/login');
                    exit;
                } else {
                    die('Algo salió mal');
                }
            } else {
                // Cargar vista con errores
                require_once 'views/auth/register.php';
            }
        } else {
            // Inicializar datos
            $data = [
                'username' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'role_id' => 4,
                'username_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];
            
            // Cargar vista
            require_once 'views/auth/register.php';
        }
    }
    
    public function createUserSession($user) {
        Session::set('user_id', $user->id_usuario);
        Session::set('user_name', $user->nombre_usuario);
        Session::set('user_email', $user->correo);
        Session::set('user_role', $user->id_rol);
        Session::set('user_role_name', $user->nombre_rol);
        
        // Redireccionar según rol
        if(Session::isAdmin()) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
        } elseif(Session::isOdontologo()) {
            header('Location: ' . BASE_URL . 'odontologos/agenda');
        } elseif(Session::isAsistente()) {
            header('Location: ' . BASE_URL . 'citas');
        } else {
            header('Location: ' . BASE_URL);
        }
        exit;
    }
    
    public function logout() {
        Session::destroy();
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }
}
?>
