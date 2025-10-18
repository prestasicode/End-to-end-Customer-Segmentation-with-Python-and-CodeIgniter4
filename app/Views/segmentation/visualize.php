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
        .visualize-container {
            padding: 2rem 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        #scatterChart {
            max-height: 600px;
        }
    </style>
</head>
<body>
    <?= view('segmentation/navbar', ['active_menu' => $active_menu]) ?>

    <div class="container visualize-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-chart-scatter"></i> Cluster Visualization
                        </h3>
                        <p class="text-muted">2D visualization of customer segments using PCA dimensionality reduction</p>

                        <div id="loading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Loading visualization data...</p>
                        </div>

                        <canvas id="scatterChart" style="display: none;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle"></i> Understanding the Visualization</h5>
                        <ul>
                            <li>Each point represents a customer</li>
                            <li>Colors represent different customer segments (clusters)</li>
                            <li>Proximity indicates similarity in customer characteristics</li>
                            <li>PCA (Principal Component Analysis) reduces multiple features to 2 dimensions for visualization</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let scatterChart = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadVisualizationData();
        });

        async function loadVisualizationData() {
            try {
                const response = await fetch('/segmentation/getVisualizationData');
                const result = await response.json();

                if (result.success) {
                    createScatterPlot(result.data);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('scatterChart').style.display = 'block';
                } else {
                    showError(result.message);
                }
            } catch (error) {
                showError('Failed to load visualization data: ' + error.message);
            }
        }

        function createScatterPlot(data) {
            const ctx = document.getElementById('scatterChart').getContext('2d');

            // Group data by cluster
            const clusters = {};
            data.forEach(point => {
                if (!clusters[point.cluster]) {
                    clusters[point.cluster] = [];
                }
                clusters[point.cluster].push({
                    x: point.pca_1,
                    y: point.pca_2
                });
            });

            // Define colors for each cluster
            const colors = {
                0: '#ff6b6b',
                1: '#4ecdc4',
                2: '#45b7d1',
                3: '#f9ca24',
                4: '#a8e6cf'
            };

            // Create datasets
            const datasets = Object.keys(clusters).map(clusterId => ({
                label: `Cluster ${clusterId}`,
                data: clusters[clusterId],
                backgroundColor: colors[clusterId] || '#95a5a6',
                borderColor: colors[clusterId] || '#95a5a6',
                pointRadius: 4,
                pointHoverRadius: 6
            }));

            scatterChart = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Customer Segmentation - PCA Visualization',
                            font: {
                                size: 16
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Cluster ${context.dataset.label.split(' ')[1]}: (${context.parsed.x.toFixed(2)}, ${context.parsed.y.toFixed(2)})`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Principal Component 1'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Principal Component 2'
                            }
                        }
                    }
                }
            });
        }

        function showError(message) {
            const loading = document.getElementById('loading');
            loading.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> ${message}
                    <br><small>Please ensure the model has been trained.</small>
                </div>
            `;
        }
    </script>
</body>
</html>
