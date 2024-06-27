<?php
require_once("db/connection.php"); 
$db = new Database();
$con = $db->conectar();
session_start();

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formreg")) {
    $documento = $_POST['documento'];
    $nombre = $_POST['nombre'];
    $sexo = $_POST['sexo'];
    $edad = $_POST['edad'];
    $ranking = 0;
    $password = $_POST['password'];
    $rol = 2;

    // Comprobar si el documento ya existe
    $sql = $con->prepare("SELECT * FROM jugadores WHERE documento = :documento");
    $sql->bindParam(':documento', $documento, PDO::PARAM_STR);
    $sql->execute();
    $fila = $sql->fetchAll(PDO::FETCH_ASSOC);

    if ($fila) {
        echo '<script>alert("EL DOCUMENTO YA EXISTE //CAMBIELO//");</script>';
        echo '<script>window.location="registro.php"</script>';
    } else {
        // Comprobar si existen datos vacíos
        if ($documento == "" || $nombre == "" || $sexo == "") {
            echo '<script>alert("EXISTEN DATOS VACIOS");</script>';
            echo '<script>window.location="registro.php"</script>';
        } else {
            // Comprobar el número de registros actuales por sexo
            $sqlHombres = $con->prepare("SELECT COUNT(*) as total_hombres FROM jugadores WHERE sexo = 1");
            $sqlHombres->execute();
            $totalHombres = $sqlHombres->fetch(PDO::FETCH_ASSOC)['total_hombres'];

            $sqlMujeres = $con->prepare("SELECT COUNT(*) as total_mujeres FROM jugadores WHERE sexo = 2");
            $sqlMujeres->execute();
            $totalMujeres = $sqlMujeres->fetch(PDO::FETCH_ASSOC)['total_mujeres'];

            // Verificar si se puede registrar un nuevo jugador según el sexo
            if (($sexo == 1 && $totalHombres >= 12) || ($sexo == 2 && $totalMujeres >= 12)) {
                echo '<script>alert("Lo sentimos, las inscripciones se han cerrado");</script>';
                echo '<script>window.location="registro.php"</script>';
            } else {
                // Registrar el nuevo jugador
                $pass_cifrado = password_hash($password, PASSWORD_DEFAULT);
                $insertSQL = $con->prepare("INSERT INTO jugadores (documento, nombre, edad, sexo, ranking, pass, id_rol) VALUES (:documento, :nombre, :edad, :sexo, :ranking, :pass, :rol)");
                $insertSQL->bindParam(':documento', $documento, PDO::PARAM_STR);
                $insertSQL->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $insertSQL->bindParam(':edad', $edad, PDO::PARAM_INT);
                $insertSQL->bindParam(':sexo', $sexo, PDO::PARAM_INT);
                $insertSQL->bindParam(':ranking', $ranking, PDO::PARAM_INT);
                $insertSQL->bindParam(':pass', $pass_cifrado, PDO::PARAM_STR);
                $insertSQL->bindParam(':rol', $rol, PDO::PARAM_INT);

                $insertSQL->execute();
                
                echo '<script>alert("REGISTRO EXITOSO");</script>';
                echo '<script>window.location="index.php"</script>';
            }
        }
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <title>registrarme</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/templatemo.css">
    <link rel="stylesheet" href="assets/css/custom.css">

    <!-- Load fonts style after rendering the layout styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">


    <style>
        @keyframes borderRotate {
            0% {
                border-color: red;
            }
            25% {
                border-color: yellow;
            }
            50% {
                border-color: green;
            }
            75% {
                border-color: blue;
            }
            100% {
                border-color: red;
            }
        }
        .border-animated {
            border-width: 4px;
            border-style: solid;
            animation: borderRotate 5s linear infinite;
        }
        body {
            background: url('https://st2.depositphotos.com/1704023/7492/i/950/depositphotos_74922425-stock-photo-whole-tennis-court-from-the.jpg') no-repeat center center fixed;
            background-size: cover;
        }
    </style>



</head>
  


<body class="bg-gray-100 flex items-center justify-center h-screen relative overflow-hidden">
    <div class="background-animated"></div>
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md relative border-animated">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Formulario de Registro</h2>
        <form method="post" name="form1" id="form1"  autocomplete="off"> 
            <div class="mb-4">
                <label for="documento" class="block text-gray-700">Documento</label>
                <input type="text" id="documento" name="documento" pattern="[0-9]{8,11}" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="nombre" class="block text-gray-700">Nombre</label>
                <input type="text" id="nombre" name="nombre" pattern="[A-Za-z\s]+" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="edad" class="block text-gray-700">Edad</label>
                <input type="number" id="edad" name="edad" min="1" max="120" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="sexo" class="block text-gray-700">Sexo</label>
                <select name="sexo">
                <option value ="">Selecione su genero </option>
                
                <?php
                    $control = $con -> prepare ("SELECT * from generos");
                    $control -> execute();
                while ($fila = $control->fetch(PDO::FETCH_ASSOC)) 
                {
                    echo "<option value=" . $fila['sexo'] . ">"
                     . $fila['genero'] . "</option>";
                } 
                ?>
            </select>
            </div>

            <div class="row">
                <label for="">contraseña</label>
             <input type="password" name="password" id="password" pattern="[0-9A-Za-z]{4,15}" placeholder="Ingrese la Contraseña" title="La contraseña puede tener numeros o letras minimo 4 caracteres">
            </div>
            <br>
          
           
            <!-- <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 focus:outline-none">Registrar</button> -->

            <input type="submit" name="validar" value="Registrarme"  class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 focus:outline-none">
            <input type="hidden" name="MM_insert" value="formreg">
        </form>
    </div>



    <!-- Start Script -->
    <script src="assets/js/jquery-1.11.0.min.js"></script>
    <script src="assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/templatemo.js"></script>
    <script src="assets/js/custom.js"></script>
    <!-- End Script -->
</body>

</html>