<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casa Vieja Hotel y Restaurante</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #DC8711, #664D07);
            color: white;
            text-align: center;
            padding: 2rem;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 600px;
            background: rgba(0,0,0,0.3);
            padding: 3rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            background-color: #DC8711;
            color: white;
        }
        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        .status {
            margin-top: 2rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏨 Casa Vieja Hotel y Restaurante</h1>
        <p>Tu hogar en el corazón de la montaña</p>
        <p>La landing page se está cargando desde los archivos estáticos...</p>
        
        <div class="buttons">
            <a href="/login" class="btn btn-primary">Acceso Administración</a>
            <a href="/dashboard" class="btn btn-secondary">Dashboard</a>
        </div>
        
        <div class="status">
            <p>Si esta página persiste, verifica la configuración de los archivos estáticos de la landing page.</p>
            <p>Los archivos deben estar ubicados en: <code>public/hotel_landing/</code></p>
        </div>
    </div>

    <script>
        // Redirect to the actual landing page files after 3 seconds
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    </script>
</body>
</html>
