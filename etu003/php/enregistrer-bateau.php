<?php
// php/enregistrer-bateau.php

require_once('database.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Établir la connexion à la base de données
$db = dbConnect();
if (!$db) {
    header('HTTP/1.1 503 Service Unavailable');
    exit();
}

// On s'assure que la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit();
}

$formData = $_POST;

// Utilisation d'une transaction pour garantir l'intégrité des données
$db->beginTransaction();

try {
    // 1. Convertir l'état de navigation textuel en ID
    $id_statut = 0;
    switch ($formData['state']) {
        case 'naviguant': $id_statut = 1; break;
        case 'ancre':     $id_statut = 2; break;
        case 'amarre':    $id_statut = 3; break;
        default: throw new Exception("État de navigation invalide.");
    }

    // 2. Vérifier si le bateau existe, et le créer uniquement si nécessaire
    $id_bateau = dbFindBateauByMmsi($db, $formData['mmsi']);

    if (!$id_bateau) {
        // Le bateau n'existe pas, on le crée
        $id_bateau = dbInsertBateau($db, $formData);
        if (!$id_bateau) {
            throw new Exception("Échec de la création du bateau.");
        }
    }
    // Si le bateau existe déjà, on ne fait rien et on continue avec son ID.

    // 3. Préparer les données et insérer le relevé AIS
    $releveData = $formData;
    $releveData['id_bateau'] = $id_bateau;
    $releveData['id_statut'] = $id_statut;

    if (!dbInsertReleve($db, $releveData)) {
        throw new Exception("Échec de l'enregistrement du relevé.");
    }

    // Si tout s'est bien passé, on valide la transaction
    $db->commit();

    // On envoie une réponse de succès
    header('Content-Type: application/json; charset=utf-8');
    header('HTTP/1.1 200 OK');
    echo json_encode(['message' => 'Nouveau relevé enregistré avec succès !']);

} catch (Exception $e) {
    // En cas d'erreur, on annule la transaction
    $db->rollBack();

    // On envoie une réponse d'erreur
    header('Content-Type: application/json; charset=utf-8');
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['message' => $e->getMessage()]);
}

exit;
?>