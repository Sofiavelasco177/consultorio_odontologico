





<?php
include("Database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombrePaciente'];
    $fecha = $_POST['fecha'];
    $motivo = $_POST['motivoConsulta'];
    $observaciones = $_POST['observaciones'];
    $tratamiento = $_POST['tratamiento'];
    $profesional = $_POST['profesional'];

    $sql = "INSERT INTO observaciones (nombre_paciente, fecha, motivo_consulta, observaciones, tratamiento, profesional)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssss", $nombre, $fecha, $motivo, $observaciones, $tratamiento, $profesional);

    if ($stmt->execute()) {
        echo "<script>alert('Observación registrada con éxito'); window.location.href='formulario.html';</script>";
    } else {
        echo "Error: " . $conexion->error;
    }

    $stmt->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Odontologo</title>
    <link rel="stylesheet" href="style.css">
    <script type="importmap">
    {
      "imports": {
        "@fullcalendar/core": "https://cdn.skypack.dev/@fullcalendar/core@6.1.11",
        "@fullcalendar/daygrid": "https://cdn.skypack.dev/@fullcalendar/daygrid@6.1.11",
        "@fullcalendar/timegrid": "https://cdn.skypack.dev/@fullcalendar/timegrid@6.1.11",
        "@fullcalendar/list": "https://cdn.skypack.dev/@fullcalendar/list@6.1.11",
        "@fullcalendar/interaction": "https://cdn.skypack.dev/@fullcalendar/interaction@6.1.11",
        "chart.js": "https://cdn.skypack.dev/chart.js@4.4.2"
      }
    }
    </script>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="navbar-header">
                <h2>Dental Odontologo</h2>
            </div>
            <ul>
                
                <li><a href="#" data-section="patients"><img src="patients-icon.png" alt="" class="nav-icon"> Pacientes</a></li>
                <li><a href="#" data-section="appointments"><img src="appointments-icon.png" alt="" class="nav-icon"> Citas</a></li>
                <li><a href="#" data-section="full-calendar"><img src="calendar-icon.png" alt="" class="nav-icon"> Calendario Completo</a></li>
                <li><a href="#" data-section="observations"><img src="observations-icon.png" alt="" class="nav-icon">Formato</a></li>
                
            </ul>
        </nav>
        <main class="content">
           

            <!-- Patients Section -->
            <div id="patients-content" class="page-content">
                <header class="content-header">
                    <h1>Gestión de Pacientes</h1>
                </header>
                <div id="earningsSummary">
                    <!-- Earnings summary will be populated by JS -->
                </div>
                <div id="patientListTableContainer">
                    <!-- Patient table will be populated by JS -->
                </div>
                <div id="earningsChartContainer">
                    <canvas id="earningsChart"></canvas>
                </div>
                <p><em>Esta sección muestra los pacientes del mes actual y un resumen de ganancias.</em></p>
            </div>

            <!-- Calendar Wrapper Section (for Citas and Calendario Completo) -->
            <div id="calendar-wrapper" class="page-content">
                <header class="content-header">
                    <h1 id="calendar-view-title">Agenda de Citas</h1> <!-- Title will be updated by JS -->
                </header>
                <div id="calendar"></div>
            </div>

           

    <div id="eventModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2 id="modalTitle"></h2>
            <p><strong>Paciente:</strong> <span id="modalPatientName"></span></p>
            <p><strong>Motivo:</strong> <span id="modalReason"></span></p>
            <p><strong>Horario:</strong> <span id="modalTime"></span></p>
            <p><strong>Contacto:</strong> <span id="modalContact"></span></p>
            <p><strong>Notas:</strong> <span id="modalNotes"></span></p>
        </div>
    </div>

    <div class="container my-5">
    <div class="form-card mx-auto shadow">
      <div class="card-header bg-gradient-primary text-white rounded-top">
        <h4 class="mb-0 text-center"> Registro de Observaciones del Paciente</h4>
      </div>
      <div class="card-body">
        <form method="POST" action="guardar_observacion.php">
          <div class="mb-3">
            <label for="nombrePaciente" class="form-label">Nombre del Paciente</label>
            <input type="text" class="form-control" id="nombrePaciente" name="nombrePaciente" required>
          </div>

          <div class="mb-3">
            <label for="fecha" class="form-label">Fecha de Observación</label>
            <input type="date" class="form-control" id="fecha" name="fecha" required>
          </div>

          <div class="mb-3">
            <label for="motivoConsulta" class="form-label">Motivo de la Consulta</label>
            <textarea class="form-control" id="motivoConsulta" name="motivoConsulta" rows="2" required></textarea>
          </div>

          <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones del Profesional</label>
            <textarea class="form-control" id="observaciones" name="observaciones" rows="4" required></textarea>
          </div>

          <div class="mb-3">
            <label for="tratamiento" class="form-label">Tratamiento Recomendado</label>
            <textarea class="form-control" id="tratamiento" name="tratamiento" rows="3"></textarea>
          </div>

          <div class="mb-3">
            <label for="profesional" class="form-label">Profesional que Atiende</label>
            <input type="text" class="form-control" id="profesional" name="profesional" required>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Guardar Observación</button>
          </div>
        </form>
      </div>
    </div>
  </div>

    <script type="module" src="script.js"></script>
</body>
</html>



