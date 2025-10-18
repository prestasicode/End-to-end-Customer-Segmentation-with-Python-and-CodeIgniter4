"""
Prediction API for Customer Segmentation
Provides simple interface for model predictions
"""

import pickle
import pandas as pd
import numpy as np
import json
import sys

class SegmentationPredictor:
    """Wrapper for making predictions"""

    def __init__(self, model_path='../models/segmentation_model.pkl'):
        self.model_path = model_path
        self.load_model()

    def load_model(self):
        """Load the trained model"""
        try:
            with open(self.model_path, 'rb') as f:
                model_data = pickle.load(f)

            self.kmeans = model_data['kmeans']
            self.scaler = model_data['scaler']
            self.pca = model_data['pca']
            self.label_encoders = model_data['label_encoders']
            self.feature_names = model_data['feature_names']
            self.n_clusters = model_data['n_clusters']
            self.cluster_profiles = model_data['cluster_profiles']

        except Exception as e:
            raise Exception(f"Error loading model: {str(e)}")

    def preprocess_input(self, data_dict):
        """Preprocess input data"""
        # Create DataFrame from input
        df = pd.DataFrame([data_dict])

        # Encode categorical variables
        for col, encoder in self.label_encoders.items():
            if col in df.columns:
                # Handle unseen labels
                if df[col].iloc[0] not in encoder.classes_:
                    df[col] = encoder.classes_[0]  # Use first class as default
                df[col] = encoder.transform(df[col].astype(str))

        # Ensure all required features are present
        for feat in self.feature_names:
            if feat not in df.columns:
                df[feat] = 0

        # Select features in correct order
        X = df[self.feature_names]

        return X

    def predict(self, data_dict):
        """Make prediction for single customer"""
        try:
            # Preprocess
            X = self.preprocess_input(data_dict)

            # Scale
            X_scaled = self.scaler.transform(X)

            # Predict cluster
            cluster = int(self.kmeans.predict(X_scaled)[0])

            # Get PCA coordinates
            pca_coords = self.pca.transform(X_scaled)[0]

            # Get distance to cluster center
            distance = float(self.kmeans.transform(X_scaled)[0][cluster])

            # Prepare result
            result = {
                'cluster': cluster,
                'cluster_profile': self.cluster_profiles[cluster],
                'pca_coordinates': {
                    'x': float(pca_coords[0]),
                    'y': float(pca_coords[1])
                },
                'distance_to_center': distance,
                'confidence': float(1 / (1 + distance))  # Simple confidence measure
            }

            return result

        except Exception as e:
            raise Exception(f"Prediction error: {str(e)}")

    def predict_batch(self, data_list):
        """Make predictions for multiple customers"""
        results = []
        for data in data_list:
            try:
                result = self.predict(data)
                results.append(result)
            except Exception as e:
                results.append({'error': str(e)})

        return results

def main():
    """CLI interface for predictions"""
    if len(sys.argv) < 2:
        print("Usage: python predict_api.py <json_input>")
        sys.exit(1)

    # Parse input JSON
    try:
        input_data = json.loads(sys.argv[1])
    except:
        print("Error: Invalid JSON input")
        sys.exit(1)

    # Initialize predictor
    predictor = SegmentationPredictor()

    # Make prediction
    if isinstance(input_data, list):
        result = predictor.predict_batch(input_data)
    else:
        result = predictor.predict(input_data)

    # Output result as JSON
    print(json.dumps(result, indent=2))

if __name__ == "__main__":
    main()
