# Customer Segmentation with Machine Learning & CodeIgniter 4

End-to-end customer segmentation system using Machine Learning (K-Means clustering) integrated with CodeIgniter 4 web framework.

## Features

- **Machine Learning Pipeline**: Complete ML pipeline with preprocessing, feature engineering, and K-Means clustering
- **Model Persistence**: Save and load trained models for predictions
- **Web Dashboard**: Interactive dashboard showing cluster statistics and distributions
- **Real-time Predictions**: Predict customer segments for new data
- **Visualization**: 2D PCA visualization of customer clusters
- **Batch Processing**: Upload CSV files for batch predictions
- **RESTful API**: JSON API endpoints for integration

## Architecture

```
MachineLearningwithCodeIgniter/
├── ml_pipeline/              # Machine Learning Pipeline
│   ├── customer_segmentation.py   # Main training pipeline
│   ├── predict_api.py             # Prediction API
│   ├── train_model.sh             # Training script
│   └── requirements.txt           # Python dependencies
├── models/                   # Trained models & data
│   ├── segmentation_model.pkl     # Trained model
│   ├── cluster_profiles.json      # Cluster statistics
│   └── segmented_customers.csv    # Segmented data
├── app/
│   ├── Controllers/
│   │   └── Segmentation.php       # Main controller
│   ├── Models/
│   │   └── CustomerModel.php      # Data model
│   ├── Views/
│   │   └── segmentation/          # Web interface
│   └── Config/
│       └── Routes.php             # Routes configuration
├── public/                   # Web root
└── bank.csv                  # Source data
```

## Requirements

### Backend (PHP)
- PHP 8.1 or higher
- CodeIgniter 4.x
- Composer

### Machine Learning (Python)
- Python 3.8+
- pandas
- numpy
- scikit-learn
- matplotlib
- seaborn

## Installation

### 1. Install PHP Dependencies

```bash
composer install
```

### 2. Install Python Dependencies

```bash
cd ml_pipeline
pip3 install -r requirements.txt
```

### 3. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and configure your settings.

### 4. Train the Model

```bash
cd ml_pipeline
bash train_model.sh
```

This will:
- Load the bank.csv data
- Preprocess features
- Train K-Means clustering model
- Generate cluster profiles
- Save model and segmented data

## Usage

### Start the Web Server

```bash
php spark serve
```

Visit: `http://localhost:8080`

### Web Interface

1. **Dashboard** (`/segmentation`)
   - View cluster statistics
   - See cluster distribution
   - Train/retrain model

2. **Predict** (`/segmentation/predict`)
   - Input customer data
   - Get real-time segment prediction
   - View cluster profile

3. **Visualize** (`/segmentation/visualize`)
   - 2D scatter plot of clusters
   - PCA visualization
   - Interactive charts

4. **Customers** (`/segmentation/customers`)
   - Browse segmented customers
   - Export data

### API Endpoints

#### Get Cluster Statistics
```bash
GET /segmentation/getClusterStats
```

Response:
```json
{
  "success": true,
  "profiles": {
    "0": {
      "cluster_id": 0,
      "size": 2500,
      "percentage": 25.0,
      "features": {...}
    }
  },
  "distribution": [...]
}
```

#### Predict Single Customer
```bash
POST /segmentation/predictSegment
Content-Type: application/json

{
  "age": 35,
  "job": "management",
  "marital": "married",
  "education": "tertiary",
  "balance": 5000,
  ...
}
```

Response:
```json
{
  "success": true,
  "prediction": {
    "cluster": 2,
    "cluster_profile": {...},
    "confidence": 0.85
  }
}
```

#### Get Visualization Data
```bash
GET /segmentation/getVisualizationData
```

#### Train Model
```bash
POST /segmentation/trainModel
```

### Python API (CLI)

```bash
cd ml_pipeline

# Predict single customer
python3 predict_api.py '{"age": 35, "job": "management", "balance": 5000, ...}'

# Batch prediction
python3 predict_api.py '[{...}, {...}, ...]'
```

## Machine Learning Pipeline

### Features Used

- **Demographics**: age, job, marital, education
- **Financial**: balance, housing, loan, default
- **Campaign**: contact, day, month, duration, campaign, pdays, previous, poutcome
- **Target**: deposit

### Preprocessing Steps

1. Label encoding for categorical variables
2. Feature scaling using StandardScaler
3. PCA for dimensionality reduction (visualization)

### Clustering

- Algorithm: K-Means
- Number of clusters: 4 (configurable)
- Distance metric: Euclidean

### Model Outputs

- **Cluster assignments**: 0, 1, 2, 3
- **Cluster profiles**: Statistical summaries for each segment
- **PCA coordinates**: For visualization

## Data

### Source
- **Dataset**: Bank Marketing Dataset (bank.csv)
- **Records**: 11,163 customers
- **Features**: 17 attributes

### Columns
- `age`: Customer age
- `job`: Type of job
- `marital`: Marital status
- `education`: Education level
- `default`: Has credit in default?
- `balance`: Average yearly balance (euros)
- `housing`: Has housing loan?
- `loan`: Has personal loan?
- `contact`: Contact communication type
- `day`: Last contact day of month
- `month`: Last contact month
- `duration`: Last contact duration (seconds)
- `campaign`: Number of contacts during campaign
- `pdays`: Days since last contact
- `previous`: Number of contacts before campaign
- `poutcome`: Outcome of previous campaign
- `deposit`: Has term deposit?

## Customization

### Change Number of Clusters

Edit `ml_pipeline/customer_segmentation.py`:

```python
pipeline = CustomerSegmentationPipeline(n_clusters=5)  # Change from 4 to 5
```

### Add New Features

1. Update feature selection in `preprocess_data()` method
2. Retrain model
3. Update prediction form in views

### Modify Clustering Algorithm

Replace K-Means with other algorithms:

```python
from sklearn.cluster import DBSCAN, AgglomerativeClustering

# Instead of KMeans
self.model = DBSCAN(eps=0.5, min_samples=5)
```

## Deployment

### Production Checklist

- [ ] Set `CI_ENVIRONMENT = production` in `.env`
- [ ] Configure production database
- [ ] Set proper file permissions
- [ ] Configure web server (Apache/Nginx)
- [ ] Install Python dependencies on server
- [ ] Train model with production data
- [ ] Enable HTTPS
- [ ] Configure CORS if needed
- [ ] Set up monitoring and logging

### Web Server Configuration

**Apache** (.htaccess):
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

**Nginx**:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Troubleshooting

### Model Not Found Error
- Train the model first: `bash ml_pipeline/train_model.sh`
- Check `models/` directory exists and has permissions

### Python Not Found
- Check Python installation: `python3 --version`
- Update path in controller: `$this->pythonPath = 'python3'`

### Permission Errors
```bash
chmod +x ml_pipeline/train_model.sh
chmod -R 755 models/
```

### Prediction Fails
- Ensure all required fields are provided
- Check input data format matches training data
- Verify model is loaded correctly

## Performance

- **Training time**: ~30 seconds (11K records)
- **Prediction time**: <100ms per customer
- **Model size**: ~500KB
- **Memory usage**: ~50MB

## Future Enhancements

- [ ] Add more clustering algorithms
- [ ] Implement cluster comparison
- [ ] Add customer lifetime value prediction
- [ ] Create automated retraining schedule
- [ ] Add A/B testing framework
- [ ] Implement real-time streaming predictions
- [ ] Add model versioning
- [ ] Create admin panel for model management

## License

MIT License

## Credits

Developed using:
- CodeIgniter 4
- Scikit-learn
- Bootstrap 5
- Chart.js

---

**Note**: This is a demonstration project for educational purposes. For production use, add proper authentication, authorization, input validation, and error handling.
