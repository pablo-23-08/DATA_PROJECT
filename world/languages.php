<?php
//Inclusions
require_once 'config/database.php';
require_once 'includes/functions.php';

//Définir les variables pour l'en-tête
$pageTitle = 'Langues du Monde';
$pageIcon = '🗣️';

//Connexion à la base de données
$pdo = getDbConnection();

//LANGUES LES PLUS PARLÉES DANS LE MONDE
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


//LANGUES OFFICIELLES LES PLUS RÉPANDUES
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

//STATISTIQUES GÉNÉRALES
$stmt = $pdo->query("SELECT COUNT(DISTINCT Language) as Total FROM countrylanguage");
$totalLanguages = $stmt->fetch()['Total'];

$stmt = $pdo->query("
    SELECT COUNT(DISTINCT Language) as Total 
    FROM countrylanguage 
    WHERE IsOfficial = 'T'
");
$totalOfficialLanguages = $stmt->fetch()['Total'];

//Inclure l'en-tête
include 'includes/header.php';
?>

<!-- STATISTIQUES MINI -->
<div class="stats-mini">
    <div class="stat-card">
        <div class="number"><?= formatNumber($totalLanguages) ?></div>
        <div class="label">Langues recensées</div>
    </div>
    <div class="stat-card">
        <div class="number"><?= formatNumber($totalOfficialLanguages) ?></div>
        <div class="label">Langues officielles</div>
    </div>
</div>


<!-- CONTENU PRINCIPAL -->
<div class="content-grid">
    <!-- TOP 20 LANGUES MONDIALES -->
    <div class="card">
        <h2>Top 20 Langues Mondiales</h2>
        <p style="color: var(--text-medium); margin-bottom: 15px; font-size: 0.9em;">
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

    <!-- LANGUES OFFICIELLES RÉPANDUES -->
    <div class="card">
        <h2>Langues Officielles Répandues</h2>
        <p style="color: var(--text-medium); margin-bottom: 15px; font-size: 0.9em;">
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