<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied!!</title>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <link rel="shortcut icon" type="image/png" href="images/logos/favicon.png" />
    <link rel="stylesheet" href="css/styles.min.css" />
    <style>
        /* Mengatur body agar elemen di dalamnya berada tepat di tengah */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
            background-color: #ffffff;
        }

        /* Mengatur teks Access Denied */
        h1 {
            font-family: Arial, sans-serif;
            font-size: 2rem;
            margin-top: 20px;
            color: #333;
        }
    </style>
</head>

<body>
    <!-- Lottie animation player -->
    <dotlottie-player src="https://lottie.host/1513a68b-e5ec-419c-9497-e7049cb5d5cd/ajEQz9hQf1.json"
        background="transparent" speed="5" style="width: 300px; height: 300px;" loop autoplay>
    </dotlottie-player>

    <!-- Teks Access Denied -->
    <h1>
        <strong>Anda tidak memiliki akses!</strong>
    </h1>


</body>

</html>