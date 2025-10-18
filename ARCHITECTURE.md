# System Architecture - Customer Segmentation Application

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER INTERFACE                            │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐       │
│  │Dashboard │  │ Predict  │  │Visualize │  │Customers │       │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘       │
└───────┼─────────────┼─────────────┼─────────────┼──────────────┘
        │             │             │             │
        └─────────────┴─────────────┴─────────────┘
                      │
        ┌─────────────▼─────────────┐
        │   CodeIgniter 4 Router    │
        └─────────────┬─────────────┘
                      │
        ┌─────────────▼─────────────┐
        │  Segmentation Controller  │
        │  (PHP Application Layer)  │
        └─────────────┬─────────────┘
                      │
        ┌─────────────┴─────────────┐
        │                           │
        ▼                           ▼
┌───────────────┐           ┌──────────────┐
│CustomerModel  │           │  Python API  │
│  (Data Layer) │           │   (ML Layer) │
└───────┬───────┘           └──────┬───────┘
        │                          │
        ▼                          ▼
┌───────────────┐           ┌──────────────┐
│   CSV Files   │           │ Trained Model│
│  (Database)   │           │   (PKL File) │
└───────────────┘           └──────────────┘
```

---

## Component Diagram

```
┌──────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                         │
├──────────────────────────────────────────────────────────────┤
│  Views (Bootstrap 5 + Chart.js)                              │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐              │
│  │dashboard.php│ │predict.php │ │visualize.php│              │
│  └────────────┘ └────────────┘ └────────────┘              │
└──────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌──────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                          │
├──────────────────────────────────────────────────────────────┤
│  Controllers (PHP)                                           │
│  ┌──────────────────────────────────────┐                   │
│  │  Segmentation Controller             │                   │
│  │  • index()         • predict()       │                   │
│  │  • customers()     • predictSegment()│                   │
│  │  • visualize()     • trainModel()    │                   │
│  └──────────────────────────────────────┘                   │
└──────────────────────────────────────────────────────────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
              ▼                         ▼
┌─────────────────────────┐  ┌────────────────────────┐
│     DATA LAYER          │  │    ML INTEGRATION      │
├─────────────────────────┤  ├────────────────────────┤
│  CustomerModel.php      │  │  exec() Python Scripts │
│  • getAllCustomers()    │  │  predict_api.py        │
│  • getClusterStats()    │  │                        │
│  • importFromCSV()      │  │                        │
└───────────┬─────────────┘  └────────┬───────────────┘
            │                         │
            ▼                         ▼
┌─────────────────────────┐  ┌────────────────────────┐
│   FILE STORAGE          │  │   ML PIPELINE          │
├─────────────────────────┤  ├────────────────────────┤
│  bank.csv               │  │  Scikit-learn          │
│  segmented_customers.csv│  │  • K-Means             │
│  cluster_profiles.json  │  │  • StandardScaler      │
└─────────────────────────┘  │  • PCA                 │
                             │  • LabelEncoder        │
                             └────────────────────────┘
```

---

## Data Flow Diagram

### 1. Model Training Flow

```
┌─────────┐
│bank.csv │
└────┬────┘
     │
     ▼
┌────────────────────────┐
│  Load Data (pandas)    │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│  Preprocess            │
│  • Label Encoding      │
│  • Feature Selection   │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│  Scale (StandardScaler)│
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│  K-Means Clustering    │
│  (n_clusters=4)        │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│  PCA (n_components=2)  │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│  Generate Profiles     │
└────────┬───────────────┘
         │
         ▼
┌────────────────────────┐
│  Save Model & Data     │
│  • .pkl (model)        │
│  • .json (profiles)    │
│  • .csv (segmented)    │
└────────────────────────┘
```

### 2. Prediction Flow

```
┌─────────────┐
│ User Input  │
│ (Web Form)  │
└──────┬──────┘
       │
       ▼
┌──────────────────────┐
│ Segmentation         │
│ Controller           │
└──────┬───────────────┘
       │ JSON
       ▼
┌──────────────────────┐
│ exec() Python        │
│ predict_api.py       │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Load Model (.pkl)    │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Preprocess Input     │
│ • Encode             │
│ • Scale              │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Predict Cluster      │
│ • K-Means.predict()  │
│ • PCA.transform()    │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ Return JSON          │
│ • cluster            │
│ • profile            │
│ • confidence         │
└──────┬───────────────┘
       │ JSON Response
       ▼
┌──────────────────────┐
│ Display Results      │
│ (JavaScript)         │
└──────────────────────┘
```

---

## Technology Stack Layers

```
┌─────────────────────────────────────────────────────────┐
│                    FRONTEND LAYER                        │
├─────────────────────────────────────────────────────────┤
│  HTML5  │  Bootstrap 5  │  Chart.js  │  Font Awesome   │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                   WEB FRAMEWORK                          │
├─────────────────────────────────────────────────────────┤
│  CodeIgniter 4 (PHP 8.1+)                               │
│  • MVC Architecture                                      │
│  • Routing                                               │
│  • Controllers                                           │
│  • Models                                                │
│  • Views                                                 │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│              ML/DATA SCIENCE LAYER                       │
├─────────────────────────────────────────────────────────┤
│  Python 3.8+                                             │
│  • pandas (Data manipulation)                            │
│  • numpy (Numerical computing)                           │
│  • scikit-learn (Machine Learning)                       │
│    - KMeans                                              │
│    - StandardScaler                                      │
│    - PCA                                                 │
│    - LabelEncoder                                        │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                   DATA STORAGE                           │
├─────────────────────────────────────────────────────────┤
│  File System                                             │
│  • CSV files (data storage)                              │
│  • PKL files (model persistence)                         │
│  • JSON files (configuration)                            │
└─────────────────────────────────────────────────────────┘
```

---

## Request-Response Cycle

### User Makes Prediction Request

```
1. User fills form → 2. Submit (POST) → 3. PHP Controller
                                              ↓
                                        4. Validate input
                                              ↓
                                        5. Prepare JSON
                                              ↓
                                        6. exec() Python
                                              ↓
┌─────────────────────────────────────────────────────────┐
│                    Python Process                        │
│  7. Load model                                           │
│  8. Preprocess input                                     │
│  9. Predict cluster                                      │
│  10. Format JSON response                                │
└─────────────────────────────────────────────────────────┘
                                              ↓
                                        11. Parse JSON
                                              ↓
                                        12. Return to view
                                              ↓
13. JavaScript renders result ← 14. Display to user
```

---

## File Structure with Responsibilities

```
project/
│
├── ml_pipeline/                    [ML Components]
│   ├── customer_segmentation.py   → Training pipeline
│   ├── predict_api.py              → Prediction interface
│   ├── train_model.sh              → Training automation
│   └── requirements.txt            → Dependencies
│
├── models/                         [Model Artifacts]
│   ├── segmentation_model.pkl     → Trained model + scalers
│   ├── cluster_profiles.json      → Cluster statistics
│   └── segmented_customers.csv    → Labeled data
│
├── app/                           [Web Application]
│   ├── Controllers/
│   │   └── Segmentation.php       → Request handling
│   ├── Models/
│   │   └── CustomerModel.php      → Data operations
│   ├── Views/
│   │   └── segmentation/          → UI templates
│   └── Config/
│       └── Routes.php              → URL routing
│
├── public/                        [Web Root]
│   ├── index.php                  → Entry point
│   └── assets/                    → Static files
│
├── bank.csv                       [Source Data]
├── demo.php                       [Standalone Demo]
└── start.sh                       [Quick Start]
```

---

## API Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    REST API ENDPOINTS                    │
├─────────────────────────────────────────────────────────┤
│  GET  /segmentation                 → Dashboard          │
│  GET  /segmentation/predict         → Prediction Form    │
│  GET  /segmentation/visualize       → Visualization      │
│  GET  /segmentation/customers       → Customer List      │
│                                                           │
│  GET  /segmentation/getClusterStats → Cluster Data       │
│  POST /segmentation/predictSegment  → Single Prediction  │
│  POST /segmentation/predictBatch    → Batch Prediction   │
│  GET  /segmentation/getVisualizationData → Chart Data    │
│  POST /segmentation/trainModel      → Trigger Training   │
└─────────────────────────────────────────────────────────┘
```

---

## Security Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    SECURITY LAYERS                       │
├─────────────────────────────────────────────────────────┤
│  1. Input Validation                                     │
│     • Form validation (client-side)                      │
│     • Data type checking (server-side)                   │
│     • Sanitization (XSS prevention)                      │
│                                                           │
│  2. Command Execution Safety                             │
│     • escapeshellcmd() for Python path                   │
│     • escapeshellarg() for parameters                    │
│     • JSON encoding for data transfer                    │
│                                                           │
│  3. File System Security                                 │
│     • Read-only model files                              │
│     • Controlled write paths                             │
│     • Permission restrictions                            │
│                                                           │
│  4. Production Hardening (Future)                        │
│     • HTTPS enforcement                                  │
│     • CSRF protection                                    │
│     • Authentication                                     │
│     • Rate limiting                                      │
└─────────────────────────────────────────────────────────┘
```

---

## Deployment Architecture

### Development
```
┌──────────────────────┐
│  Local Machine       │
│  ┌────────────────┐  │
│  │ PHP Built-in   │  │
│  │ Server :8080   │  │
│  └────────────────┘  │
│  ┌────────────────┐  │
│  │ Python 3.x     │  │
│  └────────────────┘  │
│  ┌────────────────┐  │
│  │ File System    │  │
│  └────────────────┘  │
└──────────────────────┘
```

### Production
```
┌────────────────────────────────────────┐
│  Web Server (Apache/Nginx)             │
│  ┌──────────────────────────────────┐  │
│  │  PHP-FPM 8.2                     │  │
│  │  ┌────────────────────────────┐  │  │
│  │  │  CodeIgniter Application   │  │  │
│  │  └────────────────────────────┘  │  │
│  └──────────────────────────────────┘  │
│  ┌──────────────────────────────────┐  │
│  │  Python Runtime                  │  │
│  │  • scikit-learn                  │  │
│  │  • pandas, numpy                 │  │
│  └──────────────────────────────────┘  │
│  ┌──────────────────────────────────┐  │
│  │  File System / Database          │  │
│  │  • /var/www/models/              │  │
│  │  • MySQL (optional)              │  │
│  └──────────────────────────────────┘  │
└────────────────────────────────────────┘
         │
         ▼
┌────────────────────────────────────────┐
│  SSL/TLS (Let's Encrypt)               │
└────────────────────────────────────────┘
         │
         ▼
┌────────────────────────────────────────┐
│  Users (Browser/API Clients)           │
└────────────────────────────────────────┘
```

---

## Performance Optimization

```
┌─────────────────────────────────────────────────────────┐
│                OPTIMIZATION STRATEGIES                   │
├─────────────────────────────────────────────────────────┤
│  1. Model Layer                                          │
│     • Model loaded once and cached in memory             │
│     • Pickle serialization for fast loading              │
│     • PCA reduces dimensionality                         │
│                                                           │
│  2. Application Layer                                    │
│     • PHP OPcache enabled                                │
│     • Lazy loading of resources                          │
│     • Efficient routing                                  │
│                                                           │
│  3. Data Layer                                           │
│     • CSV reading optimized                              │
│     • Pagination for large datasets                      │
│     • Indexing (if using database)                       │
│                                                           │
│  4. Frontend Layer                                       │
│     • CDN for static assets                              │
│     • Async JavaScript loading                           │
│     • Chart.js for efficient rendering                   │
│                                                           │
│  5. Future Optimizations                                 │
│     • Redis caching                                      │
│     • Message queues for batch jobs                      │
│     • API response caching                               │
└─────────────────────────────────────────────────────────┘
```

---

## Scalability Considerations

```
Current: Single Server
┌─────────────┐
│ Web + ML    │
└─────────────┘

Future: Distributed
┌─────────────┐     ┌─────────────┐
│ Web Server  │────▶│ ML Service  │
└─────────────┘     └─────────────┘
      │                    │
      ▼                    ▼
┌─────────────┐     ┌─────────────┐
│  Database   │     │Model Store  │
└─────────────┘     └─────────────┘
```

---

## Error Handling Flow

```
User Request
    ↓
Try {
    Validate Input
        ↓
    Process Request
        ↓
    Execute ML
        ↓
    Return Success
}
Catch {
    Input Error → 400 Bad Request
    Model Error → 500 Internal Error
    Python Error → Log + User Message
    File Error → 404 Not Found
}
```

---

## Monitoring Points

```
┌─────────────────────────────────────────────────────────┐
│               MONITORING & LOGGING                       │
├─────────────────────────────────────────────────────────┤
│  1. Application Logs                                     │
│     • writable/logs/log-*.php                            │
│     • Request/response logging                           │
│                                                           │
│  2. ML Pipeline Logs                                     │
│     • Training output                                    │
│     • Prediction errors                                  │
│                                                           │
│  3. Performance Metrics                                  │
│     • Request duration                                   │
│     • Prediction latency                                 │
│     • Memory usage                                       │
│                                                           │
│  4. Health Checks                                        │
│     • Model file exists                                  │
│     • Python executable available                        │
│     • Write permissions                                  │
└─────────────────────────────────────────────────────────┘
```

---

## Summary

This architecture provides:

✅ **Separation of Concerns:** ML logic separate from web logic
✅ **Modularity:** Components can be updated independently
✅ **Scalability:** Can be distributed across servers
✅ **Maintainability:** Clear structure and documentation
✅ **Performance:** Optimized data flow and caching
✅ **Security:** Multiple layers of protection
✅ **Flexibility:** Easy to add new features or models

The system is designed to be both powerful and easy to understand, making it suitable for production deployment while remaining accessible for development and customization.
