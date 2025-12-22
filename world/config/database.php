<?php
/**
 * Configuration de la base de données World
 * Modifiez ces paramètres selon votre environnement
 */

// Paramètres de connexion
define('DB_HOST', 'localhost');
define('DB_NAME', 'world');
define('DB_USER', 'root');
define('DB_PASS', 'h'); // Votre mot de passe MySQL

/**
 * Établir la connexion à la base de données
 * @return PDO Instance PDO
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
        
    } catch(PDOException $e) {
        // En production : message générique
        die("Erreur de connexion à la base de données.");
        
        // En développement : message détaillé
        // die("Erreur : " . $e->getMessage());
    }
}
?>