// =========================
// FICHIER : script.js
// =========================

// Dès que le DOM est chargé, on attend l'événement de soumission du formulaire
document.addEventListener('DOMContentLoaded', function() {
    const boatForm = document.getElementById('add-boat-form'); // Récupère le formulaire via son ID
    if (boatForm) {
        // Ajoute un écouteur sur la soumission du formulaire
        boatForm.addEventListener('submit', handleFormSubmit);
    }
});

/**
 * Fonction qui gère la soumission du formulaire de manière asynchrone (sans recharger la page)
 * @param {Event} event L'événement de soumission du formulaire
 */
async function handleFormSubmit(event) {
    event.preventDefault(); // Empêche le rechargement de la page lors de la soumission

    const form = event.currentTarget; // Référence au formulaire soumis
    const submitButton = form.querySelector('button[type="submit"]'); // Bouton de soumission
    const originalButtonText = submitButton.textContent; // Texte original du bouton

    // Désactive le bouton et indique que l'enregistrement est en cours
    submitButton.disabled = true;
    submitButton.textContent = 'Enregistrement en cours...';

    try {
        // Récupère les données du formulaire dans un objet FormData
        const formData = new FormData(form);

        // Envoie des données en POST au fichier PHP serveur
        const response = await fetch('../php/enregistrer-bateau.php', {
            method: 'POST',
            body: formData,
        });

        // Attente et conversion de la réponse JSON
        const result = await response.json();

        // Si la réponse est OK (code 200–299)
        if (response.ok) {
            alert('Succès : ' + result.message); // Affiche un message de succès
            form.reset(); // Réinitialise le formulaire
        } else {
            alert('Erreur : ' + result.message); // Affiche un message d'erreur du serveur
        }

    } catch (error) {
        // En cas d'erreur réseau ou autre problème inattendu
        console.error('Erreur de communication:', error);
        alert('Impossible de communiquer avec le serveur. Veuillez vérifier votre connexion.');
    } finally {
        // Réactive le bouton et restaure son texte d'origine
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
    }
}
