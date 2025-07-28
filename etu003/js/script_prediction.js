/**
 * Gère la soumission du formulaire de prédiction.
 * @param {string} type - Le type de prédiction à effectuer ('type' ou 'trajectoire').
 */
function submitPrediction(type) {
    // Vérifie si un bouton radio (un bateau) est bien sélectionné dans le formulaire.
    const selectedBoat = document.querySelector('input[name="mmsi"]:checked');
    
    if (!selectedBoat) {
        // Si aucun bateau n'est sélectionné, affiche une alerte à l'utilisateur.
        alert("Veuillez sélectionner un bateau dans la liste avant de lancer une prédiction.");
        return; // Arrête l'exécution de la fonction.
    }

    // Récupère l'élément de formulaire caché qui stocke le type de prédiction.
    const predictionTypeInput = document.getElementById('prediction_type_input');
    // Met à jour sa valeur avec le type de prédiction demandé ('type' ou 'trajectoire').
    predictionTypeInput.value = type;
    
    // Récupère le formulaire et le soumet.
    // Le formulaire enverra ses données à l'URL définie dans son attribut 'action'
    // et s'ouvrira dans une nouvelle fenêtre (défini par 'target="_blank"').
    document.getElementById('predictionForm').submit();
}