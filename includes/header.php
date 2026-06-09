<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUE FITA LOCADORA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #141414;
            color: #ffffff;
        }

        .appBar {
            background-color: #1e1e1e;
            padding: 15px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        a {
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: color 0.3s ease;
            color: #b3b3b3;
        }

        a:hover {
            color: #00aeff;
        }

        img {
            width: 350px;
            height: auto;
            max-height: 70px;
            object-fit: contain;
        }
    </style>
</head>
<body>

    <div class="appBar">
        <img src="uploads/imagens/QueFita.png" alt="Logo Que Fita">
        
        <div class="nav-links">
            <a href="index.php">Cadastrar Filme</a>
            <a href="locacoes.php">Locações</a>
        </div>
    </div>

</body>
</html>