<?php
// Inclusion du fichier de configuration
require_once 'config.php';

// Connexion à la base de données
$pdo = getDbConnection();

// Langues les plus parlées dans le monde (calculées par nombre de locuteurs)
$stmt = $pdo->query("
    SELECT cl.Language, 
           SUM((c.Population * cl.Percentage) / 100) as TotalSpeakers,
           COUNT(DISTINCT cl.CountryCode) as NbCountries,
           SUM(CASE WHEN cl.IsOfficial = 'T' THEN 1 ELSE 0 END) as NbOfficial
    FROM countrylanguage cl
    JOIN country c ON cl.CountryCode = c.Code
    WHERE c.Population > 0
    GROUP BY cl.Language
    ORDER BY TotalSpeakers DESC
    LIMIT 20
");
$worldLanguages = $stmt->fetchAll();

// Récupération de la liste des continents pour le filtre
$stmt = $pdo->query("SELECT DISTINCT Continent FROM country ORDER BY Continent");
$continents = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Traitement du filtre par continent
$selectedContinent = isset($_GET['continent']) ? $_GET['continent'] : '';
$continentLanguages = [];

if ($selectedContinent) {
    $stmt = $pdo->prepare("
        SELECT cl.Language, 
               SUM((c.Population * cl.Percentage) / 100) as TotalSpeakers,
               COUNT(DISTINCT cl.CountryCode) as NbCountries,
               SUM(CASE WHEN cl.IsOfficial = 'T' THEN 1 ELSE 0 END) as NbOfficial
        FROM countrylanguage cl
        JOIN country c ON cl.CountryCode = c.Code
        WHERE c.Continent = :continent AND c.Population > 0
        GROUP BY cl.Language
        ORDER BY TotalSpeakers DESC
        LIMIT 15
    ");
    $stmt->execute(['continent' => $selectedContinent]);
    $continentLanguages = $stmt->fetchAll();
}

// Langues officielles les plus répandues
$stmt = $pdo->query("
    SELECT cl.Language, 
           COUNT(DISTINCT cl.CountryCode) as NbCountries,
           SUM((c.Population * cl.Percentage) / 100) as TotalSpeakers
    FROM countrylanguage cl
    JOIN country c ON cl.CountryCode = c.Code
    WHERE cl.IsOfficial = 'T' AND c.Population > 0
    GROUP BY cl.Language
    ORDER BY NbCountries DESC
    LIMIT 10
");
$officialLanguages = $stmt->fetchAll();

// Statistiques générales sur les langues
$stmt = $pdo->query("SELECT COUNT(DISTINCT Language) as Total FROM countrylanguage");
$totalLanguages = $stmt->fetch()['Total'];

$stmt = $pdo->query("
    SELECT COUNT(DISTINCT Language) as Total 
    FROM countrylanguage 
    WHERE IsOfficial = 'T'
");
$totalOfficialLanguages = $stmt->fetch()['Total'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langues - World Database</title>
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

        .stats-mini {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-mini-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
        }

        .stat-mini-card .number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .stat-mini-card .label {
            color: #666;
            margin-top: 5px;
        }

        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .filter-card h3 {
            color: #667eea;
            margin-bottom: 15px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-form select {
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 1em;
            flex: 1;
            min-width: 200px;
            cursor: pointer;
        }

        .filter-form button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .filter-form button:hover {
            transform: translateY(-2px);
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

        .card h2 {
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

        .language-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            height: 8px;
            border-radius: 4px;
            margin-top: 5px;
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
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗣️ Langues du Monde</h1>
        <a href="index.php" class="back-button">← Retour à l'accueil</a>
    </div>

    <div class="stats-mini">
        <div class="stat-mini-card">
            <div class="number"><?= formatNumber($totalLanguages) ?></div>
            <div class="label">Langues recensées</div>
        </div>
        <div class="stat-mini-card">
            <div class="number"><?= formatNumber($totalOfficialLanguages) ?></div>
            <div class="label">Langues officielles</div>
        </div>
    </div>

    <div class="filter-card">
        <h3>🔍 Filtrer par Continent</h3>
        <form method="GET" class="filter-form">
            <select name="continent" id="continent">
                <option value="">-- Sélectionner un continent --</option>
                <?php foreach($continents as $continent): ?>
                    <option value="<?= escape($continent) ?>" 
                            <?= $selectedContinent === $continent ? 'selected' : '' ?>>
                        <?= escape($continent) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filtrer</button>
            <?php if($selectedContinent): ?>
                <a href="languages.php" style="text-decoration:none;">
                    <button type="button">Réinitialiser</button>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <?php if($selectedContinent && $continentLanguages): ?>
    <div class="card full-width" style="margin-bottom: 20px;">
        <h2>🌍 Langues en <?= escape($selectedContinent) ?></h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Langue</th>
                    <th>Locuteurs estimés</th>
                    <th>Pays</th>
                    <th>Statut officiel</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $maxSpeakers = $continentLanguages[0]['TotalSpeakers'];
                $rank = 1; 
                foreach($continentLanguages as $lang): 
                ?>
                <tr>
                    <td><span class="rank-number"><?= $rank++ ?></span></td>
                    <td><strong><?= escape($lang['Language']) ?></strong></td>
                    <td><?= formatNumber($lang['TotalSpeakers']) ?></td>
                    <td><?= $lang['NbCountries'] ?> pays</td>
                    <td>
                        <?php if($lang['NbOfficial'] > 0): ?>
                            <span class="badge official">✓ <?= $lang['NbOfficial'] ?> pays</span>
                        <?php else: ?>
                            <span class="badge">Non officielle</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" style="padding: 0 12px;">
                        <div class="language-bar" 
                             style="width: <?= ($lang['TotalSpeakers'] / $maxSpeakers) * 100 ?>%;">
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="content-grid">
        <div class="card">
            <h2>🌐 Top 20 Langues Mondiales</h2>
            <p style="color: #666; margin-bottom: 15px; font-size: 0.9em;">
                Classement par nombre estimé de locuteurs
            </p>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Langue</th>
                        <th>Locuteurs</th>
                        <th>Pays</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $maxSpeakers = $worldLanguages[0]['TotalSpeakers'];
                    $rank = 1; 
                    foreach($worldLanguages as $lang): 
                    ?>
                    <tr>
                        <td><span class="rank-number"><?= $rank++ ?></span></td>
                        <td><strong><?= escape($lang['Language']) ?></strong></td>
                        <td><?= formatNumber($lang['TotalSpeakers']) ?></td>
                        <td><?= $lang['NbCountries'] ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="padding: 0 12px;">
                            <div class="language-bar" 
                                 style="width: <?= ($lang['TotalSpeakers'] / $maxSpeakers) * 100 ?>%;">
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>📜 Langues Officielles Répandues</h2>
            <p style="color: #666; margin-bottom: 15px; font-size: 0.9em;">
                Langues ayant un statut officiel dans plusieurs pays
            </p>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Langue</th>
                        <th>Pays</th>
                        <th>Locuteurs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach($officialLanguages as $lang): ?>
                    <tr>
                        <td><span class="rank-number"><?= $rank++ ?></span></td>
                        <td><strong><?= escape($lang['Language']) ?></strong></td>
                        <td><span class="badge official"><?= $lang['NbCountries'] ?> pays</span></td>
                        <td><?= formatNumber($lang['TotalSpeakers']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>