<?php
// Inclusion du fichier de configuration
require_once 'config.php';

// Connexion à la base de données
$pdo = getDbConnection();

// Récupération des données : Population par continent
$stmt = $pdo->query("
    SELECT Continent, 
           SUM(Population) as TotalPopulation, 
           COUNT(*) as NbPays,
           AVG(LifeExpectancy) as AvgLifeExpectancy
    FROM country 
    WHERE Population > 0
    GROUP BY Continent 
    ORDER BY TotalPopulation DESC
");
$continents = $stmt->fetchAll();

// Récupération des 10 villes les plus peuplées
$stmt = $pdo->query("
    SELECT c.Name as CityName, 
           c.Population, 
           co.Name as CountryName, 
           co.Continent,
           c.District
    FROM city c
    JOIN country co ON c.CountryCode = co.Code
    WHERE c.Population > 0
    ORDER BY c.Population DESC
    LIMIT 10
");
$cities = $stmt->fetchAll();

// Statistiques globales
$stmt = $pdo->query("SELECT SUM(Population) as TotalPop FROM country");
$totalPopulation = $stmt->fetch()['TotalPop'];

$stmt = $pdo->query("SELECT COUNT(*) as Total FROM country");
$totalCountries = $stmt->fetch()['Total'];

$stmt = $pdo->query("SELECT COUNT(*) as Total FROM city");
$totalCities = $stmt->fetch()['Total'];

$stmt = $pdo->query("SELECT AVG(LifeExpectancy) as AvgLife FROM country WHERE LifeExpectancy > 0");
$avgLifeExpectancy = $stmt->fetch()['AvgLife'];

// Top 5 pays les plus peuplés
$stmt = $pdo->query("
    SELECT Name, Population, Continent
    FROM country
    WHERE Population > 0
    ORDER BY Population DESC
    LIMIT 5
");
$topCountries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - World Database</title>
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
            padding: 20px;
        }

        .header {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #667eea;
            font-size: 2em;
        }

        .back-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.2s;
            display: inline-block;
        }

        .back-button:hover {
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }

        .stat-card .label {
            color: #666;
            font-size: 1.1em;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .card h2 {
            color: #667eea;
            margin-bottom: 20px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .population-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 20px;
            border-radius: 10px;
            margin-top: 5px;
            transition: width 0.5s ease;
        }

        .rank-badge {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .rank-badge.gold {
            background: linear-gradient(135deg, #FFD700, #FFA500);
        }

        .rank-badge.silver {
            background: linear-gradient(135deg, #C0C0C0, #808080);
        }

        .rank-badge.bronze {
            background: linear-gradient(135deg, #CD7F32, #8B4513);
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Dashboard Mondial</h1>
        <a href="index.php" class="back-button">← Retour à l'accueil</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon">🌍</div>
            <div class="label">Population Mondiale</div>
            <div class="number"><?= formatNumber($totalPopulation) ?></div>
        </div>
        <div class="stat-card">
            <div class="icon">🗺️</div>
            <div class="label">Nombre de Pays</div>
            <div class="number"><?= formatNumber($totalCountries) ?></div>
        </div>
        <div class="stat-card">
            <div class="icon">🏙️</div>
            <div class="label">Nombre de Villes</div>
            <div class="number"><?= formatNumber($totalCities) ?></div>
        </div>
        <div class="stat-card">
            <div class="icon">❤️</div>
            <div class="label">Espérance de vie moyenne</div>
            <div class="number"><?= formatNumber($avgLifeExpectancy, 1) ?> ans</div>
        </div>
    </div>

    <div class="content-grid">
        <div class="card">
            <h2>🌍 Population par Continent</h2>
            <table>
                <thead>
                    <tr>
                        <th>Continent</th>
                        <th>Population</th>
                        <th>Pays</th>
                        <th>Esp. vie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($continents as $continent): ?>
                    <tr>
                        <td><strong><?= escape($continent['Continent']) ?></strong></td>
                        <td><?= formatNumber($continent['TotalPopulation']) ?></td>
                        <td><?= $continent['NbPays'] ?> pays</td>
                        <td><?= formatNumber($continent['AvgLifeExpectancy'], 1) ?> ans</td>
                    </tr>
                    <tr>
                        <td colspan="4" style="padding: 0 12px;">
                            <div class="population-bar" 
                                 style="width: <?= ($continent['TotalPopulation'] / $totalPopulation) * 100 ?>%;">
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>🏙️ Top 10 Villes les Plus Peuplées</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Pays</th>
                        <th>Population</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1; 
                    foreach($cities as $city): 
                        $badgeClass = '';
                        if ($rank == 1) $badgeClass = 'gold';
                        elseif ($rank == 2) $badgeClass = 'silver';
                        elseif ($rank == 3) $badgeClass = 'bronze';
                    ?>
                    <tr>
                        <td><span class="rank-badge <?= $badgeClass ?>"><?= $rank ?></span></td>
                        <td><strong><?= escape($city['CityName']) ?></strong><br>
                            <small style="color: #999;"><?= escape($city['District']) ?></small>
                        </td>
                        <td><?= escape($city['CountryName']) ?><br>
                            <small style="color: #999;"><?= escape($city['Continent']) ?></small>
                        </td>
                        <td><strong><?= formatNumber($city['Population']) ?></strong></td>
                    </tr>
                    <?php 
                    $rank++;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h2>🏆 Top 5 Pays les Plus Peuplés</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pays</th>
                    <th>Continent</th>
                    <th>Population</th>
                    <th>% Mondial</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                foreach($topCountries as $country): 
                    $percentage = ($country['Population'] / $totalPopulation) * 100;
                ?>
                <tr>
                    <td><strong><?= $rank++ ?></strong></td>
                    <td><strong><?= escape($country['Name']) ?></strong></td>
                    <td><?= escape($country['Continent']) ?></td>
                    <td><?= formatNumber($country['Population']) ?></td>
                    <td><?= formatNumber($percentage, 2) ?>%</td>
                </tr>
                <tr>
                    <td colspan="5" style="padding: 0 12px;">
                        <div class="population-bar" style="width: <?= $percentage ?>%;"></div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>