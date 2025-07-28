#!/usr/bin/python3
"""
Script for predicting a single future position of a vessel.
Loads a pre-trained model and outputs the prediction as JSON.
"""

import argparse
import joblib
import pandas as pd
import json

def predict_position(args):
    """Loads the model and predicts the next position."""
    
    # --- PARAMÈTRES ---
    # Le nom du modèle doit correspondre à celui généré par votre script d'entraînement
    model_filename = f"modele_trajectoire_{args.horizon}min.joblib"
    
    try:
        # Charger le modèle pré-entraîné
        model = joblib.load(model_filename)
    except FileNotFoundError:
        print(json.dumps({"error": f"Model file not found: {model_filename}"}))
        return

    # Créer un DataFrame avec les données d'entrée
    # L'ordre des colonnes doit être EXACTEMENT le même que celui utilisé pour l'entraînement
    features = ['LAT', 'LON', 'SOG', 'COG', 'Heading']
    input_data = pd.DataFrame([[
        args.lat, args.lon, args.sog, args.cog, args.heading
    ]], columns=features)
    
    # Faire la prédiction
    predicted_coords = model.predict(input_data)
    
    # Formater la sortie en JSON
    result = {
        "LAT_pred": predicted_coords[0][0],
        "LON_pred": predicted_coords[0][1]
    }
    print(json.dumps(result))

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument('--lat', type=float, required=True, help='Current Latitude')
    parser.add_argument('--lon', type=float, required=True, help='Current Longitude')
    parser.add_argument('--sog', type=float, required=True, help='Current Speed Over Ground')
    parser.add_argument('--cog', type=float, required=True, help='Current Course Over Ground')
    parser.add_argument('--heading', type=float, required=True, help='Current True Heading')
    # L'horizon doit correspondre au modèle que vous souhaitez utiliser
    parser.add_argument('--horizon', type=int, default=5, help='Prediction horizon in minutes')
    
    args = parser.parse_args()
    predict_position(args)