<?php
class Session {
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return false;
    }

    public static function delete($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        session_destroy();
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function checkLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public static function isAdmin() {
        return (self::isLoggedIn() && $_SESSION['user_role'] == 1);
    }

    public static function isOdontologo() {
        return (self::isLoggedIn() && $_SESSION['user_role'] == 2);
    }

    public static function isAsistente() {
        return (self::isLoggedIn() && $_SESSION['user_role'] == 3);
    }

    public static function isPaciente() {
        return (self::isLoggedIn() && $_SESSION['user_role'] == 4);
    }

    public static function checkRole($roles = []) {
        if (!self::isLoggedIn() || !in_array($_SESSION['user_role'], $roles)) {
            $_SESSION['message'] = 'No tiene permisos para acceder a esta sección';
            $_SESSION['message_type'] = 'danger';
            header('Location: ' . BASE_URL);
            exit;
        }
    }
}