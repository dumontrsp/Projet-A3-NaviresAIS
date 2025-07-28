'use strict';

// ==========================
// VARIABLES GLOBALES
// ==========================
let map;                // Objet carte Mapbox
let markers = [];       // Liste des marqueurs actifs sur la carte
let donneesNavires = []; // Données des navires récupérées depuis le serveur

// ==========================
// INITIALISATION AU CHARGEMENT
// ==========================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page chargée, initialisation...');
    chargerDonnees(); // Charge les données des navires dès que la page est prête
});

// ==========================
// CHARGEMENT DES DONNÉES
// ==========================
function chargerDonnees() {
    console.log('Chargement des données...');

    fetch('../php/visualisation.php')
      .then(response => {
          if (!response.ok) throw new Error('Erreur réseau: ' + response.status);
          return response.json(); // Transforme la réponse en JSON
      })
      .then(data => {
          console.log('Données récupérées:', data);
          donneesNavires = data; // Sauvegarde les données
          initialiserInterface(); // Lance l'initialisation de la carte et du tableau
      })
      .catch(error => {
          console.error('Erreur de chargement des données :', error);
          alert('Impossible de charger les données des navires: ' + error.message);
      });
}

// ==========================
// INITIALISATION DE L'INTERFACE
// ==========================
function initialiserInterface() {
    console.log('Initialisation de l\'interface avec', donneesNavires.length, 'navires');
    remplirTableau(donneesNavires);  // Remplit le tableau HTML
    initialiserCarte(donneesNavires); // Affiche les navires sur la carte
}

// ==========================
// AFFICHAGE DU TABLEAU
// ==========================
function remplirTableau(donnees) {
    console.log('Remplissage du tableau...');
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = ''; // Réinitialise le tableau

    donnees.forEach(navire => {
        const tr = document.createElement('tr');

        // --- Colonne avec le bouton radio ---
        const radioTd = document.createElement('td');
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = 'selection';
        radio.value = navire.MMSI;
        radio.addEventListener('change', function() {
            document.getElementById('selectedMMSI').value = this.value;
            console.log('Navire sélectionné:', this.value);
        });
        radioTd.appendChild(radio);
        tr.appendChild(radioTd);

        // --- Autres colonnes de données ---
        const champs = [
            navire.MMSI,
            navire.Horodatage,
            navire.Latitude,
            navire.Longitude,
            navire.SOG,
            navire.COG,
            navire.Cap,
            navire.Nom,
            navire.Etat,
            navire.Longueur,
            navire.Largeur,
            navire.Tirant_eau
        ];

        champs.forEach(valeur => {
            const td = document.createElement('td');
            td.textContent = valeur !== null && valeur !== undefined ? valeur : '-';
            tr.appendChild(td);
        });

        tbody.appendChild(tr);
    });

    console.log('Tableau rempli avec', donnees.length, 'navires');
}

// ==========================
// INITIALISATION DE LA CARTE
// ==========================
function initialiserCarte(donnees) {
    console.log('Initialisation de la carte Mapbox...');
    
    mapboxgl.accessToken = 'pk.eyJ1IjoiYW1hbmRlayIsImEiOiJjbWMxdzZ2eGMwMjIwMmxxczlqM2dmdTBzIn0.QA64sFH_i5akGsFxx1Iz4g';

    try {
        map = new mapboxgl.Map({
            container: 'map-container',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [2.3522, 48.8566], // Position par défaut (Paris)
            zoom: 5
        });

        map.on('load', () => {
            console.log('Carte chargée, ajout des marqueurs...');
            ajouterMarqueurs(donnees);
        });

        map.on('error', (e) => console.error('Erreur Mapbox:', e));

    } catch (error) {
        console.error('Erreur lors de l\'initialisation de la carte:', error);
        document.getElementById('map-container').innerHTML = '<p>Erreur lors du chargement de la carte.</p>';
    }
}

// ==========================
// AJOUT DES MARQUEURS
// ==========================
function ajouterMarqueurs(donnees) {
    // Nettoyage des anciens marqueurs
    markers.forEach(marker => marker.remove());
    markers = [];

    const bounds = new mapboxgl.LngLatBounds(); // Pour ajuster la vue automatiquement
    let marqueursAjoutes = 0;

    console.log("--- Début de l'ajout des marqueurs ---");

    donnees.forEach((navire, index) => {
        console.log(`Bateau #${index + 1} (MMSI: ${navire.MMSI}) :`);
        console.log(`  -> Données brutes: Latitude='${navire.Latitude}', Longitude='${navire.Longitude}'`);

        const lat = parseFloat(navire.Latitude);
        const lng = parseFloat(navire.Longitude);
        console.log(`  -> Données converties: lat=${lat}, lng=${lng}`);

        if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
            console.log("  -> ✅ Coordonnées valides. Création du marqueur.");

            const el = document.createElement('div');
            el.className = 'marker';
            el.title = navire.Nom || 'Navire sans nom';

            const popupContent = `
                <div style="font-family: Montserrat, sans-serif;">
                    <h3>${navire.Nom || 'Navire sans nom'}</h3>
                    <p><strong>MMSI:</strong> ${navire.MMSI}</p>
                    <p><strong>Vitesse:</strong> ${navire.SOG || 0} nœuds</p>
                    <p><strong>Position:</strong> ${lat.toFixed(4)}, ${lng.toFixed(4)}</p>
                </div>
            `;

            const marker = new mapboxgl.Marker(el)
                .setLngLat([lng, lat])
                .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML(popupContent))
                .addTo(map);

            markers.push(marker);
            bounds.extend([lng, lat]);
            marqueursAjoutes++;
        } else {
            console.log("  -> ❌ Coordonnées invalides ou nulles. Marqueur ignoré.");
        }
    });

    console.log(`--- Fin de l'ajout des marqueurs. Total ajoutés : ${marqueursAjoutes} ---`);

    if (marqueursAjoutes > 0) {
        map.fitBounds(bounds, { padding: 50, maxZoom: 12 });
    }
}

// ==========================
// BOUTONS DE CONTRÔLE
// ==========================
function filtrerDonnees() {
    console.log('Filtrage des données...');
    alert('Fonction de filtrage à implémenter'); // À développer
}

function actualiserDonnees() {
    console.log('Actualisation des données...');
    chargerDonnees(); // Recharge les données à la demande
}

// ==========================
// VALIDATION DU FORMULAIRE DE PRÉDICTION
// ==========================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('predictionForm');
    const mmsiInput = document.getElementById('selectedMMSI');

    if (form) {
        form.addEventListener('submit', function(event) {
            if (!mmsiInput.value) {
                event.preventDefault(); // Annule l’envoi
                alert('Veuillez sélectionner un navire avant de lancer la prédiction.');
            }
        });
    }
});
