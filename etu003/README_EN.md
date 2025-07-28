# AIS-ShipTracking-WebApp
## 🌐 Available Languages
- [Français](README.md)
- [English](readmeEN_EN.md) (you are here)


Project completed during the 3rd year at ISEN Brest. It involves analysis, cleaning, and visualization of AIS (Automatic Identification System) data from ships in the Gulf of Mexico. Development of a web application with integrated AI models (clustering, classification, regression) to predict ship type and trajectory. Developed as a team of three students.

## 1. WEB COMPONENT DESCRIPTION (Final Version)

This web application is a platform designed to monitor ships using their AIS data. It integrates:
- A **MySQL database** for storage,
- A **PHP back-end** for business logic,
- A **front-end** in HTML/CSS/JavaScript for user interaction.

### Key Features:
- Adding new vessel data via a form.
- Visualizing the entire fleet on an interactive map.
- Analyzing vessel behavior using a Python model (`clusters.py`) that assigns a cluster to each ship.

---

## 2. PROJECT FILE STRUCTURE
```
/project/ (root directory)
|
+-- /css/
| +-- style_clusters.css (CSS for cluster visualization)
| +-- style_fin.css (Main stylesheet)
|
+-- /html/
| +-- ajout.html (Form to add ship data)
| +-- index.html (Landing page)
| +-- visualisation.html (Fleet visualization page)
|
+-- /images/
| (Folder containing application images and icons)
|
+-- /js/
| +-- script.js (General scripts for HTML pages)
| +-- script_prediction.js (Script for client-side prediction)
| +-- visualisation.js (Script for interactive map handling)
|
+-- /php/
| +-- constants.php (Constants, e.g. DB credentials)
| +-- database.php (Database connection logic)
| +-- enregistrer-bateau.php (Form data processing script)
| +-- prediction.php (Main PHP script to handle prediction calls)
| +-- prediction_cluster.php (Cluster prediction logic)
| +-- prediction_type.php (Ship type prediction logic)
| +-- visualisation.php (Retrieves ship data for the front-end)
|
+-- /python/
| +-- clusters.py (AI model script for clustering)
| +-- modele_trajectoire.py (AI model script for trajectory prediction)
| +-- prediction_traj.py (Trajectory prediction script)
| +-- type.py (Python script for ship type classification)
|
+-- README
```
## 3. INSTALLATION REQUIREMENTS

- A web server (e.g., Apache, Nginx)
- MySQL database server
- PHP with `PDO_MySQL` extension enabled
- Python 3 (to run the AI scripts)

---

## 4. INSTALLATION & DATABASE CONFIGURATION GUIDE

Follow these steps in order for a successful setup:

### 1. **Upload the Project Files**
- Upload all project folders and files to your web server.

### 2. **Create and Populate the Database**
- Using **phpMyAdmin** or another SQL client, create a new MySQL database.
- Open the provided `ScriptSQL.txt` file.
- Copy and paste its content into your SQL console to create the necessary tables:  
  `bateau`, `releve_ais`, `statut_navigation`.

---

## 5. ACCESSING THE APPLICATION

Once everything is set up, the application is accessible at:
http://[your-server-address]/path/to/project/html/index.html



