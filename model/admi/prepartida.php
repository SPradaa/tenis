<?php
require_once("../../db/connection.php");
$db = new Database();
$con = $db->conectar();
session_start();

// Función para obtener una lista de jugadores de un género específico
function obtenerJugadoresPorGenero($con, $sexo) {
    $sql = "SELECT * FROM jugadores WHERE id_rol = 2 AND sexo = :sexo";
    $stmt = $con->prepare($sql);
    $stmt->execute(['sexo' => $sexo]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener hombres y mujeres
$hombres = obtenerJugadoresPorGenero($con, 1);
$mujeres = obtenerJugadoresPorGenero($con, 2);

// Asignar tokens y guardar enfrentamientos
function asignarTokensYGuardar($jugadores, $con, $torneo) {
    shuffle($jugadores); // Mezclar los jugadores para obtener enfrentamientos aleatorios
    
    // Crear enfrentamientos de pares
    $enfrentamientos = [];
    for ($i = 0; $i < count($jugadores) - 1; $i += 2) {
        $jugador1 = $jugadores[$i];
        $jugador2 = $jugadores[$i + 1];
        
        $token = sprintf("%04d", rand(0, 9999)); // Generar un nuevo token para este enfrentamiento
        $jugador1['token'] = $token;
        $jugador2['token'] = $token;

        // Guardar enfrentamiento en la tabla
        $stmt = $con->prepare("INSERT INTO detalle (token, documento, torneo, resultado) VALUES (?, ?, ?, ''), (?, ?, ?, '')");
        $stmt->execute([$token, $jugador1['documento'], $torneo, $token, $jugador2['documento'], $torneo]);

        $enfrentamientos[] = [$jugador1, $jugador2];
    }
    return $enfrentamientos;
}

// Obtener el ID del torneo seleccionado
$torneo = isset($_POST['torneo']) ? $_POST['torneo'] : null;

// Generar enfrentamientos y asignar tokens
$enfrentamientos_hombres = asignarTokensYGuardar($hombres, $con, $torneo);
$enfrentamientos_mujeres = asignarTokensYGuardar($mujeres, $con, $torneo);

// Función para mostrar una tabla de enfrentamientos
function mostrarTablaEnfrentamientos($enfrentamientos, $titulo) {
    echo "<div class='col-md-6'>";
    echo "<div class='card border-primary mb-3'>";
    echo "<div class='card-header bg-primary text-white'>$titulo</div>";
    echo "<div class='card-body'>";
    echo "<table class='table table-bordered'>";
    echo "<thead class='thead-dark'>";
    echo "<tr><th>Nombre</th><th>Token</th><th>vs</th><th>Nombre</th><th>Token</th></tr>"; // Agregar columnas para los tokens
    echo "</thead>";
    echo "<tbody>";
    foreach ($enfrentamientos as $enfrentamiento) {
        echo "<tr>";
        echo "<td>{$enfrentamiento[0]['nombre']}</td>";
        echo "<td>{$enfrentamiento[0]['token']}</td>";
        echo "<td>vs</td>";
        echo "<td>{$enfrentamiento[1]['nombre']}</td>";
        echo "<td>{$enfrentamiento[1]['token']}</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

// Verificar si el botón debe estar habilitado o deshabilitado
$habilitarBoton = count($enfrentamientos_hombres) % 2 == 0 && count($enfrentamientos_mujeres) % 2 == 0;
$claseBoton = $habilitarBoton ? 'btn-success' : 'btn-danger';
$estadoBoton = $habilitarBoton ? '' : 'disabled';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enfrentamientos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <?php mostrarTablaEnfrentamientos($enfrentamientos_hombres, "Enfrentamientos entre Hombres"); ?>
        <?php mostrarTablaEnfrentamientos($enfrentamientos_mujeres, "Enfrentamientos entre Mujeres"); ?>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form method="POST" action="">
                <div class="form-group" >
                    <label for="torneo">Seleccionar Torneo:</label>
                    <select name="torneo" class="form-control" id="torneo" required>
                        <?php
                        $control = $con->prepare("SELECT * FROM partidos where id_estado = 1");
                        $control->execute();
                        while ($fila = $control->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value=" . $fila['id_partido'] . ">"
                            . $fila['torneo'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn <?php echo $claseBoton; ?>" <?php echo $estadoBoton; ?>>Registrar Enfrentamientos</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php
$con = null; // Cerrar la conexión PDO
?>
