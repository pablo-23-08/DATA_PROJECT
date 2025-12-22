<?php
/**
 * Fichier de configuration de la base de données
 * Modifiez ces paramètres selon votre configuration locale
 */

// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'world');
define('DB_USER', 'root');
define('DB_PASS', 'h'); // Mettez votre mot de passe MySQL ici si nécessaire

/**
 * Fonction pour établir la connexion à la base de données
 * @return PDO Instance PDO pour les requêtes
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch(PDOException $e) {
        // En production, ne jamais afficher les détails de l'erreur
        die("Erreur de connexion à la base de données. Veuillez contacter l'administrateur.");
        // Pour le développement, décommentez la ligne suivante :
        // die("Erreur de connexion : " . $e->getMessage());
    }
}

/**
 * Fonction utilitaire pour formater les nombres
 * @param mixed $number Nombre à formater
 * @param int $decimals Nombre de décimales
 * @return string Nombre formaté
 */
function formatNumber($number, $decimals = 0) {
    if ($number === null || $number === '') {
        return 'N/A';
    }
    return number_format($number, $decimals, ',', ' ');
}

/**
 * Fonction pour échapper les données HTML
 * @param string $data Données à échapper
 * @return string Données échappées
 */
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>