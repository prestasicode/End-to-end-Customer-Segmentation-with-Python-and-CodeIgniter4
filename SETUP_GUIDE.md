# Customer Segmentation with CodeIgniter 4 - Complete Setup Guide

## Overview
This guide will walk you through setting up a complete customer segmentation application using Machine Learning (Python) integrated with CodeIgniter 4 (PHP).

---

## Prerequisites

### System Requirements
- **PHP 8.1+** (we'll install via Homebrew)
- **Python 3.8+** (already installed)
- **Composer** (PHP package manager)
- **Git** (version control)

---

## Step 1: Install PHP 8.1+ via Homebrew

```bash
# Install Homebrew if not already installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install PHP 8.1+
brew install php

# Verify PHP installation
php --version
```

Expected output:
```
PHP 8.4.13 (cli) (built: Oct 18 2025 16:32:15) (NTS)
```

---

## Step 2: Install Composer (PHP Package Manager)

```bash
# Install Composer globally
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verify Composer installation
composer --version
```

---

## Step 3: Navigate to Project Directory

```bash
cd "/Users/macosx/Downloads/SAMPLE DATA/MachineLearningwithCodeIgniter"
```

---

## Step 4: Install CodeIgniter 4 Dependencies

```bash
# Install PHP dependencies via Composer
composer install

# If composer.json doesn't exist, create it first:
composer init --name="yourname/customer-segmentation" --description="Customer Segmentation with ML and CodeIgniter 4" --type="project"

# Then add CodeIgniter 4
composer require codeigniter4/framework:^4.4
```

---

## Step 5: Setup Python Environment and Dependencies

```bash
# Create Python virtual environment
python3 -m venv venv

# Activate virtual environment
source venv/bin/activate

# Install Python ML dependencies
cd ml_pipeline
pip3 install pandas numpy scikit-learn matplotlib seaborn

# OR install from requirements file
pip3 install -r requirements.txt
```

---

## Step 6: Train the Machine Learning Model

```bash
# Navigate to ML pipeline directory
cd ml_pipeline

# Train the segmentation model
python3 customer_segmentation.py

# This will create:
# - ../models/segmentation_model.pkl
# - ../models/cluster_profiles.json
# - ../models/segmented_customers.csv
```

Expected output:
```
============================================================
Customer Segmentation Pipeline - Bank Marketing Data
============================================================
Loading data from ../bank.csv...
Data loaded: 11162 rows, 17 columns
...
Pipeline completed successfully!
============================================================
```

---

## Step 7: Configure CodeIgniter 4

### 7.1 Create Environment File
```bash
cd "/Users/macosx/Downloads/SAMPLE DATA/MachineLearningwithCodeIgniter"
cp .env.example .env
```

### 7.2 Edit .env file
```bash
# Edit the .env file
nano .env
```

Add/modify these settings:
```env
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080'
app.indexPage = ''

database.default.hostname = localhost
database.default.database = customer_segmentation
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
```

### 7.3 Create Routes Configuration
Create/edit `app/Config/Routes.php`:
```php
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Segmentation::index');
$routes->get('/segmentation', 'Segmentation::index');
$routes->get('/segmentation/predict', 'Segmentation::predict');
$routes->post('/segmentation/predictSegment', 'Segmentation::predictSegment');
$routes->get('/segmentation/getClusterStats', 'Segmentation::getClusterStats');
$routes->get('/segmentation/visualize', 'Segmentation::visualize');
$routes->get('/segmentation/customers', 'Segmentation::customers');
$routes->post('/segmentation/trainModel', 'Segmentation::trainModel');
```

---

## Step 8: Create CodeIgniter 4 Controller

Create `app/Controllers/Segmentation.php`:
```php
<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Segmentation extends Controller
{
    protected $pythonPath = 'python3';
    protected $mlScript;
    protected $modelsPath;

    public function __construct()
    {
        $this->mlScript = ROOTPATH . 'ml_pipeline/predict_api.py';
        $this->modelsPath = ROOTPATH . 'models/';
    }

    public function index()
    {
        $stats = $this->loadStats();
        return view('segmentation/dashboard', ['stats' => $stats]);
    }

    public function predict()
    {
        return view('segmentation/predict');
    }

    public function predictSegment()
    {
        $request = service('request');
        $input = $request->getJSON(true);

        if (!$input) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid input'
            ]);
        }

        // Call Python script
        $command = sprintf(
            'cd %s && %s %s %s 2>&1',
            escapeshellarg(ROOTPATH . 'ml_pipeline'),
            escapeshellcmd($this->pythonPath),
            escapeshellarg('predict_api.py'),
            escapeshellarg(json_encode($input))
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Prediction failed',
                'error' => implode("\n", $output)
            ]);
        }

        $result = json_decode(implode("\n", $output), true);

        return $this->response->setJSON([
            'success' => true,
            'prediction' => $result
        ]);
    }

    public function getClusterStats()
    {
        $stats = $this->loadStats();
        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats
        ]);
    }

    public function visualize()
    {
        return view('segmentation/visualize');
    }

    public function customers()
    {
        return view('segmentation/customers');
    }

    public function trainModel()
    {
        $command = sprintf(
            'cd %s && %s %s 2>&1',
            escapeshellarg(ROOTPATH . 'ml_pipeline'),
            escapeshellcmd($this->pythonPath),
            escapeshellarg('customer_segmentation.py')
        );

        exec($command, $output, $returnCode);

        return $this->response->setJSON([
            'success' => $returnCode === 0,
            'message' => $returnCode === 0 ? 'Model trained successfully' : 'Training failed',
            'output' => implode("\n", $output)
        ]);
    }

    private function loadStats()
    {
        $profilesFile = $this->modelsPath . 'cluster_profiles.json';

        if (!file_exists($profilesFile)) {
            return [
                'total_customers' => 0,
                'clusters' => 0,
                'avg_age' => 0,
                'avg_balance' => 0,
                'distribution' => []
            ];
        }

        $profiles = json_decode(file_get_contents($profilesFile), true);

        $total = 0;
        $distribution = [];

        foreach ($profiles as $id => $profile) {
            $total += $profile['size'];
            $distribution[] = [
                'id' => $id,
                'count' => $profile['size'],
                'percentage' => round($profile['percentage'], 1)
            ];
        }

        // Calculate averages
        $avgAge = 41;
        $avgBalance = 1500;

        if (!empty($profiles[0]['features'])) {
            $avgAge = round($profiles[0]['features']['age']['mean']);
            $avgBalance = round($profiles[0]['features']['balance']['mean']);
        }

        return [
            'total_customers' => $total,
            'clusters' => count($profiles),
            'avg_age' => $avgAge,
            'avg_balance' => $avgBalance,
            'distribution' => $distribution
        ];
    }
}
```

---

## Step 9: Create Views

### 9.1 Create Dashboard View
Create `app/Views/segmentation/dashboard.php`:
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Segmentation - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .stat-card {
            padding: 2rem;
            text-align: center;
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body text-center">
                <h1><i class="fas fa-brain"></i> Customer Segmentation Dashboard</h1>
                <p class="text-muted">Machine Learning powered customer analytics with CodeIgniter 4</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <div class="stat-value"><?= number_format($stats['total_customers']) ?></div>
                    <div class="text-muted">Total Customers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-layer-group fa-3x text-success mb-3"></i>
                    <div class="stat-value"><?= $stats['clusters'] ?></div>
                    <div class="text-muted">Segments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-calendar fa-3x text-warning mb-3"></i>
                    <div class="stat-value"><?= $stats['avg_age'] ?></div>
                    <div class="text-muted">Avg Age</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-dollar-sign fa-3x text-info mb-3"></i>
                    <div class="stat-value">$<?= number_format($stats['avg_balance']) ?></div>
                    <div class="text-muted">Avg Balance</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>Quick Actions</h3>
                <div class="d-grid gap-2">
                    <a href="/segmentation/predict" class="btn btn-primary btn-lg">
                        <i class="fas fa-magic"></i> Predict Customer Segment
                    </a>
                    <a href="/segmentation/visualize" class="btn btn-success btn-lg">
                        <i class="fas fa-chart-scatter"></i> View Cluster Visualization
                    </a>
                    <a href="/segmentation/customers" class="btn btn-info btn-lg">
                        <i class="fas fa-table"></i> Browse Customers
                    </a>
                    <button onclick="trainModel()" class="btn btn-warning btn-lg">
                        <i class="fas fa-cogs"></i> Retrain Model
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4>Cluster Distribution</h4>
                <?php foreach ($stats['distribution'] as $cluster): ?>
                    <div class="mb-2">
                        <strong>Cluster <?= $cluster['id'] ?>:</strong>
                        <?= number_format($cluster['count']) ?> customers (<?= $cluster['percentage'] ?>%)
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $cluster['percentage'] ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        async function trainModel() {
            const response = await fetch('/segmentation/trainModel', {method: 'POST'});
            const result = await response.json();
            alert(result.message);
            if (result.success) {
                location.reload();
            }
        }
    </script>
</body>
</html>
```

### 9.2 Create Prediction View
Create `app/Views/segmentation/predict.php`:
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predict Customer Segment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h2><i class="fas fa-magic"></i> Predict Customer Segment</h2>
                <a href="/segmentation" class="btn btn-secondary mb-3">← Back to Dashboard</a>

                <form id="predictForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Age</label>
                            <input type="number" class="form-control" name="age" required value="35">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job</label>
                            <select class="form-control" name="job" required>
                                <option value="management">Management</option>
                                <option value="technician">Technician</option>
                                <option value="services">Services</option>
                                <option value="admin.">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Balance</label>
                            <input type="number" class="form-control" name="balance" required value="5000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Education</label>
                            <select class="form-control" name="education" required>
                                <option value="tertiary">Tertiary</option>
                                <option value="secondary">Secondary</option>
                                <option value="primary">Primary</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="marital" value="married">
                    <input type="hidden" name="default" value="no">
                    <input type="hidden" name="housing" value="yes">
                    <input type="hidden" name="loan" value="no">
                    <input type="hidden" name="contact" value="cellular">
                    <input type="hidden" name="day" value="15">
                    <input type="hidden" name="month" value="may">
                    <input type="hidden" name="duration" value="300">
                    <input type="hidden" name="campaign" value="1">
                    <input type="hidden" name="pdays" value="-1">
                    <input type="hidden" name="previous" value="0">
                    <input type="hidden" name="poutcome" value="unknown">
                    <input type="hidden" name="deposit" value="yes">

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-magic"></i> Predict Segment
                    </button>
                </form>

                <div id="result" class="mt-4" style="display: none;">
                    <div class="alert alert-success">
                        <h4>Prediction Result</h4>
                        <p id="resultText"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('predictForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            const response = await fetch('/segmentation/predictSegment', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                document.getElementById('result').style.display = 'block';
                document.getElementById('resultText').innerHTML = `
                    <strong>Cluster: ${result.prediction.cluster}</strong><br>
                    Cluster Size: ${result.prediction.cluster_profile.size.toLocaleString()} customers
                    (${result.prediction.cluster_profile.percentage.toFixed(1)}%)<br>
                    Confidence: ${(result.prediction.confidence * 100).toFixed(1)}%
                `;
            }
        });
    </script>
</body>
</html>
```

---

## Step 10: Configure Web Server

### 10.1 For Development (CodeIgniter Built-in Server)
```bash
cd "/Users/macosx/Downloads/SAMPLE DATA/MachineLearningwithCodeIgniter"
php spark serve
```

### 10.2 For Development (PHP Built-in Server)
```bash
cd "/Users/macosx/Downloads/SAMPLE DATA/MachineLearningwithCodeIgniter"
php -S localhost:8080 -t public
```

---

## Step 11: Access the Application

1. **Dashboard**: http://localhost:8080/segmentation
2. **Prediction**: http://localhost:8080/segmentation/predict
3. **API Endpoint**: POST http://localhost:8080/segmentation/predictSegment

---

## Step 12: Test the Application

### 12.1 Test Dashboard
```bash
curl http://localhost:8080/segmentation
```

### 12.2 Test Prediction API
```bash
curl -X POST http://localhost:8080/segmentation/predictSegment \
  -H "Content-Type: application/json" \
  -d '{
    "age": 35,
    "job": "management",
    "marital": "married",
    "education": "tertiary",
    "balance": 5000,
    "housing": "yes",
    "loan": "no",
    "contact": "cellular",
    "day": 15,
    "month": "may",
    "duration": 300,
    "campaign": 1,
    "pdays": -1,
    "previous": 0,
    "poutcome": "unknown",
    "deposit": "yes"
  }'
```

---

## Project Structure

```
MachineLearningwithCodeIgniter/
├── app/
│   ├── Controllers/
│   │   └── Segmentation.php        # Main controller
│   ├── Views/
│   │   └── segmentation/           # View templates
│   │       ├── dashboard.php
│   │       ├── predict.php
│   │       └── visualize.php
│   └── Config/
│       └── Routes.php              # Route definitions
├── ml_pipeline/                    # Python ML pipeline
│   ├── customer_segmentation.py    # Training script
│   ├── predict_api.py             # Prediction API
│   └── requirements.txt           # Python dependencies
├── models/                        # Trained models
│   ├── segmentation_model.pkl     # Trained model
│   ├── cluster_profiles.json      # Cluster statistics
│   └── segmented_customers.csv    # Results
├── public/                        # Web root
│   └── index.php                  # Entry point
├── vendor/                        # Composer dependencies
├── .env                          # Environment config
├── composer.json                 # PHP dependencies
└── bank.csv                      # Training data
```

---

## Troubleshooting

### Common Issues:

1. **PHP Not Found**
   ```bash
   brew install php
   ```

2. **Composer Not Found**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

3. **CodeIgniter Dependencies Missing**
   ```bash
   composer install
   ```

4. **Python Dependencies Missing**
   ```bash
   pip3 install pandas numpy scikit-learn
   ```

5. **Model Not Found**
   ```bash
   cd ml_pipeline
   python3 customer_segmentation.py
   ```

6. **Permission Errors**
   ```bash
   chmod 755 ml_pipeline/
   chmod +x ml_pipeline/train_model.sh
   ```

---

## Next Steps

1. **Add Authentication**: Implement user login/registration
2. **Database Integration**: Store predictions in database
3. **Advanced Visualization**: Add Chart.js for cluster visualization
4. **API Documentation**: Create Swagger documentation
5. **Deployment**: Configure for production environment

---

## Support

For issues or questions:
- Check the logs: `tail -f writable/logs/*.log`
- Review PHP errors: Check server error logs
- Python debugging: Run scripts manually to check output

**Congratulations!** You now have a fully functional Customer Segmentation application with CodeIgniter 4 and Machine Learning integration! 🎉