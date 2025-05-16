<?php 

class Session {
    public static function init() {
        session_start();
    }

    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function isLoggedIn() {
        return isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;
    }
}


if(Session::isLoggedIn()) {
    header('location: ' . BASE_URL . 'dashboard');
    exit(); 
}
?>



<footer class="bg-light py-4 mt-auto">
    <div class="container text-center">
        <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>