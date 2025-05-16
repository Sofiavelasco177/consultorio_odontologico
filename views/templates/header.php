<?php

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Consultorio Odontológico');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php 
    // Mostrar la barra de navegación solo si el usuario está logueado
    if(Session::isLoggedIn()): 
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-tooth me-2"></i>
                <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>"><i class="fas fa-home me-1"></i> Inicio</a>
                    </li>
                    
                    <?php if(Session::isAdmin() || Session::isAsistente()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>pacientes"><i class="fas fa-users me-1"></i> Pacientes</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if(Session::isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>odontologos"><i class="fas fa-user-md me-1"></i> Odontólogos</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if(Session::isAdmin() || Session::isAsistente()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>consultorios"><i class="fas fa-clinic-medical me-1"></i> Consultorios</a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>citas"><i class="fas fa-calendar-alt me-1"></i> Citas</a>
                    </li>
                    
                    <?php if(Session::isAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cogs me-1"></i> Administración
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>users"><i class="fas fa-user me-1"></i> Usuarios</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>tratamientos"><i class="fas fa-briefcase-medical me-1"></i> Tratamientos</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>especialidades"><i class="fas fa-stethoscope me-1"></i> Especialidades</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>reportes"><i class="fas fa-chart-bar me-1"></i> Reportes</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo Session::get('user_name'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>users/profile"><i class="fas fa-id-card me-1"></i> Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/logout"><i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="container py-4">

<!-- El contenido de la página irá aquí -->


        
