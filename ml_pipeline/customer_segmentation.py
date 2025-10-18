"""
Customer Segmentation ML Pipeline
End-to-end pipeline for customer segmentation using K-Means clustering
"""

import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.cluster import KMeans
from sklearn.decomposition import PCA
import pickle
import json
import os
from datetime import datetime

class CustomerSegmentationPipeline:
    """
    Complete pipeline for customer segmentation
    """

    def __init__(self, n_clusters=4):
        self.n_clusters = n_clusters
        self.scaler = StandardScaler()
        self.kmeans = KMeans(n_clusters=n_clusters, random_state=42, n_init=10)
        self.pca = PCA(n_components=2)
        self.label_encoders = {}
        self.feature_names = []
        self.cluster_profiles = {}

    def load_data(self, filepath):
        """Load data from CSV"""
        print(f"Loading data from {filepath}...")
        self.df = pd.read_csv(filepath)
        print(f"Data loaded: {self.df.shape[0]} rows, {self.df.shape[1]} columns")
        return self.df

    def preprocess_data(self, df=None):
        """Preprocess data for clustering"""
        if df is None:
            df = self.df.copy()
        else:
            df = df.copy()

        print("Preprocessing data...")

        # Identify categorical and numerical columns
        categorical_cols = df.select_dtypes(include=['object']).columns.tolist()
        numerical_cols = df.select_dtypes(include=['int64', 'float64']).columns.tolist()

        # Remove target if exists
        if 'deposit' in categorical_cols:
            categorical_cols.remove('deposit')

        # Encode categorical variables
        for col in categorical_cols:
            if col not in self.label_encoders:
                self.label_encoders[col] = LabelEncoder()
                df[col] = self.label_encoders[col].fit_transform(df[col].astype(str))
            else:
                # Handle unseen labels
                df[col] = df[col].apply(lambda x: x if x in self.label_encoders[col].classes_ else 'unknown')
                df[col] = self.label_encoders[col].transform(df[col].astype(str))

        # Select features for clustering
        feature_cols = categorical_cols + numerical_cols
        if 'deposit' in feature_cols:
            feature_cols.remove('deposit')

        self.feature_names = feature_cols
        X = df[feature_cols]

        print(f"Features selected: {len(feature_cols)}")
        return X

    def train(self, X):
        """Train the segmentation model"""
        print("Training segmentation model...")

        # Scale features
        X_scaled = self.scaler.fit_transform(X)

        # Perform clustering
        self.kmeans.fit(X_scaled)

        # Fit PCA for visualization
        self.pca.fit(X_scaled)

        # Generate cluster profiles
        clusters = self.kmeans.predict(X_scaled)
        self._generate_cluster_profiles(X, clusters)

        print(f"Model trained successfully with {self.n_clusters} clusters")
        print(f"Cluster distribution: {np.bincount(clusters)}")

        return clusters

    def predict(self, X):
        """Predict cluster for new data"""
        X_scaled = self.scaler.transform(X)
        clusters = self.kmeans.predict(X_scaled)
        return clusters

    def _generate_cluster_profiles(self, X, clusters):
        """Generate statistical profiles for each cluster"""
        df_with_clusters = X.copy()
        df_with_clusters['cluster'] = clusters

        for cluster_id in range(self.n_clusters):
            cluster_data = df_with_clusters[df_with_clusters['cluster'] == cluster_id]

            profile = {
                'cluster_id': int(cluster_id),
                'size': int(len(cluster_data)),
                'percentage': float(len(cluster_data) / len(df_with_clusters) * 100),
                'features': {}
            }

            # Calculate statistics for each feature
            for col in X.columns:
                profile['features'][col] = {
                    'mean': float(cluster_data[col].mean()),
                    'median': float(cluster_data[col].median()),
                    'std': float(cluster_data[col].std()),
                    'min': float(cluster_data[col].min()),
                    'max': float(cluster_data[col].max())
                }

            self.cluster_profiles[cluster_id] = profile

        print("Cluster profiles generated")

    def get_pca_coordinates(self, X):
        """Get PCA coordinates for visualization"""
        X_scaled = self.scaler.transform(X)
        pca_coords = self.pca.transform(X_scaled)
        return pca_coords

    def save_model(self, model_dir='../models'):
        """Save the trained model and preprocessing objects"""
        print(f"Saving model to {model_dir}...")

        os.makedirs(model_dir, exist_ok=True)

        # Save model components
        model_data = {
            'kmeans': self.kmeans,
            'scaler': self.scaler,
            'pca': self.pca,
            'label_encoders': self.label_encoders,
            'feature_names': self.feature_names,
            'n_clusters': self.n_clusters,
            'cluster_profiles': self.cluster_profiles,
            'trained_at': datetime.now().isoformat()
        }

        with open(f'{model_dir}/segmentation_model.pkl', 'wb') as f:
            pickle.dump(model_data, f)

        # Save cluster profiles as JSON
        with open(f'{model_dir}/cluster_profiles.json', 'w') as f:
            json.dump(self.cluster_profiles, f, indent=2)

        print("Model saved successfully")

    def load_model(self, model_dir='../models'):
        """Load a trained model"""
        print(f"Loading model from {model_dir}...")

        with open(f'{model_dir}/segmentation_model.pkl', 'rb') as f:
            model_data = pickle.load(f)

        self.kmeans = model_data['kmeans']
        self.scaler = model_data['scaler']
        self.pca = model_data['pca']
        self.label_encoders = model_data['label_encoders']
        self.feature_names = model_data['feature_names']
        self.n_clusters = model_data['n_clusters']
        self.cluster_profiles = model_data['cluster_profiles']

        print("Model loaded successfully")
        return model_data

def main():
    """Main execution pipeline"""
    print("="*60)
    print("Customer Segmentation Pipeline - Bank Marketing Data")
    print("="*60)

    # Initialize pipeline
    pipeline = CustomerSegmentationPipeline(n_clusters=4)

    # Load data
    data = pipeline.load_data('../bank.csv')

    # Display basic info
    print("\nData Info:")
    print(data.info())
    print("\nFirst few rows:")
    print(data.head())

    # Preprocess
    X = pipeline.preprocess_data(data)

    # Train model
    clusters = pipeline.train(X)

    # Add cluster assignments to original data
    data['cluster'] = clusters

    # Get PCA coordinates for visualization
    pca_coords = pipeline.get_pca_coordinates(X)
    data['pca_1'] = pca_coords[:, 0]
    data['pca_2'] = pca_coords[:, 1]

    # Save segmented data
    output_file = '../models/segmented_customers.csv'
    data.to_csv(output_file, index=False)
    print(f"\nSegmented data saved to {output_file}")

    # Display cluster profiles
    print("\n" + "="*60)
    print("CLUSTER PROFILES")
    print("="*60)
    for cluster_id, profile in pipeline.cluster_profiles.items():
        print(f"\nCluster {cluster_id}:")
        print(f"  Size: {profile['size']} customers ({profile['percentage']:.2f}%)")
        print(f"  Top characteristics:")
        # Show top 5 features by mean value
        features_sorted = sorted(
            profile['features'].items(),
            key=lambda x: abs(x[1]['mean']),
            reverse=True
        )[:5]
        for feat_name, feat_stats in features_sorted:
            print(f"    - {feat_name}: mean={feat_stats['mean']:.2f}, std={feat_stats['std']:.2f}")

    # Save model
    pipeline.save_model('../models')

    print("\n" + "="*60)
    print("Pipeline completed successfully!")
    print("="*60)

if __name__ == "__main__":
    main()
