<?php
// Inclusions
require_once 'config/database.php';
require_once 'includes/functions.php';

// Définir les variables pour l'en-tête
$pageTitle = 'Informations sur les Pays';
$pageIcon = '🗺️';

// Connexion à la base de données
$pdo = getDbConnection();

// LISTE DES PAYS pour le menu déroulant
$stmt = $pdo->query("SELECT Code, Name, Continent FROM country ORDER BY Name");
$allCountries = $stmt->fetchAll();

// TRAITEMENT DE LA RECHERCHE
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

// Inclure l'en-tête
include 'includes/header.php';
?>

<!-- FORMULAIRE DE RECHERCHE -->
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
    <!-- EN-TÊTE DU PAYS -->
    <div class="country-header">
        <div class="flag">🌍</div>
        <h2><?= escape($countryData['Name']) ?></h2>
        <div class="subtitle">
            <?= escape($countryData['Continent']) ?> • <?= escape($countryData['Region']) ?>
        </div>
    </div>

    <!-- STATISTIQUES DU PAYS -->
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

    <!-- INFORMATIONS DÉTAILLÉES -->
    <div class="content-grid">
        <!-- INFORMATIONS GÉNÉRALES -->
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

        <!-- LANGUES PARLÉES -->
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

    <!-- PRINCIPALES VILLES -->
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
    <!-- PAYS NON TROUVÉ -->
    <div class="card">
        <div class="no-data">
            ❌ Aucun pays trouvé avec ce code.<br>
            <small>Veuillez sélectionner un pays valide dans la liste.</small>
        </div>
    </div>
<?php else: ?>
    <!-- MESSAGE INITIAL -->
    <div class="card">
        <div class="no-data">
            👆 Sélectionnez un pays dans le menu déroulant ci-dessus<br>
            pour voir ses informations détaillées
        </div>
    </div>
<?php endif; ?>

</body>
</html>