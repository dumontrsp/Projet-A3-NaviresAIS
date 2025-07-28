<?php
/**
 * Fichier : prediction.php
 * Rôle : Affiche le résultat d'une prédiction (type ou trajectoire) pour un navire donné.
 */

// --- 1. Inclusion des constantes et connexion à la base de données ---
require_once('constants.php'); // Inclusion des paramètres DB

try {
    // Configuration DSN pour PDO avec charset UTF-8
    $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8";
    // Création de l'objet PDO pour la connexion à la BDD
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
    // Activation du mode d'erreur Exception pour PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En cas d'erreur de connexion, on stoppe l'exécution avec un message
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// --- 2. Validation et récupération des données du formulaire ---
// Vérifie que le MMSI et le type de prédiction sont bien passés en POST
if (!isset($_POST['mmsi']) || !isset($_POST['prediction_type'])) {
    die("Erreur : MMSI ou type de prédiction manquant.");
}

$mmsi = $_POST['mmsi'];                   // MMSI du navire cible
$predictionType = $_POST['prediction_type']; // Type de prédiction souhaité (type ou trajectoire)

// --- 3. Récupération des dernières données du navire ---
// On récupère les infos du navire et ses dernières données AIS
try {
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

    // Si aucune donnée trouvée, on arrête le script
    if (!$boatData) {
        die("Aucune donnée trouvée pour le MMSI : " . htmlspecialchars($mmsi));
    }
} catch (PDOException $e) {
    // Gestion d'erreur en cas de problème SQL
    die("Erreur lors de la récupération des données du navire : " . $e->getMessage());
}

// --- 4. Logique de prédiction (avec SIMULATION) ---
$pageTitle = "Résultat de la prédiction"; // Titre par défaut de la page
$output = ""; // Contenu HTML à afficher

if ($predictionType == 'type') {
    // Si on veut prédire le type de navire
    $pageTitle = "Prédiction du type pour " . htmlspecialchars($boatData['VesselName'] ?? $mmsi);
    $output .= "<h2>Prédiction du type de bateau (Simulation)</h2>";

    // Modèles simulés pour la prédiction de type
    $models = ['knn', 'svm', 'rf', 'mlp'];
    $predictions = [];

    // Simulation de prédictions (valeurs aléatoires)
    foreach ($models as $model) {
        $predictions[$model] = rand(10, 70);
    }

    // Construction du tableau HTML pour afficher les résultats
    $output .= "<table class='results-table'><thead><tr><th>Modèle</th><th>Type Prédit (ID)</th></tr></thead><tbody>";
    foreach ($predictions as $model => $pred) {
        $output .= "<tr><td>" . strtoupper($model) . "</td><td>" . htmlspecialchars($pred) . "</td></tr>";
    }
    $output .= "</tbody></table>";

} elseif ($predictionType == 'trajectoire') {
    // Si on veut prédire la trajectoire
    $pageTitle = "Prédiction de trajectoire pour " . htmlspecialchars($boatData['VesselName'] ?? $mmsi);
    $output .= "<h2>Prédiction de la trajectoire (Simulation)</h2>";

    // Simulation de nouvelle position prédite (petit décalage aléatoire)
    $prediction_data = [
        "LAT_pred" => $boatData['latitude'] + (mt_rand(-50, 50) / 1000),
        "LON_pred" => $boatData['longitude'] + (mt_rand(-50, 50) / 1000)
    ];

    // Div qui contiendra la carte Plotly
    $output .= "<div id='map_prediction' style='width: 100%; height: 600px; margin-top: 24px;'></div>";
    // Ajout du script JS pour afficher la carte avec les points actuels et prédits
    $output .= generate_plotly_script($boatData, $prediction_data);

} else {
    // Si le type de prédiction n'est pas reconnu
    $output .= "<p>Type de prédiction non reconnu.</p>";
}

// --- 5. Fonction pour générer le script JS de la carte Plotly ---
function generate_plotly_script($current, $predicted) {
    $lat_current = $current['latitude'];
    $lon_current = $current['longitude'];
    $lat_pred = $predicted['LAT_pred'];
    $lon_pred = $predicted['LON_pred'];

    // Retourne un script JavaScript Plotly pour afficher la position actuelle, la prédiction, et la trajectoire
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
            mapbox: { style: 'open-street-map', center: { lat: $lat_current, lon: $lon_current }, zoom: 10 }, 
            margin: { t: 0, b: 0, l: 0, r: 0 }, 
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
    <link rel="shortcut icon" href="../../images/logo_track_ocean.png" type="image/x-icon">
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
</head>
<body>
    <header class="main-header">
        <div class="logo">Projet Maritime</div>
    </header>

    <main class="container">
        <h1><?php echo $pageTitle; ?></h1>

        <div class="card">
            <?php echo $output; // Affichage du contenu principal (tableau ou carte) ?>
        </div>
    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> - Projet Maritime</p>
    </footer>
</body>
</html>
