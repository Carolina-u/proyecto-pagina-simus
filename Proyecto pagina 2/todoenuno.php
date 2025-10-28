<?php
// ===============================
// CONFIGURACIÓN DE CONEXIÓN
// ===============================
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conexion = new mysqli("localhost", "root", "729004", "steam_simplificado");
$conexion->set_charset("utf8mb4");

session_start();


// ===============================
// VERIFICAR USUARIO EXISTE
if (!isset($_SESSION['cedula'])) {
    header("Location: login.php");
    exit;
}

$cedula= $_SESSION['cedula'];
$nombre= $_SESSION['nombre'];
// ===============================
// SUBIR JUEGO
// ===============================
if (isset($_POST['subir_juego'])) {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $link = $_POST['link_descarga'];
    $categoria = intval($_POST['categoria']); // Asegurar entero
    $cedula = $_SESSION['cedula'];

    $imagen = "";
    if (!empty($_FILES['imagen']['name'])) {
        $carpeta = "imgs/";
        if (!file_exists($carpeta)) mkdir($carpeta, 0777, true);
        $nombreImg = time() . "_" . basename($_FILES["imagen"]["name"]);
        $ruta = $carpeta . $nombreImg;
        if (!move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta)) {
            die("Error al subir la imagen.");
        }
        $imagen = $ruta;
    }

    $stmt = $conexion->prepare("INSERT INTO juegos (titulo, descripcion, link_descarga, imagen, id_categoria, cedula_usuario) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $titulo, $descripcion, $link, $imagen, $categoria, $cedula);
    if (!$stmt->execute()) die("Error al subir juego: " . $stmt->error);

    header("Location: todoenuno.php");
    exit;
}

// ===============================
// ELIMINAR JUEGO (solo del usuario actual)
// ===============================
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $cedula = $_SESSION['cedula'];

    $stmt = $conexion->prepare("DELETE FROM juegos WHERE id_juego = ? AND cedula_usuario = ?");
    $stmt->bind_param("is", $id, $cedula);
    if (!$stmt->execute()) die("Error al eliminar juego: " . $stmt->error);

    header("Location: todoenuno.php");
    exit;
}

// ===============================
// AGREGAR COMENTARIO
// ===============================
if (isset($_POST['agregar_comentario'])) {
    $comentario = $_POST['comentario'];
    $id_juego = intval($_POST['id_juego']);
    $cedula = $_SESSION['cedula'];

    // Verificar juego existe
    $resJuego = $conexion->prepare("SELECT id_juego FROM juegos WHERE id_juego = ?");
    $resJuego->bind_param("i", $id_juego);
    $resJuego->execute();
    if ($resJuego->get_result()->num_rows == 0) die("El juego no existe.");

    $stmt = $conexion->prepare("INSERT INTO comentarios (id_juego, cedula_usuario, comentario) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id_juego, $cedula, $comentario);
    if (!$stmt->execute()) die("Error al agregar comentario: " . $stmt->error);

    header("Location: todoenuno.php");
    exit;
}

// ===============================
// FILTRO DE CATEGORÍA
// ===============================
$filtro = "";
if (isset($_GET['categoria']) && $_GET['categoria'] != "todas") {
    $filtro = "WHERE j.id_categoria=" . intval($_GET['categoria']);
}

// ===============================
// CONSULTAS DE JUEGOS Y CATEGORÍAS
// ===============================
$categorias = $conexion->query("SELECT * FROM categorias");
$juegos = $conexion->query("
    SELECT j.*, u.nombre, u.apellido, c.nombre_categoria
    FROM juegos j
    JOIN usuarios u ON j.cedula_usuario = u.cedula
    LEFT JOIN categorias c ON j.id_categoria = c.id_categoria
    $filtro
    ORDER BY j.fecha_publicacion DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tienda de Juegos</title>
<style>
/* ===================== Estilos ===================== */
body { font-family: 'Poppins', sans-serif; background-color: #f8f6ff; color: #333; margin: 0; padding: 0; }
header { background-color: #6b2fdc; color: white; padding: 20px; text-align: center; font-size: 24px; }
.container { width: 90%; margin: 30px auto; }
.formulario, .juego { background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
input, textarea, select, button { width: 100%; margin-top: 10px; padding: 10px; border-radius: 8px; border: 1px solid #ccc; font-size: 14px; }
button { background-color: #6b2fdc; color: white; cursor: pointer; border: none; font-weight: bold; }
button:hover { background-color: #8b48ff; }
.juegos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
.juego img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; }
.comentarios { background: #f1edff; border-radius: 8px; padding: 10px; margin-top: 10px; }
.comentario { border-bottom: 1px solid #ddd; padding: 5px 0; }
.descargar { display: inline-block; padding: 8px 12px; background: #6b2fdc; color: white; text-decoration: none; border-radius: 6px; margin-top: 8px; }
.eliminar { background: crimson; color: white; padding: 5px 10px; border-radius: 6px; text-decoration: none; float: right; }
</style>
</head>
<body>

<header>Tienda de Juegos - Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></header>
<div class="container">

<!-- Subir juego -->
<div class="formulario">
<h2>Subir nuevo juego</h2>
<form method="POST" enctype="multipart/form-data">
<input type="text" name="titulo" placeholder="Título del juego" required>
<textarea name="descripcion" placeholder="Descripción" required></textarea>
<input type="text" name="link_descarga" placeholder="Link de descarga">
<label>Imagen:</label>
<input type="file" name="imagen" accept="image/*">
<label>Categoría:</label>
<select name="categoria" required>
<option value="">Seleccionar...</option>
<?php while ($cat = $categorias->fetch_assoc()): ?>
<option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
<?php endwhile; ?>
</select>
<button type="submit" name="subir_juego">Subir</button>
</form>
</div>

<!-- Filtro -->
<div class="formulario">
<h2>Filtrar por categoría</h2>
<form method="GET">
<select name="categoria">
<option value="todas">Todas</option>
<?php
$categorias->data_seek(0);
while ($cat = $categorias->fetch_assoc()):
?>
<option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
<?php endwhile; ?>
</select>
<button type="submit">Filtrar</button>
</form>
</div>

<!-- Listado de juegos -->
<div class="juegos-grid">
<?php while ($j = $juegos->fetch_assoc()): ?>
<div class="juego">
<img src="<?= $j['imagen'] ?: 'imgs/default.jpg' ?>" alt="Juego">
<h3><?= htmlspecialchars($j['titulo']) ?></h3>
<p><strong>Categoría:</strong> <?= htmlspecialchars($j['nombre_categoria'] ?: 'Sin categoría') ?></p>
<p><?= nl2br(htmlspecialchars($j['descripcion'])) ?></p>
<a class="descargar" href="<?= htmlspecialchars($j['link_descarga']) ?>" target="_blank">Descargar</a>

<?php if ($j['cedula_usuario'] == $_SESSION['cedula']): ?>
<a class="eliminar" href="?eliminar=<?= $j['id_juego'] ?>" onclick="return confirm('¿Eliminar este juego?')">Eliminar</a>
<?php endif; ?>

<div class="comentarios">
<h4>Comentarios</h4>
<?php
$comentarios = $conexion->query("
SELECT c.*, u.nombre FROM comentarios c
JOIN usuarios u ON c.cedula_usuario = u.cedula
WHERE id_juego = " . intval($j['id_juego']) . "
ORDER BY fecha_comentario DESC
");
while ($com = $comentarios->fetch_assoc()):
?>
<div class="comentario">
<b><?= htmlspecialchars($com['nombre']) ?>:</b> <?= htmlspecialchars($com['comentario']) ?>
</div>
<?php endwhile; ?>

<form method="POST">
<input type="hidden" name="id_juego" value="<?= $j['id_juego'] ?>">
<input type="text" name="comentario" placeholder="Escribe un comentario..." required>
<button type="submit" name="agregar_comentario">Comentar</button>
</form>
</div>
</div>
<?php endwhile; ?>
</div>

</div>
</body>
</html>
