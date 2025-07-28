<?php

// AJOUTEZ CES 3 LIGNES POUR LE DÉBOGAGE
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuration de la base de données
require_once('constants.php');

// Headers pour JSON et CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Connexion à la base de données avec gestion d'erreurs améliorée
    $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);

    // Requête SQL corrigée pour récupérer les dernières données de chaque bateau
    $sql = "
    SELECT DISTINCT
        b.MMSI, 
        r.BaseDateTime AS Horodatage,
        r.Latitude,
        r.Longitude,
        r.SOG,
        r.COG,
        r.Heading AS Cap,
        b.VesselName AS Nom,
        COALESCE(s.libelle_statut, 'Inconnu') AS Etat,
        b.Lenght AS Longueur,
        b.Width AS Largeur,
        b.Draft AS Tirant_eau
    FROM releve_ais r
    INNER JOIN bateau b ON r.id_bateau = b.id_bateau
    LEFT JOIN statut_navigation s ON r.id_statut = s.id_statut
    INNER JOIN (
        SELECT id_bateau, MAX(BaseDateTime) as derniere_date
        FROM releve_ais
        GROUP BY id_bateau
    ) derniers ON r.id_bateau = derniers.id_bateau AND r.BaseDateTime = derniers.derniere_date
    WHERE r.Latitude IS NOT NULL 
      AND r.Longitude IS NOT NULL
      AND b.MMSI IS NOT NULL
    ORDER BY r.BaseDateTime DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    // Log pour débogage
    error_log("Nombre de résultats: " . count($result));

    if (empty($result)) {
        // Si aucun résultat, retourner des données de test
        $result = [
            [
                'MMSI' => '227123456',
                'Horodatage' => date('Y-m-d H:i:s'),
                'Latitude' => '48.8566',
                'Longitude' => '2.3522',
                'SOG' => '15.2',
                'COG' => '180',
                'Cap' => '180',
                'Nom' => 'Le Foch',
                'Etat' => 'En navigation',
                'Longueur' => '265',
                'Largeur' => '31',
                'Tirant_eau' => '8.5'
            ],
            [
                'MMSI' => '228765432',
                'Horodatage' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
                'Latitude' => '48.40',
                'Longitude' => '-4.48',
                'SOG' => '12.0',
                'COG' => '90',
                'Cap' => '90',
                'Nom' => 'Bretagne',
                'Etat' => 'En navigation',
                'Longueur' => '140',
                'Largeur' => '25',
                'Tirant_eau' => '6.1'
            ]
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Log de l'erreur
    error_log("Erreur PDO: " . $e->getMessage());
    
    // Retourner une erreur JSON
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur de base de données',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Log de l'erreur générale
    error_log("Erreur générale: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur serveur',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>