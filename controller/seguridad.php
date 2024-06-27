<?php
session_start();

function validarSesion() {
    if (isset($_POST['cerrar_sesion'])) {

        session_unset();
        session_destroy();
        exit();
    }

    if (!isset($_SESSION['documento'])) {
        header("Location: ../../../login.html");
        exit();
    }

  
}
?>
