<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-container {
            padding: 2rem 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
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
        .stat-label {
            color: #6c757d;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        .cluster-card {
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }
        .cluster-0 { border-left-color: #ff6b6b; }
        .cluster-1 { border-left-color: #4ecdc4; }
        .cluster-2 { border-left-color: #45b7d1; }
        .cluster-3 { border-left-color: #f9ca24; }
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .navbar-brand {
            font-weight: bold;
            color: #667eea !important;
        }
        .nav-link {
            color: #495057 !important;
            font-weight: 500;
        }
        .nav-link.active {
            color: #667eea !important;
        }
        .loading {
            text-align: center;
            padding: 3rem;
        }
        .btn-train {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
        }
        .btn-train:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <?= view('segmentation/navbar', ['active_menu' => $active_menu]) ?>

    <div class="container dashboard-container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0"><i class="fas fa-chart-pie"></i> Customer Segmentation Dashboard</h2>
                                <p class="text-muted mb-0">Machine Learning powered customer analytics</p>
                            </div>
                            <button class="btn btn-train" onclick="trainModel()">
                                <i class="fas fa-robot"></i> Train Model
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="stats-container" class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <div class="stat-value" id="total-customers">-</div>
                    <div class="stat-label">Total Customers</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-layer-group fa-3x text-success mb-3"></i>
                    <div class="stat-value" id="total-clusters">-</div>
                    <div class="stat-label">Customer Segments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-calendar fa-3x text-warning mb-3"></i>
                    <div class="stat-value" id="avg-age">-</div>
                    <div class="stat-label">Average Age</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <i class="fas fa-dollar-sign fa-3x text-info mb-3"></i>
                    <div class="stat-value" id="avg-balance">-</div>
                    <div class="stat-label">Avg Balance</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-bar"></i> Cluster Distribution</h5>
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle"></i> Cluster Profiles</h5>
                        <div id="cluster-profiles">
                            <div class="loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let distributionChart = null;

        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });

        async function loadDashboardData() {
            try {
                const response = await fetch('/segmentation/getClusterStats');
                const data = await response.json();

                if (data.success) {
                    updateStatistics();
                    updateDistribution(data.distribution);
                    updateClusterProfiles(data.profiles);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError('Failed to load dashboard data: ' + error.message);
            }
        }

        function updateStatistics() {
            // This would ideally come from the API
            fetch('/segmentation/getClusterStats')
                .then(response => response.json())
                .then(data => {
                    if (data.distribution) {
                        const total = data.distribution.reduce((sum, item) => sum + item.count, 0);
                        document.getElementById('total-customers').textContent = total.toLocaleString();
                        document.getElementById('total-clusters').textContent = data.distribution.length;
                    }
                });
        }

        function updateDistribution(distribution) {
            const ctx = document.getElementById('distributionChart').getContext('2d');

            if (distributionChart) {
                distributionChart.destroy();
            }

            const labels = distribution.map(item => `Cluster ${item.cluster}`);
            const values = distribution.map(item => item.count);
            const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#a8e6cf'];

            distributionChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function updateClusterProfiles(profiles) {
            const container = document.getElementById('cluster-profiles');
            container.innerHTML = '';

            for (let clusterId in profiles) {
                const profile = profiles[clusterId];
                const card = document.createElement('div');
                card.className = `cluster-card cluster-${clusterId}`;
                card.innerHTML = `
                    <h6><strong>Cluster ${clusterId}</strong></h6>
                    <p class="mb-1">Size: <strong>${profile.size.toLocaleString()}</strong> customers (${profile.percentage.toFixed(1)}%)</p>
                    <small class="text-muted">View details in visualization page</small>
                `;
                container.appendChild(card);
            }
        }

        async function trainModel() {
            if (!confirm('Are you sure you want to train/retrain the model? This may take several minutes.')) {
                return;
            }

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Training...';

            try {
                const response = await fetch('/segmentation/trainModel', {
                    method: 'POST'
                });
                const data = await response.json();

                if (data.success) {
                    alert('Training started successfully! Please check back in a few minutes.');
                    setTimeout(() => loadDashboardData(), 3000);
                } else {
                    alert('Training failed: ' + data.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-robot"></i> Train Model';
            }
        }

        function showError(message) {
            const container = document.getElementById('cluster-profiles');
            container.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> ${message}
                    <br><small>Please train the model first using the "Train Model" button.</small>
                </div>
            `;
        }
    </script>
</body>
</html>
