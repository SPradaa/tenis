<?php
require_once("db/connection.php");
$db = new Database();
$con = $db->conectar();
session_start();

// Consulta para obtener los jugadores masculinos
$queryMasculino = "SELECT documento, nombre, edad, ranking FROM jugadores WHERE sexo = 1";
$stmtMasculino = $con->prepare($queryMasculino);
$stmtMasculino->execute();
$jugadoresMasculinos = $stmtMasculino->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener los jugadores femeninos
$queryFemenino = "SELECT documento, nombre, edad, ranking FROM jugadores WHERE sexo = 2";
$stmtFemenino = $con->prepare($queryFemenino);
$stmtFemenino->execute();
$jugadoresFemeninos = $stmtFemenino->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Jugadores</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #e9ecef;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 20px;
        }
        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #28a745;
        }
        th, td {
            text-align: center;
        }
        th {
            background-color: #28a745;
            color: white;
        }
        .table thead th {
            vertical-align: middle;
            text-align: center;
        }
        .btn-back {
            display: block;
            margin: 20px auto;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Participantes del Torneo de Tenis</h1>
        <button class="btn btn-primary btn-back" onclick="goBack()"><i class="fas fa-arrow-left"></i> Regresar</button>
        <div class="row">
            <div class="col-md-6 table-container">
                <h2><i class="fas fa-mars"></i> Torneo Masculino</h2>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Edad</th>
                            <th>Ranking</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($jugadoresMasculinos as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['documento']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['edad']); ?></td>
                                <td><?php echo htmlspecialchars($row['ranking']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 table-container">
                <h2><i class="fas fa-venus"></i> Torneo Femenino</h2>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Edad</th>
                            <th>Ranking</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($jugadoresFemeninos as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['documento']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['edad']); ?></td>
                                <td><?php echo htmlspecialchars($row['ranking']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>

