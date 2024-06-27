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
$token = $_SESSION['token']; // Accede al token desde la sesión

// Consulta para verificar si ya existe un registro en la tabla salas con el mismo token y documento
$sql_salas = $con->prepare("SELECT * FROM salas WHERE token = :token AND documento = :documento");
$sql_salas->bindParam(':token', $token, PDO::PARAM_INT);
$sql_salas->bindParam(':documento', $documento, PDO::PARAM_INT);
$sql_salas->execute();
$sala = $sql_salas->fetch();

if (!$sala) {
    // Si no existe, insertar un nuevo registro
    $sql_insert = $con->prepare("INSERT INTO salas (documento, token, puntos, juegos, sets, id_estado, ganador, fallos) VALUES (:documento, :token, 0, 0, 0, 1, NULL, 0)");
    $sql_insert->bindParam(':documento', $documento, PDO::PARAM_INT);
    $sql_insert->bindParam(':token', $token, PDO::PARAM_INT);
    $sql_insert->execute();
}

// Contar cuántos usuarios hay en la tabla salas con el mismo token
$sql_count = $con->prepare("SELECT COUNT(*) as count FROM salas WHERE token = :token");
$sql_count->bindParam(':token', $token, PDO::PARAM_INT);
$sql_count->execute();
$count_result = $sql_count->fetch();
$user_count = $count_result['count'];

// Obtener los datos de la tabla salas filtrados por token
$sql_all_salas = $con->prepare("SELECT * FROM salas WHERE token = :token");
$sql_all_salas->bindParam(':token', $token, PDO::PARAM_INT);
$sql_all_salas->execute();
$all_salas = $sql_all_salas->fetchAll(PDO::FETCH_ASSOC);

// Estado actual de la sala
$current_id_estado = $sala['id_estado'] ?? 1; // Valor predeterminado 1 si no se ha definido

// Actualizar el estado si se hace clic en Jugar
if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $numero_aleatorio = rand(1, 2);

    if ($accion == 'jugar' && $current_id_estado == 3) {
        if ($numero_aleatorio == 1) {
            // El jugador ha respondido con éxito
            $mensaje = "Has respondido con éxito, '$numero_aleatorio'";
            
            // Verificar si el jugador ha ganado el juego
            $sql_update_puntos = $con->prepare("UPDATE salas SET puntos = puntos + 1 WHERE token = :token AND documento = :documento");
            $sql_update_puntos->bindParam(':token', $token, PDO::PARAM_INT);
            $sql_update_puntos->bindParam(':documento', $documento, PDO::PARAM_INT);
            $sql_update_puntos->execute();

            // Verificar si el jugador ha ganado el juego
            $sql_check_puntos = $con->prepare("SELECT puntos FROM salas WHERE token = :token AND documento = :documento");
            $sql_check_puntos->bindParam(':token', $token, PDO::PARAM_INT);
            $sql_check_puntos->bindParam(':documento', $documento, PDO::PARAM_INT);
            $sql_check_puntos->execute();
            $puntos_result = $sql_check_puntos->fetch();
            $puntos = $puntos_result['puntos'];

            if ($puntos >= 3) {
                $sql_update_juegos = $con->prepare("UPDATE salas SET puntos = 0, juegos = juegos + 1 WHERE token = :token AND documento = :documento");
                $sql_update_juegos->bindParam(':token', $token, PDO::PARAM_INT);
                $sql_update_juegos->bindParam(':documento', $documento, PDO::PARAM_INT);
                $sql_update_juegos->execute();
            }

            // Verificar si el jugador ha ganado el set
            $sql_check_juegos = $con->prepare("SELECT juegos FROM salas WHERE token = :token AND documento = :documento");
            $sql_check_juegos->bindParam(':token', $token, PDO::PARAM_INT);
            $sql_check_juegos->bindParam(':documento', $documento, PDO::PARAM_INT);
            $sql_check_juegos->execute();
            $juegos_result = $sql_check_juegos->fetch();
            $juegos = $juegos_result['juegos'];

            if ($juegos >= 3) {
                $sql_update_sets = $con->prepare("UPDATE salas SET juegos = 0, sets = sets + 1 WHERE token = :token AND documento = :documento");
                $sql_update_sets->bindParam(':token', $token, PDO::PARAM_INT);
                $sql_update_sets->bindParam(':documento', $documento, PDO::PARAM_INT);
                $sql_update_sets->execute();
            }

            // Verificar si el jugador ha ganado el partido
            $sql_check_sets = $con->prepare("SELECT sets FROM salas WHERE token = :token AND documento = :documento");
            $sql_check_sets->bindParam(':token', $token, PDO::PARAM_INT);
            $sql_check_sets->bindParam(':documento', $documento, PDO::PARAM_INT);
            $sql_check_sets->execute();
            $sets_result = $sql_check_sets->fetch();
            $sets = $sets_result['sets'];

            // Verificar si el jugador ha ganado el partido
            if ($sets >= 3) {
                // Obtener el documento del jugador perdedor
                $sql_get_perdedor = $con->prepare("SELECT documento FROM salas WHERE token = :token AND documento != :documento");
                $sql_get_perdedor->bindParam(':token', $token, PDO::PARAM_INT);
                $sql_get_perdedor->bindParam(':documento', $documento, PDO::PARAM_INT);
                $sql_get_perdedor->execute();
                $perdedor_result = $sql_get_perdedor->fetch();
                
                if ($perdedor_result) {
                    $documento_perdedor = $perdedor_result['documento'];

                    // Actualizar el ganador en la tabla salas
                    $sql_update_ganador = $con->prepare("UPDATE salas SET ganador = :documento WHERE token = :token");
                    $sql_update_ganador->bindParam(':token', $token, PDO::PARAM_INT);
                    $sql_update_ganador->bindParam(':documento', $documento, PDO::PARAM_INT);
                    $sql_update_ganador->execute();

                    // Eliminar al usuario perdedor de la tabla salas
                    $sql_eliminar_perdedor = $con->prepare("DELETE FROM salas WHERE token = :token AND documento = :documento");
                    $sql_eliminar_perdedor->bindParam(':token', $token, PDO::PARAM_INT);
                    $sql_eliminar_perdedor->bindParam(':documento', $documento_perdedor, PDO::PARAM_INT);
                    $sql_eliminar_perdedor->execute();

                    // Redirigir a juego.php
                    header("Location: juego.php");
                    exit();
                }
            }

            // Actualizar el estado de 3 a 4
            $sql_update_estado = $con->prepare("UPDATE salas SET id_estado = 4 WHERE token = :token AND documento = :documento");
            $sql_update_estado->bindParam(':token', $token, PDO::PARAM_INT);
            $sql_update_estado->bindParam(':documento', $documento, PDO::PARAM_INT);
            $sql_update_estado->execute();

            // Luego, actualizar el estado del otro jugador de 4 a 3
            $sql_update_other_estado = $con->prepare("UPDATE salas SET id_estado = 3 WHERE token = :token AND documento != :documento AND id_estado = 4");
            $sql_update_other_estado->bindParam(':token', $token, PDO::PARAM_INT);
            $sql_update_other_estado->bindParam(':documento', $documento, PDO::PARAM_INT);
            $sql_update_other_estado->execute();

        } else {
            // El jugador ha fallado
            $mensaje = "Has fallado";
            
            // Incrementar el contador de fallos
            $sql_update_fallos = $con->prepare("UPDATE salas SET fallos = fallos + 1 WHERE token = :token AND documento = :documento");
            $sql_update_fallos->bindParam(':token', $token, PDO::PARAM_INT);
            $sql_update_fallos->bindParam(':documento', $documento, PDO::PARAM_INT);
            $sql_update_fallos->execute();

            // Verificar el número de fallos
            $sql_check_fallos = $con->prepare("SELECT fallos FROM salas WHERE token = :token AND documento = :documento");
            $sql_check_fallos->bindParam(':token', $token, PDO::PARAM_INT);
            $sql_check_fallos->bindParam(':documento', $documento, PDO::PARAM_INT);
            $sql_check_fallos->execute();
            $fallos_result = $sql_check_fallos->fetch();
            $fallos = $fallos_result['fallos'];

            if ($fallos >= 2) {
                // Reiniciar fallos y dar punto al oponente
                $sql_reset_fallos = $con->prepare("UPDATE salas SET fallos = 0 WHERE token = :token AND documento = :documento");
                $sql_reset_fallos->bindParam(':token', $token, PDO::PARAM_INT);
                $sql_reset_fallos->bindParam(':documento', $documento, PDO::PARAM_INT);
                $sql_reset_fallos->execute();

                $sql_update_opponent_puntos = $con->prepare("UPDATE salas SET puntos = puntos + 1 WHERE token = :token AND documento != :documento");
                $sql_update_opponent_puntos->bindParam(':token', $token, PDO::PARAM_INT);
                $sql_update_opponent_puntos->bindParam(':documento', $documento, PDO::PARAM_INT);
                $sql_update_opponent_puntos->execute();
            }
        }

        // Enviar respuesta en formato JSON
        echo json_encode(['mensaje' => $mensaje]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego en Progreso</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Juego en Progreso</h1>
        <?php if ($user_count == 1): ?>
            <div class="alert alert-info">
                <h4>Esperando a un oponente...</h4>
                <p id="countdown">30 segundos restantes</p>
            </div>
            <script>
                var countDownDate = new Date().getTime() + 30000; // 30 segundos a partir de ahora
                var x = setInterval(function() {
                    var now = new Date().getTime();
                    var distance = countDownDate - now;

                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    document.getElementById("countdown").innerHTML = seconds + " segundos restantes";

                    if (distance < 0) {
                        clearInterval(x);
                        document.getElementById("countdown").innerHTML = "El tiempo ha expirado";

                        // Realizar una solicitud AJAX para actualizar la tabla salas
                        var xhr = new XMLHttpRequest();
                        xhr.open("POST", "update_winner.php", true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                // Redirigir a la página de ganador o mostrar mensaje
                                alert("¡Ganaste por falta de oponente!");
                                window.location.href = "juego.php"; // Cambia esto a la página que desees
                            }
                        };
                        xhr.send("token=<?php echo $token; ?>&documento=<?php echo $documento; ?>");
                    }
                }, 1000);
            </script>
        <?php else: ?>
            <div class="alert alert-success">
                <h4>Juego en progreso</h4>
                <p>Ambos jugadores están listos.</p>
            </div>
        <?php endif; ?>

        <h2 class="mt-4">Detalles de la Sala</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Sala</th>
                    <th>Documento</th>
                    <th>Token</th>
                    <th>Puntos</th>
                    <th>Juegos</th>
                    <th>Sets</th>
                    <th>ID Estado</th>
                    <th>Ganador</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_salas as $sala): ?>
                    <tr>
                        <td><?php echo $sala['id_sala']; ?></td>
                        <td><?php echo $sala['documento']; ?></td>
                        <td><?php echo $sala['token']; ?></td>
                        <td><?php echo $sala['puntos']; ?></td>
                        <td><?php echo $sala['juegos']; ?></td>
                        <td><?php echo $sala['sets']; ?></td>
                        <td><?php echo $sala['id_estado']; ?></td>
                        <td><?php echo $sala['ganador']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($current_id_estado == 3): ?>
            <button id="playButton" class="btn btn-primary">Jugar</button>
        <?php else: ?>
            <button type="button" class="btn btn-primary" disabled>Jugar</button>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#playButton').click(function() {
            $.ajax({
                type: 'POST',
                url: '',
                data: { accion: 'jugar' },
                dataType: 'json', // Especificamos que esperamos una respuesta JSON
                success: function(response) {
                    alert(response.mensaje);
                    location.reload(); // Recargar la página para actualizar los datos
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Has fallado: ' );
                }
            });
        });
    });
</script>

</body>
</html>
