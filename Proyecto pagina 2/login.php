<?php
// ======================
// CONFIGURACIÓN GENERAL
// ======================
$servidor    = "localhost";
$usuario     = "root";
$clave       = "729004";
$baseDeDatos = "steam_simplificado";

// Conexión MySQL
$con = new mysqli($servidor, $usuario, $clave, $baseDeDatos);
if ($con->connect_error) {
    die("Error en la conexión con la base de datos: " . $con->connect_error);
}

// ======================
// PROCESO DE LOGIN
// ======================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = trim($_POST['cedula']);
    $contrasena = $_POST['contrasena'];

    if (empty($cedula) || empty($contrasena)) {
        echo json_encode(['success' => false, 'error' => 'Completa todos los campos']);
        exit;
    }

    // Consulta SQL
    $sql = "SELECT cedula, nombre, contrasena FROM usuarios WHERE cedula = ?";
    $consulta = $con->prepare($sql);

    if ($consulta) {
        $consulta->bind_param("s", $cedula);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            // Como en tu BD las contraseñas están guardadas sin hash (si las metiste manualmente)
            // puedes usar esto temporalmente hasta que uses password_hash()
            if ($contrasena === $usuario['contrasena'] || password_verify($contrasena, $usuario['contrasena'])) {
                $token = base64_encode($usuario['cedula'] . ":" . $usuario['nombre']);
                echo json_encode([
                    'success' => true,
                    'token' => $token,
                    'nombre' => $usuario['nombre'],
                    'cedula' => $usuario['cedula']
                ]);
                exit;
            } else {
                echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No existe ningún usuario con esa cédula']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta']);
        exit;
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang ="es">
  <head>
    <meta charset="UTF-8">
    <meta name ="viewport" content ="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <style>
      body{
        font-family: monospace, sans-serif;
        margin:0;
        padding:0;
        background: url(imgs/fondojuego.png);
        color:#333;
        position:relative;
        min-height:100vh;
      }
      .encabezadodepagina{
        background:white;
        padding:1rem 2rem;
        box-shadow:0 2px 5px rgba(0,0,0,0.1);
        display:flex;
        justify-content:space-between;
        align-items:center;
        position:relative;
        top:0;
        left:0;
        z-index:3;
      }
      .logo{font-size:24px;font-weight:bold;color:#6d5fe0;}
      .nav a{margin:0 15px;text-decoration:none;color:#333;font-weight:500;}
      .nav a:hover{color:#6d5fe0;}

      .cajadelogin{
        display:flex;
        justify-content:center;
        align-items:center;
        height:100vh;
        background:linear-gradient(135deg, #6d5fe0, #8a7cf7);
      }
      .cajitadeingreso{
        background:white;
        padding:40px;
        border-radius:12px;
        box-shadow:0 4px 15px rgba(0,0,0,0.2);
        width:350px;
        text-align:center;
      }
      .cajitadeingreso h2{margin-bottom:20px;color:#6d5fe0;}
      .cajitadeingreso input{
        width:100%;
        padding:12px;
        margin:10px 0;
        border:1px solid #ccc;
        border-radius:8px;
        font-size:16px;
      }
      .cajitadeingreso button{
        width:100%;
        padding:12px;
        background:#6d5fe0;
        color:white;
        border:none;
        border-radius:8px;
        font-size:16px;
        cursor:pointer;
        transition:background 0.3s ease;
      }
      .cajitadeingreso button:hover{background:#5c4fd1;}
      .cajitadeingreso a{display:block;margin-top:15px;font-size:14px;color:#6d5fe0;text-decoration:none;}
      .mensajeerror{color:#d93025;background:#fce8e6;padding:10px;border-radius:4px;border:1px solid #f28b82;margin:10px 0;font-size:14px;display:none;}
      .inputerror{border:2px solid #d93025 !important;}
    </style>
  </head>
  <body>
    <div class="encabezadodepagina">
      <div class="logo">SIMUS.MJN</div>
      <div class="nav">
        <a href="nosotros.html">Nosotros</a>
        <a href="index.php">Registro</a>
      </div>
    </div>

    <div class="cajadelogin">
      <div class="cajitadeingreso">
        <h2>Iniciar sesión</h2>
        <div id="mensajeerror" class="mensajeerror"></div>
        <form id="formulariologin" method="POST">
          <input type="text" name="cedula" placeholder="Cédula" required>
          <input type="password" name="contrasena" placeholder="Contraseña" required>
          <button type="submit">Entrar</button>
          <a href="index.php">¿No tienes cuenta? Regístrate</a>
        </form>
      </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded',function(){
  const mensajeerror=document.getElementById('mensajeerror');

  document.getElementById('formulariologin').addEventListener('submit',function(e){
    e.preventDefault();
    const cedula=document.querySelector('input[name="cedula"]').value.trim();
    const contrasena=document.querySelector('input[name="contrasena"]').value;
    mensajeerror.style.display='none';

    if(cedula===''||contrasena===''){
      mensajeerror.textContent='Por favor completa todos los campos.';
      mensajeerror.style.display='block';
      return;
    }

    const formdata=new FormData();
    formdata.append('cedula',cedula);
    formdata.append('contrasena',contrasena);

    fetch('',{method:'POST',body:formdata})
    .then(res=>res.json())
    .then(data=>{
      if(data.success){
        localStorage.setItem('token_sesion',data.token);
        localStorage.setItem('nombre',data.nombre);
        window.location.href='pprin.php';
      }else{
        mensajeerror.textContent=data.error;
        mensajeerror.style.display='block';
      }
    })
    .catch(err=>{
      mensajeerror.textContent='Error en el sistema.';
      mensajeerror.style.display='block';
      console.error(err);
    });
  });
});
</script>
  </body>
</html>
