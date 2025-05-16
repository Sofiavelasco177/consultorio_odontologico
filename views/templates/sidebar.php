<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'home') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-home me-2"></i>
                    Inicio
                </a>
            </li>
            
            <?php if(Session::isAdmin() || Session::isAsistente()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'citas') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>citas">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Citas
                </a>
            </li>
            <?php endif; ?>
            
            <?php if(Session::isOdontologo()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'odontologos/agenda') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>odontologos/agenda">
                    <i class="fas fa-calendar-check me-2"></i>
                    Mi Agenda
                </a>
            </li>
            <?php endif; ?>
            
            <?php if(Session::isPaciente()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'pacientes/miscitas') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>pacientes/miscitas">
                    <i class="fas fa-calendar-check me-2"></i>
                    Mis Citas
                </a>
            </li>
            <?php endif; ?>
            
            <?php if(Session::isAdmin() || Session::isAsistente()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'pacientes') !== false && strpos($_SERVER['REQUEST_URI'], 'miscitas') === false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>pacientes">
                    <i class="fas fa-users me-2"></i>
                    Pacientes
                </a>
            </li>
            <?php endif; ?>
            
            <?php if(Session::isAdmin()): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'odontologos') !== false && strpos($_SERVER['REQUEST_URI'], 'agenda') === false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>odontologos">
                    <i class="fas fa-user-md me-2"></i>
                    Odontólogos
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'consultorios') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>consultorios">
                    <i class="fas fa-door-open me-2"></i>
                    Consultorios
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'tratamientos') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>tratamientos">
                    <i class="fas fa-procedures me-2"></i>
                    Tratamientos
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'users') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>users">
                    <i class="fas fa-user-cog me-2"></i>
                    Usuarios
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'reportes') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>reportes">
                    <i class="fas fa-chart-line me-2"></i>
                    Reportes
                </a>
            </li>
            <?php 
                endif;
             ?>
        </ul>
    </div>
</nav>