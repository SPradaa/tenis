<?php
require_once(" ../../../db/connection.php");
$conexion = new Database();
$con = $conexion->conectar();
session_start();

// Verificar si se ha enviado el formulario
if(isset($_POST["inicio"])) {
    // Obtener el código del formulario
    $codigo = $_POST['codigo'];

    // Aquí puedes realizar cualquier validación adicional según tus necesidades
    // ...

    // Si la validación es exitosa, continuar con la verificación del código único de la empresa
    if($nit == $nitVerificacion) {
        // Obtener los datos del usuario directamente de la base de datos
        $consultaUsuario = $con->prepare("SELECT * FROM usuarios WHERE documento = :documento");
        $consultaUsuario->bindParam(':documento', $_SESSION['documento']);
        $consultaUsuario->execute();
        $usuario = $consultaUsuario->fetch(PDO::FETCH_ASSOC);

        // Almacenar los datos del usuario en variables
        $documento = $usuario['documento'];
        $nombre = $usuario['nombre'];
        $apellido = $usuario['apellido'];
        $correo = $usuario['correo'];
        $tipo =$usuario['id_rol'];
        $nit = $usuario['nit'];

        // Consultar datos de la empresa asociada al NIT
        $consultaEmpresa = $con->prepare("SELECT * FROM empresas WHERE nit = :nit");
        $consultaEmpresa->bindParam(':nit', $nit);
        $consultaEmpresa->execute();
        $empresa = $consultaEmpresa->fetch(PDO::FETCH_ASSOC);

        // Almacenar los datos de la empresa en variables
        $nitVerificacion = $empresa['nit'];
        $nombreEmpresa = $empresa['empresa'];
        $idLlave = $empresa['id_llave'];
        $codigoUnico = $empresa['codigo_unico'];

        // Validar el código ingresado con el código único de la empresa
        if ($codigo == $codigoUnico) {
            // Redirigir según el tipo de usuario
            if($tipo == 1) {
                header("Location: ../model/admins/desarrollador/index.php");
                exit();
            } elseif($tipo == 2 ) {
                header("Location: ../model/admins/administrador/index.php");
                exit();
            } elseif($tipo == 3) {
                header("Location: ../medico/indexmed.php");
                exit();
            } elseif($tipo == 4) {
                header("Location: ../model/admins/farmaceuta/index.php");
                exit();
            }
        } else {
            // Acción cuando la validación inicial falla
            // Destruir la sesión y redirigir al login
            header("Location: ../error.html");
            session_destroy();
            
            exit();
        }
    } else {
        // Si no se ha enviado el formulario, destruir la sesión y redireccionar al login
        header("Location: ../error.html");
        session_destroy();
        
        exit();
    }
}
?>
