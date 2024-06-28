<?php
require_once ("../../db/connection.php");
$db = new Database();
$con = $db->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_partido = rand(10000, 99999);
    $id_partido = rand(10000, 99999); 
    $torneo = $_POST['torneo'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $estado = 1;


    $sql = $con->prepare("INSERT INTO partidos (id_partido, torneo, fecha, hora, id_estado) VALUES (:id_partido, :torneo, :fecha, :hora, :estado)");
    $sql->bindParam(':id_partido', $id_partido);
    $sql->bindParam(':torneo', $torneo);
    $sql->bindParam(':fecha', $fecha);
    $sql->bindParam(':hora', $hora);
    $sql->bindParam(':estado', $estado);
    
    if ($sql->execute()) {
        echo "Nuevo partido creado con éxito";
        echo '<script>window.location="indexadm.php"</script>';
    } else {
        echo "Error al crear el partido";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Partido</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Crear Partido</h1>
        
        <form method="POST" action="create.php">
            <div class="form-group">
                <label for="torneo">Torneo</label>
                <input type="text" class="form-control" id="torneo" name="torneo" required>
            </div>
            <div class="form-group">
                <label for="fecha">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
            </div>
            <div class="form-group">
                <label for="fecha">Hora</label>
                <input type="time" class="form-control" id="fecha" name="hora" required>
            </div>
            <button type="submit" class="btn btn-primary">Crear Partido</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
