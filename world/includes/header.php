<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? escape($pageTitle) : 'World Database' ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1><?= isset($pageIcon) ? $pageIcon : '🌍' ?> <?= isset($pageTitle) ? escape($pageTitle) : 'World Database' ?></h1>
        <a href="index.php" class="back-button">← Retour à l'accueil</a>
    </div>