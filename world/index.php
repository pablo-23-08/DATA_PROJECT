<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Database - Accueil</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            max-width: 800px;
            width: 100%;
        }

        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5em;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 50px;
            font-size: 1.1em;
        }

        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .nav-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            border: none;
            border-radius: 15px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .nav-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }

        .nav-button .icon {
            font-size: 2.5em;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #999;
            font-size: 0.9em;
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 2em;
            }
            
            .button-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌍 World Database</h1>
        <p class="subtitle">Explorez les données mondiales sur les pays, villes et langues</p>
        
        <div class="button-grid">
            <a href="dashboard.php" class="nav-button">
                <span class="icon">📊</span>
                <span>Dashboard</span>
            </a>
            
            <a href="languages.php" class="nav-button">
                <span class="icon">🗣️</span>
                <span>Langues</span>
            </a>
            
            <a href="countries.php" class="nav-button">
                <span class="icon">🗺️</span>
                <span>Pays</span>
            </a>
        </div>
        
        <div class="footer">
            <p>Base de données World - Statistiques mondiales</p>
        </div>
    </div>
</body>
</html>