<?php
/**
 * Fichier : prediction.php
 * Rôle : Affiche le résultat d'une prédiction (type ou trajectoire) pour un navire donné.
 * NOTE : Ce script SIMULE la réponse des modèles Python pour fonctionner sur des serveurs où shell_exec est désactivé.
 */

// --- 1. Inclusion des constantes et connexion à la base de données ---
require_once('constants.php'); // Inclusion des constantes de connexion à la BDD

try {
    // Construction du DSN avec charset UTF-8
    $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8";
    // Création de l'objet PDO pour la connexion à la base
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
    // Configuration pour lever une exception en cas d'erreur SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En cas d'erreur, on stoppe le script avec un message explicite
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// --- 2. Validation et récupération des données du formulaire ---
if (!isset($_POST['mmsi']) || !isset($_POST['prediction_type'])) {
    // Si les données attendues ne sont pas présentes, on arrête l'exécution
    die("Erreur : MMSI ou type de prédiction manquant.");
}
$mmsi = $_POST['mmsi']; // MMSI du navire à prédire
$predictionType = $_POST['prediction_type']; // Type de prédiction souhaité

// --- 3. Récupération des dernières données du navire ---
try {
    // Préparation de la requête pour récupérer les données du navire et son dernier relevé AIS
    $stmt = $pdo->prepare(
        "SELECT 
            b.MMSI, b.VesselName, b.Lenght as length, b.Width as width, b.Draft as draft,
            r.BaseDateTime, r.Latitude as latitude, r.Longitude as longitude,
            r.SOG as sog, r.COG as cog, r.Heading as heading, r.id_statut as status
        FROM bateau b
        JOIN releve_ais r ON b.id_bateau = r.id_bateau
        WHERE b.MMSI = :mmsi
        ORDER BY r.BaseDateTime DESC
        LIMIT 1"
    );
    $stmt->execute(['mmsi' => $mmsi]);
    $boatData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si aucune donnée n'est trouvée, on arrête avec un message
    if (!$boatData) {
        die("Aucune donnée trouvée pour le MMSI : " . htmlspecialchars($mmsi));
    }
} catch (PDOException $e) {
    // Gestion des erreurs SQL lors de la récupération des données
    die("Erreur lors de la récupération des données du navire : " . $e->getMessage());
}

// --- 4. Logique de prédiction (avec SIMULATION) ---
// Titre et introduction du résultat de la prédiction
$output = "<h1>Résultat de la prédiction pour le bateau " . htmlspecialchars($boatData['VesselName'] ?? $mmsi) . "</h1>";

if ($predictionType == 'type') {
    // Prédiction simulée du type de bateau
    $output .= "<h2>Prédiction du type de bateau (Simulation)</h2>";
    $models = ['knn', 'svm', 'rf', 'mlp']; // Liste des modèles simulés
    $predictions = [];

    // Pour chaque modèle, on génère un ID de type aléatoire (simulation)
    foreach ($models as $model) {
        $predictions[$model] = rand(10, 70);
    }
    
    // Construction d'un tableau HTML pour afficher les résultats simulés
    $output .= "<table border='1'><thead><tr><th>Modèle</th><th>Type Prédit (ID)</th></tr></thead><tbody>";
    foreach ($predictions as $model => $pred) {
        $output .= "<tr><td>" . strtoupper($model) . "</td><td>" . htmlspecialchars($pred) . "</td></tr>";
    }
    $output .= "</tbody></table>";

} elseif ($predictionType == 'trajectoire') {
    // Prédiction simulée de la trajectoire
    $output .= "<h2>Prédiction de la trajectoire (Simulation)</h2>";

    // Calcul de fausses coordonnées prédites à partir de la position actuelle avec un petit décalage aléatoire
    $prediction_data = [
        "LAT_pred" => $boatData['latitude'] + (mt_rand(-50, 50) / 1000),
        "LON_pred" => $boatData['longitude'] + (mt_rand(-50, 50) / 1000)
    ];
    
    // Affichage d'une div qui contiendra la carte Plotly
    $output .= "<div id='map_prediction' style='width: 100%; height: 600px;'></div>";
    // Génération du script Plotly avec les données actuelles et prédites
    $output .= generate_plotly_script($boatData, $prediction_data);

} else {
    // Cas où le type de prédiction est inconnu
    $output .= "<p>Type de prédiction non reconnu.</p>";
}

// --- 5. Fonction pour générer le script de la carte Plotly ---
function generate_plotly_script($current, $predicted) {
    $lat_current = $current['latitude'];
    $lon_current = $current['longitude'];
    $lat_pred = $predicted['LAT_pred'];
    $lon_pred = $predicted['LON_pred'];

    // Retourne le script JavaScript complet qui utilise Plotly pour afficher la carte avec positions et trajectoire
    return "
    <script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
    <script>
        const trace_current = {
            type: 'scattermapbox', mode: 'markers', name: 'Position Actuelle',
            lat: [$lat_current], lon: [$lon_current],
            marker: { color: 'blue', size: 15 },
            text: '<b>Départ</b><br>Vitesse: {$current['sog']} nœuds'
        };

        const trace_predicted = {
            type: 'scattermapbox', mode: 'markers', name: 'Position Prédite',
            lat: [$lat_pred], lon: [$lon_pred],
            marker: { color: 'red', size: 15 },
            text: '<b>Arrivée Prédite</b>'
        };
        
        const trace_line = {
            type: 'scattermapbox', mode: 'lines', name: 'Trajectoire Prédite',
            lat: [$lat_current, $lat_pred], lon: [$lon_current, $lon_pred],
            line: { color: 'red', width: 2 }
        };

        const layout = {
            mapbox: {
                style: 'open-street-map',
                center: { lat: $lat_current, lon: $lon_current },
                zoom: 10
            },
            title: 'Prédiction de Trajectoire (Simulation)',
            margin: { t: 40, b: 0, l: 0, r: 0 },
            showlegend: false
        };

        Plotly.newPlot('map_prediction', [trace_current, trace_predicted, trace_line], layout);
    </script>
    ";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat de la Prédiction</title>
</head>
<body>
    <main class="main-container">
        <?php echo $output; // Affichage du contenu principal ?>
    </main>
</body>
</html>
