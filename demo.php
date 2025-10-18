<?php
/**
 * Standalone Demo - Customer Segmentation
 * This is a simplified version that works without full CodeIgniter installation
 */

// Configuration
define('BASEPATH', __DIR__ . '/');
define('ML_SCRIPT', BASEPATH . 'ml_pipeline/predict_api.py');
define('PYTHON_PATH', 'python3');
define('MODELS_PATH', BASEPATH . 'models/');

// Handle routing
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$request = parse_url($request, PHP_URL_PATH);

// Simple router
if ($request === '/' || $request === '/demo.php') {
    showDashboard();
} elseif ($request === '/predict' && $method === 'GET') {
    showPredictForm();
} elseif ($request === '/api/predict' && $method === 'POST') {
    handlePredict();
} elseif ($request === '/api/stats') {
    getStats();
} elseif ($request === '/visualize') {
    showVisualization();
} else {
    http_response_code(404);
    echo "404 Not Found";
}

// Functions

function showDashboard() {
    $stats = loadStats();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Customer Segmentation - Demo</title>
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
                    <h1><i class="fas fa-brain"></i> Customer Segmentation Demo</h1>
                    <p class="text-muted">Machine Learning powered customer analytics</p>
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
                        <a href="/predict" class="btn btn-primary btn-lg">
                            <i class="fas fa-magic"></i> Predict Customer Segment
                        </a>
                        <a href="/visualize" class="btn btn-success btn-lg">
                            <i class="fas fa-chart-scatter"></i> View Cluster Visualization
                        </a>
                        <a href="<?= MODELS_PATH ?>segmented_customers.csv" class="btn btn-info btn-lg" download>
                            <i class="fas fa-download"></i> Download Segmented Data
                        </a>
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
    </body>
    </html>
    <?php
}

function showPredictForm() {
    ?>
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
                    <a href="/" class="btn btn-secondary mb-3">← Back to Dashboard</a>

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

                const response = await fetch('/api/predict', {
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
    <?php
}

function showVisualization() {
    echo "Visualization page - Would show scatter plot of clusters using Chart.js";
}

function handlePredict() {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        return;
    }

    // Call Python script
    $command = sprintf(
        'cd %s && %s %s %s 2>&1',
        escapeshellarg(BASEPATH . 'ml_pipeline'),
        escapeshellcmd(PYTHON_PATH),
        escapeshellarg('predict_api.py'),
        escapeshellarg(json_encode($input))
    );

    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Prediction failed',
            'error' => implode("\n", $output)
        ]);
        return;
    }

    $result = json_decode(implode("\n", $output), true);

    echo json_encode([
        'success' => true,
        'prediction' => $result
    ]);
}

function getStats() {
    header('Content-Type: application/json');
    echo json_encode(loadStats());
}

function loadStats() {
    $profilesFile = MODELS_PATH . 'cluster_profiles.json';

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

    // Calculate averages from first cluster (approximation)
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
