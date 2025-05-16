<?php
session_start();


if (isset($_SESSION['nombre']))
{
    header('Location: ../../controlador/validar.php');
}

if (isset($_POST['btningresar'])) 
{
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "hotel";

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
        header("Location: ../../controlador/validar.php");
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
    <title>Crear Cuenta de Paciente - Clínica Dental</title>
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
                <h2>Crear tu Cuenta </h2>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" required placeholder="Ej: 123456789">
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Su nombre">
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required placeholder="Su apellido">
                </div>

                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                </div>

                <div class="form-group">
                    <label for="genero">Género:</label>
                    <select id="genero" name="genero" required>
                        <option value="" disabled selected>Seleccione...</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" required placeholder="Su dirección completa">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" required placeholder="Ej: 0991234567">
                </div>

                <div class="form-group">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" required placeholder="suemail@ejemplo.com">
                </div>
            </div>
                <div class="form-group">
                    <label for="regPassword">Contraseña:</label>
                    <input type="password" id="regPassword" name="regPassword" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirmar Contraseña:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="btn">Registrar</button>
            </form>
            <p class="login-link">¿Ya tienes una cuenta? <a href="index.html">Iniciar sesión</a></p>
            <div id="registerMessage" class="message"></div>
        </div>
    </div>
    <script src="registro.js" type="module"></script>
</body>
</html>