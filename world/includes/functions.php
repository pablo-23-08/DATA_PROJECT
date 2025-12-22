<?php
/**
 * Fonctions utilitaires pour le projet World Database
 */

/**
 * Formater un nombre avec espaces pour les milliers
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
 * Échapper les données HTML pour éviter les failles XSS
 * @param string $data Données à échapper
 * @return string Données sécurisées
 */
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Obtenir l'URL de base du projet
 * @return string URL de base
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $script;
}

/**
 * Rediriger vers une page
 * @param string $page Nom de la page
 */
function redirect($page) {
    header('Location: ' . $page);
    exit;
}

/**
 * Afficher un message d'erreur formaté
 * @param string $message Message d'erreur
 */
function showError($message) {
    echo '<div class="error-message">' . escape($message) . '</div>';
}

/**
 * Vérifier si une valeur est vide ou nulle
 * @param mixed $value Valeur à vérifier
 * @return bool
 */
function isEmpty($value) {
    return $value === null || $value === '' || $value === 0;
}
?>