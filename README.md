# Projet-A3-NaviresAIS
Projet réalisé en 3ème année à l’ISEN Brest. Analyse, nettoyage et visualisation de données AIS de navires dans le golfe du Mexique. Développement d’une application web avec intégration de modèles d’IA (clustering, classification, régression) pour prédire le type et la trajectoire des navires. Travail en trinôme.

-----------------------------
1. DESCRIPTION DE LA PARTIE WEB( La partie finale)
-----------------------------

Cette application web est une plateforme conçue pour le suivi de navires via leurs données AIS. Le projet intègre une base de données MySQL pour le stockage, un backend en PHP pour la logique métier, et un frontend en HTML/CSS/JavaScript pour l'interaction avec l'utilisateur.

Les fonctionnalités principales incluent :
- L'ajout de nouvelles données de navires via un formulaire.
- La visualisation de l'ensemble de la flotte sur une carte interactive.
- L'analyse du comportement des navires via un modèle clusters.py qui attribue un "cluster" à chaque navire.

-----------------------------
2. STRUCTURE DES FICHIERS
-----------------------------

'''/projet/ (racine du projet)
|
+-- /css/
|   +-- style_clusters.css   (Feuille de style pour la visualisation des clusters)
|   +-- style_fin.css        (Feuille de style principale)
|
+-- /html/
|   +-- ajout.html           (Formulaire d'ajout de données)
|   +-- index.html           (Page d'accueil)
|   +-- visualisation.html   (Page de visualisation de la flotte)
|
+-- /images/
|   (Dossier contenant les images et icônes de l'application)
|
+-- /js/
|   +-- script.js            (Scripts généraux pour les pages HTML)
|   +-- script_prediction.js (Script pour la fonctionnalité de prédiction côté client)
|   +-- visualisation.js     (Script pour la gestion de la carte interactive)
|
+-- /php/
|   +-- constants.php        (Fichier pour les constantes, ex: identifiants BDD)
|   +-- database.php         (Script pour la connexion à la base de données)
|   +-- enregistrer-bateau.php (Script de traitement du formulaire d'ajout)
|   +-- prediction.php       (Script PHP principal pour gérer les appels de prédiction)
|   +-- prediction_cluster.php (Script PHP pour l'analyse des clusters)
|   +-- prediction_type.php  (Script PHP pour la prédiction de type)
|   +-- visualisation.php    (Backend pour récupérer les données des navires)
|
+-- /python/
|   +-- clusters.py          (Script du modèle d'IA pour le clustering)
|   +-- modele_trajectoire.py(Script du modèle d'IA pour les trajectoires)
|   +-- prediction_traj.py   (Script de prédiction de trajectoire)
|   +-- type.py              (Script Python lié à la classification des types)
|
+-- README.txt
+-- ScriptSQL.txt             (Ce fichier)
'''

-----------------------------
3. PRÉREQUIS D'INSTALLATION
-----------------------------
- Serveur web 
- Serveur de base de données MySQL
- Extension PHP `PDO_MySQL` activée
- Python 3 (pour l'exécution du véritable script d'IA)
-----------------------------
4. GUIDE D'INSTALLATION ET DE CONFIGURATION BDD
-----------------------------

Suivez ces étapes dans l'ordre pour une installation réussie.

1.  **COPIER LES FICHIERS DU PROJET**
    - Uploadez l'ensemble des dossiers et fichiers du projet sur votre serveur web.

2.  **CRÉER ET ALIMENTER LA BASE DE DONNÉES**
    - À l'aide de phpMyAdmin, créez une base de données vide.
    - Copier coller le fichier texte SciptSQL.txt fourni avec le projet dans votre terminal SQL de votre base de données. Ce script créera toutes les tables nécessaires (`bateau`, `releve_ais`, `statut_navigation`).

-----------------------------
5. ACCÈS À L'APPLICATION
-----------------------------

Une fois la configuration terminée, l'application est accessible via l'URL principale :

`http://[adresse-de-votre-serveur]/chemin/vers/le/projet/html/accueil.html`

Depuis la page d'accueil, vous pourrez naviguer vers les différentes fonctionnalités, notamment la page de visualisation de la flotte.


