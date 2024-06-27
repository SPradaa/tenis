<?php

require_once ("../../db/connection.php");
// include("../../controller/validar.php");
$db = new Database();
$con = $db->conectar();
session_start();

$sql = $con->prepare("SELECT * FROM jugadores");
$sql->execute();
$fila = $sql->fetch();

$_SESSION['documento'] = $fila['documento'];
$nombre = $_SESSION['nombre'];
$_SESSION['edad'] = $fila['edad'];
$_SESSION['sexo'] = $fila['sexo'];
$_SESSION['ranking'] = $fila['ranking'];
$_SESSION['tipo'] = $fila['id_rol'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Bienvenido sr. <?php echo $nombre; ?></h1>
        
        <!-- Tarjeta para crear torneo -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Crear Torneo</h5>
                <p class="card-text">Haga clic en el botón para crear un nuevo torneo.</p>
                <a href="create.php" class="btn btn-primary">Crear Torneo</a>
            </div>
        </div>
    </div>

    <div class="card">
            <div class="card-body">
                <h5 class="card-title">Crear enfrentamientos</h5>
                <p class="card-text">Haga clic en el botón para crear los enfrentamientos del torneo.</p>
                <a href="prepartida.php" class="btn btn-primary">Crear VS</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
