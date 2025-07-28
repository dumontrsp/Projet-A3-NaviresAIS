<?php
// =================================================================
// PARTIE 1 : LOGIQUE PHP
// =================================================================

// --- 1. On charge les constantes de la base de données ---
// Ce fichier doit définir DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD.
require_once('constants.php');

// --- 2. Préparation de la connexion avec PDO ---
// Création de la chaîne DSN (Data Source Name) pour PDO
$dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
// Options pour PDO : gestion des erreurs et mode de récupération des résultats
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Exceptions en cas d'erreur SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Résultats sous forme de tableau associatif
];

// On initialise un tableau vide pour stocker les résultats finaux
$resultat_final = [];
try {
    // --- 3. On établit la connexion à la base de données avec PDO ---
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
    
    // =================================================================
    //  Requête SQL pour récupérer les infos des bateaux avec leur dernier relevé AIS
    // =================================================================
    $sql = "
        SELECT
            b.MMSI, b.VesselName, b.Lenght, b.Width, b.Draft, b.id_type,
            ra.Latitude AS LAT, ra.Longitude AS LON,
            ra.BaseDateTime, ra.SOG, ra.COG, ra.Heading, ra.id_statut
        FROM
            bateau b
        INNER JOIN (
            -- Sous-requête pour récupérer la dernière date de relevé par bateau
            SELECT id_bateau, MAX(BaseDateTime) AS max_date
            FROM releve_ais GROUP BY id_bateau
        ) last_releve ON b.id_bateau = last_releve.id_bateau
        INNER JOIN releve_ais ra ON last_releve.id_bateau = ra.id_bateau AND last_releve.max_date = ra.BaseDateTime;
    ";
    
    // Exécution directe de la requête SQL
    $stmt = $pdo->query($sql);
    // Récupération de tous les résultats sous forme de tableau associatif
    $all_boats_data = $stmt->fetchAll();

    // =================================================================
    //   Appel du script Python clusters.py pour chaque bateau
    // =================================================================
    if ($all_boats_data) {
        // Chemin vers le script Python (à adapter selon l'emplacement final)
        $script_path = '../python/clusters.py';

        // Parcours de chaque bateau et de son dernier relevé AIS
        foreach ($all_boats_data as $boat_data) {
            // Construction sécurisée de la commande shell avec tous les arguments
            $command = 'python3 ' . escapeshellarg($script_path) .
                ' --sog ' . escapeshellarg($boat_data['SOG']) .
                ' --cog ' . escapeshellarg($boat_data['COG']) .
                ' --latitude ' . escapeshellarg($boat_data['LAT']) .
                ' --longitude ' . escapeshellarg($boat_data['LON']) .
                ' --heading ' . escapeshellarg($boat_data['Heading']) .
                ' --length ' . escapeshellarg($boat_data['Lenght']) .
                ' --width ' . escapeshellarg($boat_data['Width']) .
                ' --draft ' . escapeshellarg($boat_data['Draft']) .
                ' --status ' . escapeshellarg($boat_data['id_statut']) .
                ' --time ' . escapeshellarg($boat_data['BaseDateTime']);
            
            // Exécution de la commande, capture de la sortie et du code retour
            exec($command . ' 2>&1', $output, $return_code);

            // Si le script Python s'est bien exécuté et a retourné quelque chose
            if ($return_code === 0 && !empty($output)) {
                // Ajout du numéro de cluster prédit (première ligne de la sortie)
                $boat_data['cluster'] = (int)$output[0];
                // Ajout de ce bateau avec cluster dans le résultat final
                $resultat_final[] = $boat_data;
            }
            // Réinitialisation du tableau $output pour la prochaine itération
            $output = [];
        }
    }

} catch (\PDOException $e) {
    // En cas d'erreur SQL, on stocke un tableau avec une clé "error" pour l'afficher plus tard
    $resultat_final = ['error' => "Erreur de base de données : " . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="shortcut icon" href="../../images/logo_track_ocean.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projet Maritime</title>
    
    <!-- Feuille de style personnalisée -->
    <link rel="stylesheet" href="../css/style_clusters.css">
    
    <!-- Import Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    
    <style>
        /* Styles pour la légende colorée affichée sur la carte */
        .custom-legend {
            position: absolute; top: 20px; right: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 15px; border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
            list-style: none; margin: 0; z-index: 10;
        }
        .legend-item {
            display: flex; align-items: center;
            margin-bottom: 8px; font-family: 'Inter', sans-serif;
            font-weight: 600; font-size: 14px;
        }
        .legend-item:last-child { margin-bottom: 0; }
        .legend-color-swatch {
            width: 15px; height: 15px; border-radius: 50%;
            margin-right: 10px; border: 1px solid rgba(0,0,0,0.1);
        }
        /* Couleurs des clusters */
        .cluster-color-0, .cluster-color-1 { background-color: #28A745; }
        .cluster-color-2 { background-color: #00A6D6; }
        .cluster-color-3 { background-color: #FFC107; }
        .cluster-color-4 { background-color: #DC3545; }
        .cluster-color-5 { background-color: #6f42c1; }
    </style>
</head>
<body>
    <!-- En-tête principal avec navigation -->
    <header class="main-header">
        <div class="logo">Projet Maritime Web</div>
        <nav class="nav-links"><a href="../projet/html/acceuil.html">Accueil</a></nav>
        <nav class="nav-links"><a href="../projet/html/ajout.html">Ajout de données</a></nav>
        <nav class="nav-links"><a href="../projet/html/visualisaion.html">Visualisation</a></nav>
    </header>

    <main class="container">
        <h1>Analyse de Comportement de la Flotte</h1>
        <p>Cette page affiche le résultat de l'analyse comportementale pour l'ensemble des navires. Le cluster est prédit par le fichier python clusters.py</p>
        
        <!-- Conteneur de la carte -->
        <div id="map-wrapper" class="card" style="position: relative; height: 600px; margin-top: 32px; padding: 0; overflow: hidden;">
            <!-- Légende personnalisée des clusters -->
            <ul class="custom-legend">
                <li class="legend-item"><span class="legend-color-swatch cluster-color-1"></span> Cluster 1</li>
                <li class="legend-item"><span class="legend-color-swatch cluster-color-2"></span> Cluster 2</li>
                <li class="legend-item"><span class="legend-color-swatch cluster-color-3"></span> Cluster 3</li>
                <li class="legend-item"><span class="legend-color-swatch cluster-color-4"></span> Cluster 4</li>
                <li class="legend-item"><span class="legend-color-swatch cluster-color-5"></span> Cluster 5</li>
            </ul>
        </div>
    </main>

    <footer class="main-footer">
        <!-- Affiche l'année actuelle dynamiquement -->
        <p>&copy; <?php echo date('Y'); ?> Projet Maritime - ISEN</p>
    </footer>

    <!-- Librairie Plotly pour les graphiques et cartes -->
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

    <script>
        // On récupère les données PHP encodées en JSON dans JS
        const result_data = <?php echo json_encode($resultat_final); ?>;
        const mapDiv = document.getElementById('map-wrapper');

        // Si le serveur a retourné une erreur technique
        if (result_data && result_data.error) {
            // On affiche un message d'erreur lisible à l'utilisateur
            mapDiv.innerHTML = `<div style="padding: 48px; text-align:center;">
                <h2>Erreur Technique</h2>
                <p style="color:red; white-space: pre-wrap;">${result_data.error}</p>
            </div>`;
        
        // Sinon si on a des données de bateaux avec clusters
        } else if (result_data && result_data.length > 0) {
            // Couleurs attribuées aux clusters (index = clusterId)
            const clusterColors = ['#000000', '#28A745', '#00A6D6', '#FFC107', '#DC3545', '#6f42c1'];
            // Objet pour regrouper les données par cluster
            const clustersData = {};

            // Parcours de chaque bateau pour organiser par cluster
            result_data.forEach(boat => {
                const clusterId = boat.cluster;
                // Initialisation de la structure pour ce cluster si première occurrence
                if (!clustersData[clusterId]) {
                    clustersData[clusterId] = {
                        type: 'scattermapbox',
                        mode: 'markers',
                        name: 'Cluster ' + clusterId,
                        lat: [],
                        lon: [],
                        text: [],
                        marker: { size: 12, color: clusterColors[clusterId] }
                    };
                }
                // Ajout des coordonnées et texte pour l'infobulle
                clustersData[clusterId].lat.push(boat.LAT);
                clustersData[clusterId].lon.push(boat.LON);
                clustersData[clusterId].text.push(`<b>${boat.VesselName}</b><br>MMSI: ${boat.MMSI}<br>Cluster Prédit: ${clusterId}`);
            });

            // Conversion de l'objet en tableau pour Plotly
            const dataForPlotly = Object.values(clustersData);
            // Configuration de la carte Mapbox dans Plotly
            const layout = {
                mapbox: {
                    style: 'open-street-map',
                    center: { lat: result_data[0].LAT, lon: result_data[0].LON },
                    zoom: 8
                },
                margin: { r: 0, t: 0, b: 0, l: 0 },
                showlegend: false // On cache la légende par défaut car on a notre propre légende HTML
            };
            // Création du graphique/carte dans la div dédiée
            Plotly.newPlot('map-wrapper', dataForPlotly, layout);

        } else {
            // Pas de données trouvées => message d'information utilisateur
            mapDiv.innerHTML = '<div style="padding: 48px; text-align:center;"><h2>Analyse Impossible</h2><p>Aucun navire avec des relevés de position n\'a été trouvé dans la base de données.</p></div>';
        }
    </script>
</body>
</html>
