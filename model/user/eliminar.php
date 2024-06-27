<?php
require_once("../../conexion/connection.php");
$db = new Database();
$con = $db->conectar();
session_start();

// Obtener los torneos disponibles
$sql_torneos = $con->prepare("SELECT * FROM torneos");
$sql_torneos->execute();
$torneos = $sql_torneos->fetchAll();

// Obtener los jugadores masculinos con id_rol igual a 2
$sql_masculinos = $con->prepare("SELECT * FROM jugadores WHERE id_rol = 2 AND sexo = 1");
$sql_masculinos->execute();
$jugadores_masculinos = $sql_masculinos->fetchAll();

// Obtener los jugadores femeninos con id_rol igual a 2
$sql_femeninos = $con->prepare("SELECT * FROM jugadores WHERE id_rol = 2 AND sexo = 2");
$sql_femeninos->execute();
$jugadores_femeninos = $sql_femeninos->fetchAll();

// Función para obtener enfrentamientos aleatorios sin repetición de jugadores
function obtenerEnfrentamientos($jugadores) {
    $enfrentamientos = array();
    $total_jugadores = count($jugadores);

    // Revolver los jugadores aleatoriamente
    shuffle($jugadores);

    // Crear enfrentamientos 1 vs 1
    for ($i = 0; $i < $total_jugadores - 1; $i += 2) {
        $enfrentamientos[] = array($jugadores[$i], $jugadores[$i + 1]);
    }

    return $enfrentamientos;
}

// Obtener enfrentamientos aleatorios para jugadores masculinos y femeninos
$enfrentamientos_masculinos = obtenerEnfrentamientos($jugadores_masculinos);
$enfrentamientos_femeninos = obtenerEnfrentamientos($jugadores_femeninos);

// Guardar enfrentamientos en la base de datos si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enfrentamientos = json_decode($_POST['enfrentamientos'], true);
    $torneo = $_POST['torneo'];  // Obtener el nombre del torneo seleccionado

    foreach ($enfrentamientos as $enfrentamiento) {
        $id_jugador1 = $enfrentamiento[0]['documento'];
        $id_jugador2 = $enfrentamiento[1]['documento'];
        $fecha = date('Y-m-d H:i:s'); // Puedes cambiar la fecha a la que desees
        $resultado = ''; // Inicialmente vacío

        $sql = $con->prepare("INSERT INTO enfrentamientos (id_jugador1, id_jugador2, fecha, torneo, resultado) VALUES (:id_jugador1, :id_jugador2, :fecha, :torneo, :resultado)");
        $sql->bindParam(':id_jugador1', $id_jugador1);
        $sql->bindParam(':id_jugador2', $id_jugador2);
        $sql->bindParam(':fecha', $fecha);
        $sql->bindParam(':torneo', $torneo);
        $sql->bindParam(':resultado', $resultado);
        $sql->execute();
    }

    // Redirigir a primerjuego.php después de guardar los enfrentamientos
    header("Location: primerjuego.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enfrentamientos Aleatorios</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos CSS personalizados -->
    <style>
        .jugador-verde {
            background-color: #b3ffb3; /* Cambia el color de fondo a verde */
        }

        .jugador-azul {
            background-color: #b3d9ff; /* Cambia el color de fondo a azul */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Enfrentamientos Aleatorios</h1>

        <?php if (isset($mensaje)) { ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php } ?>

        <form id="enfrentamientosForm" action="" method="post">
            <div class="form-group">
                <label for="torneo">Selecciona el Torneo:</label>
                <select class="form-control" id="torneo" name="torneo" required>
                    <option value="">Selecciona un torneo</option>
                    <?php foreach ($torneos as $torneo): ?>
                        <option value="<?php echo htmlspecialchars($torneo['nombre']); ?>">
                            <?php echo htmlspecialchars($torneo['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h2>Jugadores Masculinos</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Edad</th>
                        <th>Ranking</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enfrentamientos_masculinos as $enfrentamiento): ?>
                        <tr>
                            <td class="jugador-azul"><?php echo htmlspecialchars($enfrentamiento[0]['documento']); ?></td>
                            <td class="jugador-azul"><?php echo htmlspecialchars($enfrentamiento[0]['nombre']); ?></td>
                            <td class="jugador-azul"><?php echo htmlspecialchars($enfrentamiento[0]['edad']); ?></td>
                            <td class="jugador-azul"><?php echo htmlspecialchars($enfrentamiento[0]['ranking']); ?></td>
                            <td>VS</td>
                            <td class="jugador-verde"><?php echo htmlspecialchars($enfrentamiento[1]['documento']); ?></td>
                            <td class="jugador-verde"><?php echo htmlspecialchars($enfrentamiento[1]['nombre']); ?></td>
                            <td class="jugador-verde"><?php echo htmlspecialchars($enfrentamiento[1]['edad']); ?></td>
                            <td class="jugador-verde"><?php echo htmlspecialchars($enfrentamiento[1]['ranking']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Jugadores Femeninos</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th>Edad</th>
                        <th>Ranking</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enfrentamientos_femeninos as $enfrentamiento): ?>
                        <tr>
                            <td class="jugador-azul"><?php echo htmlspecialchars($enfrentamiento[0]['documento']); ?></td>
                            <td class="jugador-azul"><?php echo htmlspecialchars($enfrentamiento[0]['nombre']); ?></td>
                            <td class="jugador-azul"><?php echo htmlspecialchars($enfrentamiento[0]['edad']); ?></td>
                            <td class="jugador-azul"><?php echo htmlspecialchars($enfrentamiento[0]['ranking']); ?></td>
                            <td>VS</td>
                            <td class="jugador-verde"><?php echo htmlspecialchars($enfrentamiento[1]['documento']); ?></td>
                            <td class="jugador-verde"><?php echo htmlspecialchars($enfrentamiento[1]['nombre']); ?></td>
                            <td class="jugador-verde"><?php echo htmlspecialchars($enfrentamiento[1]['edad']); ?></td>
                            <td class="jugador-verde"><?php echo htmlspecialchars($enfrentamiento[1]['ranking']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <input type="hidden" name="enfrentamientos" id="enfrentamientosInput" value='<?php echo json_encode(array_merge($enfrentamientos_masculinos, $enfrentamientos_femeninos)); ?>'>
            <button type="submit" id="comenzarBtn" class="btn btn-primary" disabled>Comenzar</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var comenzarBtn = document.getElementById("comenzarBtn");
            var enfrentamientos = <?php echo json_encode(array_merge($enfrentamientos_masculinos, $enfrentamientos_femeninos)); ?>;
            
            function checkEnfrentamientos() {
                var allPaired = enfrentamientos.every(function(enfrentamiento) {
                    return enfrentamiento.length === 2;
                });

                var torneoSelected = document.getElementById("torneo").value !== "";

                comenzarBtn.disabled = !(allPaired && torneoSelected);
            }

            document.getElementById("torneo").addEventListener("change", checkEnfrentamientos);
            checkEnfrentamientos();
        });
    </script>
</body>
</html>
