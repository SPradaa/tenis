<?php
require_once("../../db/connection.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !isset($_POST['documento'])) {
        echo "Datos incompletos.";
        exit();
    }

    $token = $_POST['token'];
    $documento = $_POST['documento'];

    $db = new Database();
    $con = $db->conectar();

    // Actualizar la tabla salas con el ganador
    $sql_update = $con->prepare("UPDATE salas SET ganador = :documento WHERE token = :token");
    $sql_update->bindParam(':documento', $documento, PDO::PARAM_INT);
    $sql_update->bindParam(':token', $token, PDO::PARAM_INT);
    $sql_update->execute();

    echo "Ganador actualizado.";
}
?>
