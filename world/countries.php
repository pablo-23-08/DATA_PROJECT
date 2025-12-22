<?php
// Inclusion du fichier de configuration
require_once 'config.php';

// Connexion à la base de données
$pdo = getDbConnection();

// Récupération de la liste des pays pour le menu déroulant
$stmt = $pdo->query("SELECT Code, Name, Continent FROM country ORDER BY Name");
$allCountries = $stmt->fetchAll();

// Traitement de la recherche
$selectedCountry = isset($_GET['country']) ? $_GET['country'] : '';
$countryData = null;
$capitalData = null;
$languages = [];
$cities = [];

if ($selectedCountry) {
    // Informations du pays
    $stmt = $pdo->prepare("SELECT * FROM country WHERE Code = :code");
    $stmt->execute(['code' => $selectedCountry]);
    $countryData = $stmt->fetch();
    
    if ($countryData) {
        // Informations de la capitale
        if ($countryData['Capital']) {
            $stmt = $pdo->prepare("SELECT * FROM city WHERE ID = :id");
            $stmt->execute(['id' => $countryData['Capital']]);
            $capitalData = $stmt->fetch();
        }
        
        // Langues parlées dans le pays
        $stmt = $pdo->prepare("
            SELECT Language, IsOfficial, Percentage
            FROM countrylanguage
            WHERE CountryCode = :code
            ORDER BY Percentage DESC
        ");
        $stmt->execute(['code' => $selectedCountry]);
        $languages = $stmt->fetchAll();
        
        // Principales villes du pays
        $stmt = $pdo->prepare("
            SELECT Name, District, Population
            FROM city
            WHERE CountryCode = :code
            ORDER BY Population DESC
            LIMIT 10
        ");
        $stmt->execute(['code' => $selectedCountry]);
        $cities = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pays - World Database</title>
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

        .search-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .search-card h3 {
            color: #667eea;
            margin-bottom: 15px;
        }

        .search-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-form select {
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 1em;
            flex: 1;
            min-width: 250px;
            cursor: pointer;
        }

        .search-form button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .search-form button:hover {
            transform: translateY(-2px);
        }

        .country-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
        }

        .country-header h2 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .country-header .subtitle {
            color: #666;
            font-size: 1.2em;
        }

        .country-header .flag {
            font-size: 4em;
            margin-bottom: 15px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .stat-card .label {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .stat-card .value {
            color: #667eea;
            font-size: 1.8em;
            font-weight: bold;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .card.full-width {
            grid-column: 1 / -1;
        }

        .card h3 {
            color: #667eea;
            margin-bottom: 20px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
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

        .badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
        }

        .badge.official {
            background: #10b981;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            gap: 15px;
        }

        .info-row .label {
            color: #666;
            font-weight: bold;
            flex-shrink: 0;
        }

        .info-row .value {
            color: #333;
            text-align: right;
        }

        .percentage-bar {
            background: #eee;
            height: 20px;
            border-radius: 10px;
            margin-top: 5px;
            overflow: hidden;
        }

        .percentage-fill {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.1em;
        }

        .rank-number {
            background: #667eea;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-row .value {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗺️ Informations sur les Pays</h1>
        <a href="index.php" class="back-button">← Retour à l'accueil</a>
    </div>

    <div class="search-card">
        <h3>🔍 Rechercher un Pays</h3>
        <form method="GET" class="search-form">
            <select name="country" id="country" required>
                <option value="">-- Sélectionner un pays --</option>
                <?php foreach($allCountries as $country): ?>
                    <option value="<?= escape($country['Code']) ?>" 
                            <?= $selectedCountry === $country['Code'] ? 'selected' : '' ?>>
                        <?= escape($country['Name']) ?> (<?= escape($country['Continent']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">🔎 Rechercher</button>
        </form>
    </div>

    <?php if($countryData): ?>
        <div class="country-header">
            <div class="flag">🌍</div>
            <h2><?= escape($countryData['Name']) ?></h2>
            <div class="subtitle">
                <?= escape($countryData['Continent']) ?> • <?= escape($countryData['Region']) ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">👥</div>
                <div class="label">Population</div>
                <div class="value"><?= formatNumber($countryData['Population']) ?></div>
            </div>
            <div class="stat-card">
                <div class="icon">❤️</div>
                <div class="label">Espérance de vie</div>
                <div class="value">
                    <?= $countryData['LifeExpectancy'] ? formatNumber($countryData['LifeExpectancy'], 1) . ' ans' : 'N/A' ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon">📏</div>
                <div class="label">Superficie</div>
                <div class="value"><?= formatNumber($countryData['SurfaceArea']) ?> km²</div>
            </div>
            <div class="stat-card">
                <div class="icon">🏛️</div>
                <div class="label">Capitale</div>
                <div class="value" style="font-size: 1.2em;">
                    <?= $capitalData ? escape($capitalData['Name']) : 'N/A' ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon">💰</div>
                <div class="label">PNB</div>
                <div class="value" style="font-size: 1.4em;">
                    <?= $countryData['GNP'] ? formatNumber($countryData['GNP']) . ' M$' : 'N/A' ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon">📅</div>
                <div class="label">Indépendance</div>
                <div class="value" style="font-size: 1.4em;">
                    <?= $countryData['IndepYear'] ?: 'N/A' ?>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div class="card">
                <h3>ℹ️ Informations Générales</h3>
                <div class="info-row">
                    <span class="label">Nom local:</span>
                    <span class="value"><?= escape($countryData['LocalName']) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Forme de gouvernement:</span>
                    <span class="value"><?= escape($countryData['GovernmentForm']) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Chef d'État:</span>
                    <span class="value"><?= escape($countryData['HeadOfState'] ?: 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Code pays:</span>
                    <span class="value">
                        <span class="badge"><?= escape($countryData['Code']) ?></span>
                        <span class="badge"><?= escape($countryData['Code2']) ?></span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">PNB ancien:</span>
                    <span class="value"><?= $countryData['GNPOld'] ? formatNumber($countryData['GNPOld']) . ' M$' : 'N/A' ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Densité:</span>
                    <span class="value">
                        <?php 
                        if ($countryData['Population'] > 0 && $countryData['SurfaceArea'] > 0) {
                            $density = $countryData['Population'] / $countryData['SurfaceArea'];
                            echo formatNumber($density, 1) . ' hab/km²';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </span>
                </div>
                <?php if ($capitalData): ?>
                <div class="info-row">
                    <span class="label">Population capitale:</span>
                    <span class="value"><?= formatNumber($capitalData['Population']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>🗣️ Langues Parlées</h3>
                <?php if($languages): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Langue</th>
                                <th>Statut</th>
                                <th>Pourcentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($languages as $lang): ?>
                            <tr>
                                <td><strong><?= escape($lang['Language']) ?></strong></td>
                                <td>
                                    <?php if($lang['IsOfficial'] === 'T'): ?>
                                        <span class="badge official">✓ Officielle</span>
                                    <?php else: ?>
                                        <span class="badge">Non officielle</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= formatNumber($lang['Percentage'], 1) ?>%</strong>
                                    <div class="percentage-bar">
                                        <div class="percentage-fill" style="width: <?= $lang['Percentage'] ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">Aucune information disponible sur les langues</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($cities): ?>
        <div class="card full-width" style="margin-top: 20px;">
            <h3>🏙️ Principales Villes du Pays</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>District/Province</th>
                        <th>Population</th>
                        <th>% du pays</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rank = 1; 
                    foreach($cities as $city): 
                        $percentageOfCountry = $countryData['Population'] > 0 
                            ? ($city['Population'] / $countryData['Population']) * 100 
                            : 0;
                    ?>
                    <tr>
                        <td><span class="rank-number"><?= $rank++ ?></span></td>
                        <td><strong><?= escape($city['Name']) ?></strong></td>
                        <td><?= escape($city['District']) ?></td>
                        <td><?= formatNumber($city['Population']) ?></td>
                        <td><?= formatNumber($percentageOfCountry, 2) ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    <?php elseif($selectedCountry): ?>
        <div class="card">
            <div class="no-data">
                ❌ Aucun pays trouvé avec ce code.<br>
                <small>Veuillez sélectionner un pays valide dans la liste.</small>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="no-data">
                👆 Sélectionnez un pays dans le menu déroulant ci-dessus<br>
                pour voir ses informations détaillées
            </div>
        </div>
    <?php endif; ?>
</body>
</html>