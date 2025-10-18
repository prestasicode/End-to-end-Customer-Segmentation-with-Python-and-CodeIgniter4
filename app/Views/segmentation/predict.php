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
        .predict-container {
            padding: 2rem 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .btn-predict {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: bold;
        }
        .result-card {
            display: none;
            margin-top: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
        }
        .cluster-badge {
            font-size: 3rem;
            font-weight: bold;
            padding: 1rem 2rem;
            border-radius: 15px;
            display: inline-block;
            color: white;
        }
        .cluster-0-badge { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); }
        .cluster-1-badge { background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%); }
        .cluster-2-badge { background: linear-gradient(135deg, #45b7d1 0%, #2980b9 100%); }
        .cluster-3-badge { background: linear-gradient(135deg, #f9ca24 0%, #f39c12 100%); }
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 15px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?= view('segmentation/navbar', ['active_menu' => $active_menu]) ?>

    <div class="container predict-container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-magic"></i> Predict Customer Segment
                        </h3>

                        <form id="predictForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Age</label>
                                    <input type="number" class="form-control" name="age" required min="18" max="100" value="35">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Job</label>
                                    <select class="form-control" name="job" required>
                                        <option value="admin.">Admin</option>
                                        <option value="technician">Technician</option>
                                        <option value="services">Services</option>
                                        <option value="management">Management</option>
                                        <option value="retired">Retired</option>
                                        <option value="blue-collar">Blue-collar</option>
                                        <option value="unemployed">Unemployed</option>
                                        <option value="entrepreneur">Entrepreneur</option>
                                        <option value="housemaid">Housemaid</option>
                                        <option value="self-employed">Self-employed</option>
                                        <option value="student">Student</option>
                                        <option value="unknown">Unknown</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Marital Status</label>
                                    <select class="form-control" name="marital" required>
                                        <option value="married">Married</option>
                                        <option value="single">Single</option>
                                        <option value="divorced">Divorced</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Education</label>
                                    <select class="form-control" name="education" required>
                                        <option value="primary">Primary</option>
                                        <option value="secondary">Secondary</option>
                                        <option value="tertiary">Tertiary</option>
                                        <option value="unknown">Unknown</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Credit</label>
                                    <select class="form-control" name="default" required>
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Balance</label>
                                    <input type="number" class="form-control" name="balance" required value="1000">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Housing Loan</label>
                                    <select class="form-control" name="housing" required>
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Personal Loan</label>
                                    <select class="form-control" name="loan" required>
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Contact Type</label>
                                    <select class="form-control" name="contact" required>
                                        <option value="cellular">Cellular</option>
                                        <option value="telephone">Telephone</option>
                                        <option value="unknown">Unknown</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Day of Month</label>
                                    <input type="number" class="form-control" name="day" required min="1" max="31" value="15">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Month</label>
                                    <select class="form-control" name="month" required>
                                        <option value="jan">January</option>
                                        <option value="feb">February</option>
                                        <option value="mar">March</option>
                                        <option value="apr">April</option>
                                        <option value="may" selected>May</option>
                                        <option value="jun">June</option>
                                        <option value="jul">July</option>
                                        <option value="aug">August</option>
                                        <option value="sep">September</option>
                                        <option value="oct">October</option>
                                        <option value="nov">November</option>
                                        <option value="dec">December</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duration (seconds)</label>
                                    <input type="number" class="form-control" name="duration" required value="300">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Campaign</label>
                                    <input type="number" class="form-control" name="campaign" required value="1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Pdays</label>
                                    <input type="number" class="form-control" name="pdays" required value="-1">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Previous</label>
                                    <input type="number" class="form-control" name="previous" required value="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Previous Outcome</label>
                                    <select class="form-control" name="poutcome" required>
                                        <option value="unknown">Unknown</option>
                                        <option value="success">Success</option>
                                        <option value="failure">Failure</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Deposit</label>
                                    <select class="form-control" name="deposit" required>
                                        <option value="no">No</option>
                                        <option value="yes">Yes</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-predict btn-lg">
                                    <i class="fas fa-magic"></i> Predict Segment
                                </button>
                            </div>
                        </form>

                        <div id="resultCard" class="result-card">
                            <div class="text-center">
                                <h4>Prediction Result</h4>
                                <div class="my-4">
                                    <span id="clusterBadge" class="cluster-badge">Cluster 0</span>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <h6>Cluster Size</h6>
                                        <p id="clusterSize" class="lead">-</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Confidence</h6>
                                        <p id="confidence" class="lead">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('predictForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = e.target.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Predicting...';

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('/segmentation/predictSegment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    displayResult(result.prediction);
                } else {
                    alert('Prediction failed: ' + result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-magic"></i> Predict Segment';
            }
        });

        function displayResult(prediction) {
            const resultCard = document.getElementById('resultCard');
            const clusterBadge = document.getElementById('clusterBadge');
            const clusterSize = document.getElementById('clusterSize');
            const confidence = document.getElementById('confidence');

            const cluster = prediction.cluster;
            clusterBadge.textContent = `Cluster ${cluster}`;
            clusterBadge.className = `cluster-badge cluster-${cluster}-badge`;

            clusterSize.textContent = `${prediction.cluster_profile.size.toLocaleString()} customers (${prediction.cluster_profile.percentage.toFixed(1)}%)`;
            confidence.textContent = `${(prediction.confidence * 100).toFixed(1)}%`;

            resultCard.style.display = 'block';
            resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    </script>
</body>
</html>
