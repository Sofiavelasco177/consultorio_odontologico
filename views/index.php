<?php
// Incluir archivos de configuración
require_once('../config/config.php');
require_once 'config/database.php';
require_once 'utils/Database.php';
require_once 'utils/Session.php';

// Iniciar sesión
Session::init();

// Cargar controladores y modelos automáticamente
spl_autoload_register(function($className) {
    // Controladores
    if (file_exists('controllers/' . $className . '.php')) {
        require_once 'controllers/' . $className . '.php';
    }
    // Modelos
    elseif (file_exists('models/' . $className . '.php')) {
        require_once 'models/' . $className . '.php';
    }
    // Helpers
    elseif (file_exists('helpers/' . $className . '.php')) {
        require_once 'helpers/' . $className . '.php';
    }
});

// Enrutador simple
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'Home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Formatear el nombre del controlador
$controller = ucfirst($controller) . 'Controller';

// Verificar si el controlador existe
if (file_exists('controllers/' . $controller . '.php')) {
    $controller = new $controller;
    
    // Verificar si el método existe
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        // Método no encontrado
        header('Location: ' . BASE_URL . 'home/error/404');
    }
} else {
    // Controlador no encontrado
    header('Location: ' . BASE_URL . 'home/error/404');
}
?>