<?php
require_once("../../db/connection.php");
$db = new Database();
$con = $db->conectar();
session_start();

// Validar la sesión
if (!isset($_SESSION['documento'])) {
    header("Location: login.php"); // Redirigir a la página de inicio de sesión si no está logueado
    exit;
}

// Obtener el jugador logueado
$documento = $_SESSION['documento'];
$sql_jugador = $con->prepare("SELECT * FROM jugadores WHERE documento = :documento");
$sql_jugador->bindParam(':documento', $documento);
$sql_jugador->execute();
$jugador_logueado = $sql_jugador->fetch();

// Obtener los enfrentamientos en los que participa el jugador logueado
$sql_enfrentamientos = $con->prepare("SELECT e.*, j1.nombre as nombre1, j2.nombre as nombre2 
                                      FROM enfrentamientos e 
                                      JOIN jugadores j1 ON e.id_jugador1 = j1.documento 
                                      JOIN jugadores j2 ON e.id_jugador2 = j2.documento 
                                      WHERE e.id_jugador1 = :documento1 OR e.id_jugador2 = :documento2");
$sql_enfrentamientos->bindParam(':documento1', $documento);
$sql_enfrentamientos->bindParam(':documento2', $documento);
$sql_enfrentamientos->execute();
$enfrentamientos_jugador = $sql_enfrentamientos->fetchAll();

// Suponiendo que estamos trabajando con el primer enfrentamiento para simplificar
if (!empty($enfrentamientos_jugador)) {
    $enfrentamiento = $enfrentamientos_jugador[0];
    $nombre_jugador1 = $enfrentamiento['nombre1'];
    $nombre_jugador2 = $enfrentamiento['nombre2'];
} else {
    $nombre_jugador1 = "Jugador 1";
    $nombre_jugador2 = "Jugador 2";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego de Tenis</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cancha-tenis {
            position: relative;
            width: 100%;
            max-width: 800px;
            height: 400px;
            margin: 0 auto;
            background-color: #6ab150;
            border: 2px solid #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        .linea-central, .linea-lateral, .linea-servicio, .linea-fondo, .linea-dobles {
            position: absolute;
            background-color: #fff;
        }
        .linea-central {
            top: 0;
            left: 50%;
            width: 2px;
            height: 100%;
            transform: translateX(-50%);
        }
        .linea-lateral {
            top: 0;
            width: 2px;
            height: 100%;
        }
        .linea-lateral.left {
            left: 0;
        }
        .linea-lateral.right {
            right: 0;
        }
        .linea-servicio {
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            transform: translateY(-50%);
        }
        .linea-fondo {
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
        }
        .linea-dobles {
            top: 25%;
            left: 0;
            width: 100%;
            height: 2px;
        }
        .jugador {
            position: absolute;
            width: 100px;
            text-align: center;
            color: #fff;
        }
        .jugador1 {
            top: 25%;
            left: 20px;
        }
        .jugador2 {
            top: 25%;
            right: 20px;
        }
        .table-custom {
            width: auto;
            margin: 0 auto;
        }
    </style>
</head>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego de Puntuación</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="cancha-tenis mb-4">
            <!-- Líneas de la cancha -->
            <div class="linea-central"></div>
            <div class="linea-lateral left"></div>
            <div class="linea-lateral right"></div>
            <div class="linea-servicio"></div>
            <div class="linea-fondo"></div>
            <div class="linea-dobles"></div>
            <!-- Jugador 1 -->
            <div class="jugador jugador1">
                <div class="jugador-icono jugador-azul" style="background-image: url('icono1.png');"></div>
                <?php echo htmlspecialchars($nombre_jugador1); ?>
            </div>
            <!-- Jugador 2 -->
            <div class="jugador jugador2">
                <div class="jugador-icono jugador-verde" style="background-image: url('icono2.png');"></div>
                <?php echo htmlspecialchars($nombre_jugador2); ?>
            </div>
        </div>

        <table class="table table-bordered table-custom">
            <thead>
                <tr>
                    <th></th>
                    <th>Jugador 1</th>
                    <th>Jugador 2</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Puntos</th>
                    <td id="puntosJugador1">0</td>
                    <td id="puntosJugador2">0</td>
                </tr>
                <tr>
                    <th>Juegos</th>
                    <td id="juegosJugador1">0</td>
                    <td id="juegosJugador2">0</td>
                </tr>
                <tr>
                    <th>Sets</th>
                    <td id="setsJugador1">0</td>
                    <td id="setsJugador2">0</td>
                </tr>
                <tr>
                    <th>Turno</th>
                    <td colspan="2" id="turnoActual">Jugador 1</td>
                </tr>
            </tbody>
        </table>

        <button class="btn btn-primary" id="btnGirarRuleta" onclick="girarRuleta()">Girar Ruleta</button>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- JavaScript para el juego -->
    <script>
  let puntosJugador1 = 0;
let puntosJugador2 = 0;
let juegosJugador1 = 0;
let juegosJugador2 = 0;
let setsJugador1 = 0;
let setsJugador2 = 0;
let turno = Math.random() < 0.5 ? 1 : 2;
let timer;
const nombreJugador1 = '<?php echo htmlspecialchars($nombre_jugador1); ?>';
const nombreJugador2 = '<?php echo htmlspecialchars($nombre_jugador2); ?>';

function actualizarEstado() {
    document.getElementById('puntosJugador1').innerText = puntosJugador1;
    document.getElementById('puntosJugador2').innerText = puntosJugador2;
    document.getElementById('juegosJugador1').innerText = juegosJugador1;
    document.getElementById('juegosJugador2').innerText = juegosJugador2;
    document.getElementById('setsJugador1').innerText = setsJugador1;
    document.getElementById('setsJugador2').innerText = setsJugador2;
    document.getElementById('turnoActual').innerText = turno === 1 ? nombreJugador1 : nombreJugador2;
    document.getElementById('btnGirarRuleta').disabled = turno !== 1;
}

function girarRuleta() {
    clearTimeout(timer); // Clear the existing timer
    const resultado = Math.random() < 0.5 ? 'Responder' : 'Fallar';
    if (resultado === 'Responder') {
        alert('Has regresado correctamente la pelota');
    } else {
        alert('Has fallado');
        if (turno === 1) {
            puntosJugador2 += 15;
            if (puntosJugador2 >= 30) {
                juegosJugador2++;
                puntosJugador1 = 0;
                puntosJugador2 = 0;
                if (juegosJugador2 >= 2) {
                    setsJugador2++;
                    juegosJugador1 = 0;
                    juegosJugador2 = 0;
                    if (setsJugador2 >= 3) {
                        alert(`${nombreJugador2} gana el partido!`);
                        resetearJuego();
                        return;
                    }
                }
            }
        } else {
            puntosJugador1 += 15;
            if (puntosJugador1 >= 30) {
                juegosJugador1++;
                puntosJugador1 = 0;
                puntosJugador2 = 0;
                if (juegosJugador1 >= 2) {
                    setsJugador1++;
                    juegosJugador1 = 0;
                    juegosJugador2 = 0;
                    if (setsJugador1 >= 3) {
                        alert(`${nombreJugador1} gana el partido!`);
                        resetearJuego();
                        return;
                    }
                }
            }
        }
        turno = turno === 1 ? 2 : 1;
    }
    actualizarEstado();
    iniciarTimer();
}

function resetearJuego() {
    puntosJugador1 = 0;
    puntosJugador2 = 0;
    juegosJugador1 = 0;
    juegosJugador2 = 0;
    setsJugador1 = 0;
    setsJugador2 = 0;
    turno = Math.random() < 0.5 ? 1 : 2;
    actualizarEstado();
    iniciarTimer();
}

function iniciarTimer() {
    timer = setTimeout(() => {
        girarRuleta();
    }, 5000); // 5 segundos
}

function actualizarUIPeriodicamente() {
    setInterval(() => {
        actualizarEstado();
    }, 1000); // Actualiza cada segundo
}

actualizarEstado();
iniciarTimer();
actualizarUIPeriodicamente();

    </script>
</body>
</html>
