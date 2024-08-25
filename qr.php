<?php

class GenerateQr
{
    private $generator;

    public function __construct()
    {
        // Incluimos la biblioteca de códigos de barras
        require_once "barcode.php";

        // Creamos una instancia del generador de códigos de barras
        $this->generator = new barcode_generator();
    }

    public function generateQrPng(string $data): string
    {
        // Generamos el código QR en formato GDImage
        $image = $this->generator->render_image("qr", $data, "");

        if ($image !== false) {
            // Creamos un recurso en memoria para capturar la salida de imagepng
            ob_start();
            imagepng($image);
            $pngData = ob_get_clean();

            // Liberamos la memoria asociada a la imagen
            imagedestroy($image);

            // Codificamos los datos de la imagen en base64
            return 'data:image/png;base64,' . base64_encode($pngData);
        } else {
            return '';
        }
    }
}

// Verificamos si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link'])) {
    $link = trim($_POST['link']);

    // Verificamos que el enlace no esté vacío
    if (!empty($link)) {
        $qrGenerator = new GenerateQr();
        $qrImageData = $qrGenerator->generateQrPng($link);
    } else {
        echo '<p style="color:red;">Por favor, ingrese un enlace válido.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de QR</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>

<body>

    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }

        .container {
            text-align: center;
            margin-top: 3rem;
            max-width: 600px;
            width: 90%;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
    </style>
    <h1>Generador de Código QR</h1>
    <form method="post" action="">
        <label for="link">Ingrese un enlace:</label><br>
        <input type="text" id="link" name="link" required><br><br>
        <button type="submit">Generar QR</button>
    </form>

    <p>Mira mas <a href="/projects.html">proyectos</a></p>

    <?php if (isset($qrImageData) && !empty($qrImageData)): ?>
        <h2>Código QR generado:</h2>
        <img src="<?= htmlspecialchars($qrImageData) ?>" alt="Código QR"><br>
        <a href="<?= $qrImageData ?>" download="qr_code.png">
            <button type="button">Descargar QR</button>
        </a>
    <?php endif; ?>

</body>

</html>