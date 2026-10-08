<?php
// Crear directorio de subida si no existe
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 1. Mostrar el formulario si la petición es GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include 'captura.html';
    exit;
}

// 2. Procesar los datos si la petición es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Evitar inyección de código
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    $alias = htmlspecialchars(trim($_POST['alias'] ?? ''), ENT_QUOTES, 'UTF-8');
    $edad = (int)($_POST['edad'] ?? 0);
    $armas = isset($_POST['armas']) && is_array($_POST['armas']) 
        ? implode(', ', array_map('htmlspecialchars', $_POST['armas'])) 
        : 'Ninguna';
    $magia = htmlspecialchars($_POST['magia'] ?? 'No', ENT_QUOTES, 'UTF-8');

    // Imagen por defecto (debe existir el archivo calavera.png en la misma carpeta)
    $imagenRuta = 'calavera.png';
    $mensajeSuperior = '';
    $mensajeInferior = '';

    // Comprobar si se envió una imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $fileError = $_FILES['imagen']['error'];
        $fileSize = $_FILES['imagen']['size'];
        $tmpName = $_FILES['imagen']['tmp_name'];

        $limiteTamano = 1024000; // 1000 KB en bytes

        if ($fileError === UPLOAD_ERR_OK && $tmpName) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            // Validar que sea PNG y <= 10 KB
            if ($mimeType === 'image/png' && $fileSize <= $limiteTamano) {
                $nombreArchivo = time() . '_' . basename($_FILES['imagen']['name']);
                $rutaDestino = $uploadDir . $nombreArchivo;

                if (move_uploaded_file($tmpName, $rutaDestino)) {
                    $imagenRuta = $rutaDestino;
                    $mensajeSuperior = 'Imagen subida:';
                } else {
                    $mensajeInferior = 'Error al subir la imagen';
                }
            } else {
                $mensajeInferior = 'Error al subir la imagen';
            }
        } else {
            $mensajeInferior = 'Error al subir la imagen';
        }
    } else {
        // No se seleccionó ninguna imagen
        $mensajeSuperior = 'No se subió ninguna imagen.';
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos del Jugador</title>
    <style>
        body { font-family: sans-serif; background-color: #e0e0e0; padding: 20px; }
        .card { background-color: #ffff33; padding: 30px; border-radius: 12px; max-width: 550px; margin: auto; box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
        h1 { text-align: center; margin-top: 0; }
        .container { display: flex; justify-content: space-between; margin-top: 20px; }
        .details { flex: 1; }
        .details p { margin: 10px 0; font-size: 16px; }
        .image-box { width: 180px; text-align: center; }
        .image-box img { width: 160px; height: auto; border: 1px solid #00008b; background-color: #fff; }
        .msg { font-weight: bold; margin-bottom: 10px; font-size: 15px; }
        .error-msg { margin-top: 10px; font-size: 15px; }
    </style>
</head>
<body>

<div class="card">
    <h1>Datos del Jugador</h1>
    
    <div class="container">
        <div class="details">
            <p><strong>Nombre:</strong> <?= $nombre ?></p>
            <p><strong>Alias:</strong> <?= $alias ?></p>
            <p><strong>Edad:</strong> <?= $edad ?></p>
            <p><strong>Armas seleccionadas:</strong> <?= $armas ?></p>
            <p><strong>¿Practica artes mágicas?:</strong> <?= $magia ?></p>
        </div>

        <div class="image-box">
            <?php if (!empty($mensajeSuperior)): ?>
                <div class="msg"><?= $mensajeSuperior ?></div>
            <?php endif; ?>

            <img src="<?= htmlspecialchars($imagenRuta) ?>" alt="Imagen del jugador">

            <?php if (!empty($mensajeInferior)): ?>
                <div class="error-msg"><?= $mensajeInferior ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
<?php
}
?>