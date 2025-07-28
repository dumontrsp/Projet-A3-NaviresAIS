<?php
// Inclut les constantes de connexion à la base de données (DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD)
require_once('constants.php');

/**
 * Établit une connexion à la base de données en utilisant PDO.
 * @return PDO|false Retourne l'objet PDO en cas de succès, false sinon.
 */
function dbConnect() {
    try {
        // Création d'une nouvelle instance PDO avec les paramètres de connexion
        $db = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD);
        // Active le mode d'erreur pour lancer des exceptions PDO en cas de problème
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $exception) {
        // En cas d'erreur, on loggue le message dans les logs serveur
        error_log("Connection error: " . $exception->getMessage());
        // On retourne false pour indiquer l'échec
        return false;
    }
    // Retourne l'objet PDO en cas de succès
    return $db;
}

/**
 * Cherche un bateau par son MMSI et retourne son ID s'il existe.
 * @param PDO $db L'objet de connexion à la base de données.
 * @param string $mmsi Le MMSI du bateau à rechercher.
 * @return mixed Retourne l'ID du bateau ou false si non trouvé.
 */
function dbFindBateauByMmsi($db, $mmsi) {
    try {
        // Prépare la requête SQL pour rechercher l'id du bateau via le MMSI
        $statement = $db->prepare('SELECT id_bateau FROM bateau WHERE MMSI = ?');
        // Exécute la requête avec le MMSI fourni en paramètre sécurisé
        $statement->execute([$mmsi]);
        // Renvoie la première colonne du résultat (id_bateau) ou false si aucune ligne
        return $statement->fetchColumn();
    } catch (PDOException $exception){
        // Log d'erreur en cas de problème avec la requête SQL
        error_log('Request error: '.$exception->getMessage());
        return false;
    }
}

/**
 * Insère un nouveau bateau dans la base de données.
 * @param PDO $db L'objet de connexion à la base de données.
 * @param array $data Les données du formulaire.
 * @return mixed Retourne l'ID du nouveau bateau ou false en cas d'échec.
 */
function dbInsertBateau($db, $data) {
    try {
        // Requête SQL d'insertion avec des placeholders pour la sécurité
        $request = 'INSERT INTO bateau (MMSI, IMO, VesselName, Lenght, Width, Draft, id_type) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $statement = $db->prepare($request);
        // Exécution avec les données du formulaire (valeurs par défaut si absence)
        $statement->execute([
            $data['mmsi'],
            $data['imo'] ?? 'N/A',          // Si IMO absent, on met 'N/A'
            $data['name'],
            $data['length'],
            $data['width'],
            $data['draft'],
            $data['type'] ?? 0              // Si type absent, on met 0
        ]);
        // Retourne l'ID du dernier enregistrement inséré
        return $db->lastInsertId();
    } catch (PDOException $exception){
        // Log en cas d'erreur d'insertion
        error_log('Request error: '.$exception->getMessage());
        return false;
    }
}

/**
 * Insère un nouveau relevé AIS dans la base de données.
 * @param PDO $db L'objet de connexion à la base de données.
 * @param array $data Les données du formulaire enrichies avec id_bateau et id_statut.
 * @return bool Retourne true en cas de succès, false sinon.
 */
function dbInsertReleve($db, $data) {
    try {
        // Requête SQL d'insertion pour un relevé AIS
        $request = 'INSERT INTO releve_ais (BaseDateTime, Latitude, Longitude, SOG, COG, id_bateau, Heading, id_statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $statement = $db->prepare($request);
        // Exécution avec les données fournies
        $statement->execute([
            $data['date'],
            $data['latitude'],
            $data['longitude'],
            $data['sog'],
            $data['cog'],
            $data['id_bateau'],
            $data['heading'],
            $data['id_statut']
        ]);
        // Retourne true si insertion réussie
        return true;
    } catch (PDOException $exception){
        // Log en cas d'erreur d'insertion
        error_log('Request error: '.$exception->getMessage());
        return false;
    }
}
