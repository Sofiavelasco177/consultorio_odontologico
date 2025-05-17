<?php
session_start();


if (isset($_SESSION['nombre']))
{
    header('Location: ../../controllers/AuthController.php');
}

if (isset($_POST['btningresar'])) 
{
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "consultorio_odontologico";

    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

    if (!$conn) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    $usuario = $_POST['txtusuario'];
    $password = $_POST['txtpassword'];

    
    $query = mysqli_query($conn, "SELECT * FROM rol WHERE nombre = '".$nombre."' and contraseña = '".$password."'");
    $nr = mysqli_num_rows($query);

    if (!isset($_SESSION['nombre']))
    {
    if ($nr == 1) {
        $_SESSION['nombre'] = $usuario;
        header("Location: ../../controllers/AuthController.php");
        exit();
    } else if ($nr == 0)
    {
        echo "<script>alert('Usuario o contraseña incorrectos'); window.location='login.php';</script>";
    }
}
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Clínica Dental</title>
    <link rel="stylesheet" href="style.css">
    <script type="importmap">
    {
      "imports": {}
    }
    </script>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <div class="logo-container">
                    <img src="image.png" alt="logo" class="logo">
                </div>
                <h2>BIENVENIDO A DENTAL</h2>
            </div>
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Rol:</label>
                    <select id="role" name="role">
                        <option value="Administrador">Administrador</option>
                        <option value="Odontologo">Odontólogo</option>
                        <option value="Asistente">Asistente</option>
                        <option value="Paciente">Paciente</option>
                    </select>
                </div>
                <button type="submit" class="btn">Ingresar</button>
            </form>
            <p class="register-link">¿Eres un paciente nuevo? <a href="registro.html">Crear cuenta</a></p>
            <div id="loginMessage" class="message"></div>
        </div>
    </div>
    <script src="script.js" type="module"></script>
</body>
</html>