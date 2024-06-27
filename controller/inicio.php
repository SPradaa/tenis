<?php
require_once("../db/connection.php"); 
session_start();

if (isset($_POST["inicio"])) {
    $documento = $_POST['documento'];
    $password = $_POST['password'];

    // Conectar a la base de datos
    $conexion = new Database();
    $con = $conexion->conectar();

    // Preparar la consulta SQL
    $sql = $con->prepare("SELECT * FROM jugadores WHERE documento = :documento");
    $sql->bindParam(':documento', $documento);
    $sql->execute();

    // Verificar si se encontró el usuario
    if ($fila = $sql->fetch(PDO::FETCH_ASSOC)) {
        // Verificar la contraseña
        if (password_verify($password, $fila['pass'])) {
            // Iniciar sesión
            $_SESSION['documento'] = $fila['documento'];
            $_SESSION['nombre'] = $fila['nombre'];
            $_SESSION['edad'] = $fila['edad'];
            $_SESSION['sexo'] = $fila['sexo'];
            $_SESSION['ranking'] = $fila['ranking'];
            $_SESSION['password'] = $fila['pass'];
            $_SESSION['tipo'] = $fila['id_rol'];

            // Redireccionar según el tipo de usuario
            if ($_SESSION['tipo'] == 1) {
                header("Location: ../model/admi/indexadm.php");
                exit();
            } else if ($_SESSION['tipo'] == 2) {
                header("Location: ../model/user/juego.php");
                exit();
            }
        } else {
            // Contraseña incorrecta
            header("location: ../error.php?error=invalid_password");
            exit();
        }
    } else {
        // Usuario no encontrado
        header("location: ../error.php?error=user_not_found");
        exit();
    }
} else {
    header("location: ../error.php?error=invalid_access");
    exit();
}
?>
