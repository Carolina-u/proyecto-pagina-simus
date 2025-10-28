<?php
$servidor    = "localhost";
$usuario     = "root";
$clave       = "729004";
$baseDeDatos = "steam_simplificado";

// conexion_bd
$con = mysqli_connect($servidor, $usuario, $clave, $baseDeDatos);

//  envio del formulario 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtener y limpiar datos
    $cedula    = trim($_POST["cedula"] ?? ""); //agregar cedula otra vez
    $nombre     = trim($_POST["nombre"] ?? "");
    $apellido   = trim($_POST["apellido"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";
    $acep_term_cond = isset($_POST["acep_term_cond"]) ? 1 : 0;

    // validaciones q estban antes en el js
    if ($cedula ==="" || $nombre === "" || $apellido === "" || strlen($contrasena) < 8) {
        echo "<script>alert('Completa todos los campos correctamente (contraseña >= 8 caracteres).');</script>";
    } else {
          if (!preg_match('/^[0-9]+$/', $cedula)) {
            echo "<script>alert('La cédula solo puede contener números.');</script>";
        } else if (strlen($cedula) > 11) {
            echo "<script>alert('La cédula no puede tener más de 11 caracteres.');</script>";
        } else {
        // cedula existe 
        $verif = $con->prepare("SELECT cedula FROM usuarios WHERE cedula = ?");
        $verif->bind_param("s", $cedula);
        $verif->execute();
        $verif->store_result();
        if ($verif->num_rows > 0) {
            // cedula ya registrada
            $verif->close();
            echo "<script>alert('Esta cédula ya está registrada. Intenta otra vez.');</script>";
        }else {
            $verif->close();
            $contrasena = $_POST["contrasena"] ?? "";
            $confirmarContrasena = $_POST["confirmar_contrasena"] ?? "";
            if ($contrasena !== $confirmarContrasena) {
                echo "<script>alert('Las contraseñas no coinciden.');</script>";
            } else if (strlen($contrasena) < 8) {
                echo "<script>alert('La contraseña debe tener al menos 8 caracteres.');</script>";
            }
            // hash a la contrasena 
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            //mete valores a la bd
            $inserta = $con->prepare("INSERT INTO usuarios (cedula, nombre, apellido, contrasena, fecha_registro, acep_term_cond) VALUES (?, ?, ?, ?, NOW(), ?)");

            if ($inserta === false) {
                echo "<script>alert('Error al preparar la consulta.');</script>";
            } else {
                // corregir tipo en bind_param (típicamente: "ssssi")
                $inserta->bind_param("ssssi", $cedula, $nombre, $apellido, $hash, $acep_term_cond);

                if ($inserta->execute()) {

                    // éxito: redirigir al login
                    echo "<script>

                            window.location.href = 'login.php';
                          </script>";
                } else {
                    echo "Error al insertar: " . htmlspecialchars($inserta->error);
                }
                $inserta->close();
            }
        }
    }
  }
}


$con->close();
?>

<!DOCTYPE html> <!--esto es para demostrar que es el html 5 o su version mas moderna-->
<html lang="es"> <!--iindicar que esta en español y que muestre errores si no es-->
  <head>
    <meta charset="UTF-8"> <!--para soportar los caracteres especiales del español-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!--es para verse en telefonos-->
    <title>Registro SIMUS.MJN</title> <!--titulo depestaña-->
    <style>
      body {
        font-family: monospace, sans-serif; /*fuente de letra principal*/
        background: linear-gradient(to right, #5d49f8d2, #b8b6cf); /*este es pal fondo*/
        display: flex; /*centrar contenido mas facil*/
        justify-content: center; /*que todo vaya en el centro o alienado al centro en horizontal*/
        align-items:center; /*palabras titulos botones etc en el centro en vertical*/
        height: 100vh; /*altura de lo que se ve en pantalla*/
        margin: 0; /*elimjna los margenes del navegador si los tiene*/
      }
      .cajaderegistro {
        background: white; /*el color del recuadro blanco donde esta el coso de registro*/
        padding: 60px;  /*espacio dentro del recuadro por todos los lados para que haya espacio*/
        @media (max-width:480px){
          padding: 30px;
        }
        border-radius: 20px; /*los bordes del recuadrte blanco se redondean con esto*/
        box-shadow: 0 4px 8px rgba(0,0,0,0.637); /*sombra de color negro para que resalte el coso*/
        width:400px; /*ancho que va a tener dentro del recuadro*/
      }
      h2 { /*esto es para el titulo de crear cuenta, centrado y don colorcito*/
        text-align: center;
        color:#6d5fe0;
        margin-bottom: 40px;

      }

      /*lode etiquetas y campos*/
      label {
        display: block;
        margin: 16px 0 6px;
        font-weight: bold;
        color: #333;
      }
      input {
        width:100%;
        padding:10px;
        border-radius: 6px;
        border: 1px solid #ccc;;
      }
      /*mensajes de error*/
      .errores {
        font-size: 13px;
        color: red;
        margin-top:4px;
      }

      /*el coso de aceptacion de terminos*/
      .checkbox {
        display:flex;
        align-items: center;
        justify-content:flex-start;
        gap:6px;
        margin-top:12px;
      }
      .checkbox label{
        margin:0;
        font-size:14px;
        color: #333;
      }
      .checkbox a{
        color:#6d5fe0;
        text-decoration: none;
        font-weight: bold;
      }
      .checkbox a:hover{
        text-decoration: underline;;
      }

      /*el boton de registrarse*/
      button {
        margin-top: 15px;
        margin-bottom: 20px;
        width:100%;
        padding:15px;
        background: #8a7cf7;
        border:none;
        border-radius:8px;
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
        display:block;
      }

      button :hover {
        background:#6d5fe0;
      }

      /*link de inicio de sesion si uno ya tiene cuenta*/
      .linkdeinicio {
        text-align: center;
        margin-top:15px;
        font-size:7px;
      }
      .linkdeinicio a {
        color:#6d5fe0;
        text-decoration: none;
        font-weight: bold;
      }

    </style>
  </head>

<body>
  <div class="cajaderegistro">
    <h2>Crear Cuenta</h2>

  <form id="CamposRegistro" action="index.php" method="POST">
    <label for ="cedula">Cédula:</label><br> <!--agregar cedula otra vez-->
    <input type="text" id="cedula" name="cedula" required><br><br>
  <div id="errorCedula" class="errores"></div>

    <label for="nombre">Nombre:</label><br>
    <input type="text" id="nombre" name="nombre" required><br><br>
  <div id="errorNombre" class="errores"></div>

    <label for="apellido">Apellido:</label><br>
    <input type="text" id="apellido" name="apellido" required><br><br>
  <div id="errorApellido" class="errores"></div>

    <label for="contrasena">Contraseña:</label><br>
    <input type="password" id="contrasena" name="contrasena" minlength="8" required><br><br>
  <div id="errorContrasena" class="errores"></div>

  <label for="confirmar_contrasena">Confirmar contraseña:</label><br>
  <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" minlength="8" required><br><br>
  <div id="errorConfirmarContrasena" class="errores"></div>

    <label class="checkbox">
      <input type="checkbox" id="acept_term_cond" name="acep_term_cond" value="1" required>
      <label for="acep_term_cond">Acepto los <a href="terminos.html">términos y condiciones</a></label>
    </label>
    <div id="errorAceptoTerminos" class="errores"></div>
    
    <button type="submit">Registrarse</button>
    </form>

    <div class="linkdelogin">
        ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
    </div>
  </div>
</body>

<script>
  const formulario = document.getElementById('CamposRegistro');

  formulario.addEventListener("submit", function(evento) {
    evento.preventDefault();
    let esValido = true;

    //limpia los mensajes de error
    document.querySelectorAll(".errores").forEach(e => e.textContent = "");

    //optencion de valores,
    const cedula = document.getElementById("cedula").value.trim(); //agregar cedula otra vez
    const nombre = document.getElementById("nombre").value.trim();
    const apellido = document.getElementById("apellido").value.trim();
    const contrasena = document.getElementById("contrasena").value.trim();
    const confirmarContrasena = document.getElementById("confirmar_contrasena").value.trim();
    const aceptoTerminos = document.getElementById("acept_term_cond").checked;

    // Validaciones pa q se vean en pantalla de usuario ahi mismo
    if (cedula === "") { //agregar cedula otra vez
      document.getElementById("errorCedula").textContent = "Ingresa tu cédula.";
      esValido = false;
    }
    if (nombre === "") {
      document.getElementById("errorNombre").textContent = "Ingresa tu nombre.";
      esValido = false;
    }

    if (apellido === "") {
      document.getElementById("errorApellido").textContent = "Ingresa tu apellido.";
      esValido = false;
    }


    if (contrasena.length < 8) {
      document.getElementById("errorContrasena").textContent = "La contraseña debe tener como mínimo 8 caracteres.";
      esValido = false;
    }

    if (confirmarContrasena !== contrasena) {
      document.getElementById("errorConfirmarContrasena").textContent = "Las contraseñas no coinciden.";
      esValido = false;
    }

    if (!aceptoTerminos) {
      document.getElementById("errorAceptoTerminos").textContent = "Debes aceptar los términos y condiciones.";
      esValido = false;
    }

    // Enviar si es válido
    if (esValido) {
      formulario.submit();
    }
  });
</script>