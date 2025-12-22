<?php
// Inclusions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Définir les variables pour l'en-tête
$pageTitle = 'Dashboard Mondial';
$pageIcon = '📊';

// Connexion à la base de données
$pdo = getDbConnection();

// STATISTIQUES GLOBALES
$stmt = $pdo->query("SELECT SUM(Population) as TotalPop FROM country");
$totalPopulation = $stmt->fetch()['TotalPop'];

$stmt = $pdo->query("SELECT COUNT(*) as Total FROM country");
$totalCountries = $stmt->fetch()['Total'];

$stmt = $pdo->query("SELECT COUNT(*) as Total FROM city");
$totalCities = $stmt->fetch()['Total'];

$stmt = $pdo->query("SELECT AVG(LifeExpectancy) as AvgLife FROM country WHERE LifeExpectancy > 0");
$avgLifeExpectancy = $stmt->fetch()['AvgLife'];

// POPULATION PAR CONTINENT
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

// TOP 10 VILLES
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

// TOP 5 PAYS
$stmt = $pdo->query("
    SELECT Name, Population, Continent
    FROM country
    WHERE Population > 0
    ORDER BY Population DESC
    LIMIT 5
");
$topCountries = $stmt->fetchAll();

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- STATISTIQUES GLOBALES -->
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

<!-- CONTENU PRINCIPAL -->
<div class="content-grid">
    <!-- POPULATION PAR CONTINENT -->
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

    <!-- TOP 10 VILLES -->
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
                        <small style="color: #7A8B7E;"><?= escape($city['District']) ?></small>
                    </td>
                    <td><?= escape($city['CountryName']) ?><br>
                        <small style="color: #7A8B7E;"><?= escape($city['Continent']) ?></small>
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

<!-- TOP 5 PAYS -->
<div class="card full-width">
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