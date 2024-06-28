<?php
require_once("../../db/connection.php");
session_start();

// Validar si la sesión está iniciada
if (!isset($_SESSION['documento'])) {
    header("Location: ../../login.html");
    exit();
}

$db = new Database();
$con = $db->conectar();

// Obtener el documento del usuario logueado
$documento = $_SESSION['documento'];

// Consulta para obtener el detalle del usuario logueado
$sql = $con->prepare("SELECT * FROM detalle WHERE documento = :documento");
$sql->bindParam(':documento', $documento, PDO::PARAM_INT);
$sql->execute();
$detalle = $sql->fetch();

if ($detalle) {
    $token = $detalle['token'];
    $_SESSION['token'] = $token;
    $fecha_usuario = $detalle['fecha'];
    $torneo_usuario = $detalle['torneo'];
    $resultado_usuario = $detalle['resultado'];

    // Consulta para obtener el rival con el mismo token
    $sql_rival = $con->prepare("SELECT * FROM detalle WHERE token = :token AND documento != :documento");
    $sql_rival->bindParam(':token', $token, PDO::PARAM_INT);
    $sql_rival->bindParam(':documento', $documento, PDO::PARAM_INT);
    $sql_rival->execute();
    $rival = $sql_rival->fetch();

    if ($rival) {
        $rival_documento = $rival['documento'];
        $rival_fecha = $rival['fecha'];
        $rival_torneo = $rival['torneo'];
        $rival_resultado = $rival['resultado'];

        // Consulta para obtener el nombre del rival
        $sql_nombre_rival = $con->prepare("SELECT nombre FROM jugadores WHERE documento = :documento");
        $sql_nombre_rival->bindParam(':documento', $rival_documento, PDO::PARAM_INT);
        $sql_nombre_rival->execute();
        $rival_info = $sql_nombre_rival->fetch();

        if ($rival_info) {
            $rival_nombre = $rival_info['nombre'];
        } else {
            $rival_nombre = 'Nombre no encontrado';
        }
    } else {
        $rival_nombre = 'No se encontró un rival con el mismo token.';
        $rival_fecha = $rival_torneo = $rival_resultado = '';
    }

    // Obtener información del partido más cercano
    $fecha = $con->prepare("SELECT * FROM partidos ORDER BY fecha LIMIT 1");
    $fecha->execute();
    $partido = $fecha->fetch();

    if ($partido) {
        $fecha_partido = $partido['fecha'];
        $torneo = htmlspecialchars($partido['torneo'], ENT_QUOTES, 'UTF-8');
    } else {
        $fecha_partido = null;
    }
} else {
    echo "No se encontró el detalle del usuario logueado.";
    exit();
}

// Lógica para manejar el botón "Comenzar"
if (isset($_POST['comenzar'])) {
    // Verificar si ya existe una sala con el token y el documento del usuario
    $sql_sala = $con->prepare("SELECT * FROM salas WHERE token = :token AND documento = :documento");
    $sql_sala->bindParam(':token', $token, PDO::PARAM_INT);
    $sql_sala->bindParam(':documento', $documento, PDO::PARAM_INT);
    $sql_sala->execute();
    $sala_existente = $sql_sala->fetch();

    if (!$sala_existente) {
        // Si no existe una sala con estos datos, creamos una nueva sala para el primer jugador
        $id_estado = 3; // Estado inicial para el primer jugador
        $sql_insert_sala = $con->prepare("INSERT INTO salas (documento, token, puntos, juegos, sets, id_estado, ganador) VALUES (:documento, :token, 0, 0, 0, :id_estado, NULL)");
        $sql_insert_sala->bindParam(':documento', $documento, PDO::PARAM_INT);
        $sql_insert_sala->bindParam(':token', $token, PDO::PARAM_INT);
        $sql_insert_sala->bindParam(':id_estado', $id_estado, PDO::PARAM_INT);
        $sql_insert_sala->execute();
    } else {
        // Actualizar id_estado para el segundo jugador que se registre en la misma sala
        $id_estado = 4; // Estado para el segundo jugador
        $sql_update_sala = $con->prepare("UPDATE salas SET id_estado = :id_estado WHERE token = :token");
        $sql_update_sala->bindParam(':id_estado', $id_estado, PDO::PARAM_INT);
        $sql_update_sala->bindParam(':token', $token, PDO::PARAM_INT);
        $sql_update_sala->execute();
    }

    // Verificar si todos los jugadores con el mismo token tienen el campo ganador en null
    $sql_verificar = $con->prepare("SELECT * FROM salas WHERE token = :token");
    $sql_verificar->bindParam(':token', $token, PDO::PARAM_INT);
    $sql_verificar->execute();
    $salas = $sql_verificar->fetchAll();

    $ganadores_null = true;
    foreach ($salas as $sala) {
        if (!is_null($sala['ganador'])) {
            $ganadores_null = false;
            break;
        }
    }

    if ($ganadores_null) {
        // Redirigir a la interfaz del juego
        header("Location: primerjuego.php");
        exit();
    } else {
        echo "<script>alert('El juego ya terminó');</script>";
    }
}

// Lógica para manejar el botón "Cerrar Sesión"
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../../login.html");
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interfaz del Jugador</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Bienvenido <?php echo htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8'); ?><?php echo $token ; ?></h1>

        <!-- Botón de cerrar sesión -->
        <form method="POST" action="">
            <button type="submit" name="logout" class="btn btn-danger mb-4">Cerrar Sesión</button>
        </form>

        <!-- Información de la partida más cercana -->
        <?php if ($fecha_partido): ?>
            <?php
            $fecha_actual = new DateTime();
            $fecha_partido_dt = new DateTime($fecha_partido);

            if ($fecha_actual < $fecha_partido_dt):
                $intervalo = $fecha_actual->diff($fecha_partido_dt);
                $dias = $intervalo->d;
                $horas = $intervalo->h;
                $minutos = $intervalo->i;
                $segundos = $intervalo->s;
            ?>
            <div class="alert alert-info">
                <h4>Faltan:</h4>
                <p id="countdown"><?php echo "$dias días, $horas horas, $minutos minutos y $segundos segundos"; ?></p>
            </div>
            <script>
                var countDownDate = new Date("<?php echo $fecha_partido_dt->format('Y-m-d H:i:s'); ?>").getTime();
                var x = setInterval(function() {
                    var now = new Date().getTime();
                    var distance = countDownDate - now;

                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    document.getElementById("countdown").innerHTML = days + " días, " + hours + " horas, "
                    + minutes + " minutos y " + seconds + " segundos ";

                    if (distance < 0) {
                        clearInterval(x);
                        document.getElementById("countdown").innerHTML = "El partido ha comenzado";
                        location.reload();
                    }
                }, 1000);
            </script>
            <?php else: ?>
            <div class="alert alert-success">
                <h4>Detalles del partido:</h4>
                <p><strong>Fecha:</strong> <?php echo $fecha_partido_dt->format('Y-m-d H:i:s'); ?></p>
                <p><strong>Torneo:</strong> <?php echo $torneo; ?></p>
                <form method="POST" action="">
                    <button type="submit" name="comenzar" class="btn btn-primary">Comenzar</button>
                </form>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                <h4>No hay partidos programados</h4>
            </div>
        <?php endif; ?>

        <!-- Información del usuario y rival -->
        <div class="alert alert-info mt-4">
            <h4>Información del Jugador y Rival:</h4>
            <div class="row">
                <div class="col-md-6">
                    <h5>Tu información:</h5>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <!-- <p><strong>Fecha:</strong> <?php echo htmlspecialchars($fecha_usuario, ENT_QUOTES, 'UTF-8'); ?></p> -->
                    <p><strong>Torneo:</strong> <?php echo htmlspecialchars($torneo_usuario, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Resultado:</strong> <?php echo htmlspecialchars($resultado_usuario, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="col-md-6">
                    <h5>Información del Rival:</h5>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($rival_nombre, ENT_QUOTES, 'UTF-8'); ?></p>
                    <!-- <p><strong>Fecha:</strong> <?php echo htmlspecialchars($rival_fecha, ENT_QUOTES, 'UTF-8'); ?></p> -->
                    <p><strong>Torneo:</strong> <?php echo htmlspecialchars($rival_torneo, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><strong>Resultado:</strong> <?php echo htmlspecialchars($rival_resultado, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


