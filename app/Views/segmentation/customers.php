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
        .customers-container {
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
        .table-container {
            overflow-x: auto;
        }
        .cluster-badge {
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
            font-weight: bold;
            color: white;
        }
        .cluster-0 { background-color: #ff6b6b; }
        .cluster-1 { background-color: #4ecdc4; }
        .cluster-2 { background-color: #45b7d1; }
        .cluster-3 { background-color: #f9ca24; }
    </style>
</head>
<body>
    <?= view('segmentation/navbar', ['active_menu' => $active_menu]) ?>

    <div class="container customers-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-users"></i> Customer Segments
                                </h3>
                                <p class="text-muted mb-0">Browse customers by their assigned segments</p>
                            </div>
                            <div>
                                <button class="btn btn-primary" onclick="exportData()">
                                    <i class="fas fa-download"></i> Export CSV
                                </button>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="table table-hover" id="customersTable">
                                <thead>
                                    <tr>
                                        <th>Cluster</th>
                                        <th>Age</th>
                                        <th>Job</th>
                                        <th>Education</th>
                                        <th>Balance</th>
                                        <th>Housing</th>
                                        <th>Loan</th>
                                        <th>Deposit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <nav>
                            <ul class="pagination justify-content-center" id="pagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let allCustomers = [];
        let currentPage = 1;
        const rowsPerPage = 20;

        document.addEventListener('DOMContentLoaded', function() {
            loadCustomers();
        });

        async function loadCustomers() {
            try {
                // For now, we'll show a message that data needs to be loaded from CSV
                const tbody = document.querySelector('#customersTable tbody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Customer data will be available after model training.
                                <br><small>Train the model from the dashboard to populate this table.</small>
                            </div>
                        </td>
                    </tr>
                `;

                // Try to load from segmented_customers.csv if available
                loadFromCSV();
            } catch (error) {
                console.error('Error loading customers:', error);
            }
        }

        async function loadFromCSV() {
            try {
                const response = await fetch('/segmentation/getVisualizationData');
                const result = await response.json();

                if (result.success && result.data) {
                    // For demo purposes, we'll use the visualization data
                    // In a real app, you'd have a separate endpoint for full customer data
                    displayCustomersMessage();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function displayCustomersMessage() {
            const tbody = document.querySelector('#customersTable tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Segmented customer data is available.
                            <br><small>In a production environment, this table would show paginated customer records.</small>
                            <br><br>
                            <strong>Sample clusters are visible in the Visualization page.</strong>
                        </div>
                    </td>
                </tr>
            `;
        }

        function displayCustomers() {
            const tbody = document.querySelector('#customersTable tbody');
            const start = (currentPage - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            const pageCustomers = allCustomers.slice(start, end);

            if (pageCustomers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No customers found</td></tr>';
                return;
            }

            tbody.innerHTML = pageCustomers.map(customer => `
                <tr>
                    <td><span class="cluster-badge cluster-${customer.cluster}">Cluster ${customer.cluster}</span></td>
                    <td>${customer.age}</td>
                    <td>${customer.job}</td>
                    <td>${customer.education}</td>
                    <td>$${parseFloat(customer.balance).toLocaleString()}</td>
                    <td>${customer.housing}</td>
                    <td>${customer.loan}</td>
                    <td>${customer.deposit}</td>
                </tr>
            `).join('');

            updatePagination();
        }

        function updatePagination() {
            const totalPages = Math.ceil(allCustomers.length / rowsPerPage);
            const pagination = document.getElementById('pagination');

            let html = '';
            for (let i = 1; i <= totalPages; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>`;
            }

            pagination.innerHTML = html;
        }

        function changePage(page) {
            currentPage = page;
            displayCustomers();
        }

        function exportData() {
            alert('Export functionality would download the segmented_customers.csv file. In production, this would trigger a download of the complete dataset with cluster assignments.');
            // In production, you'd implement actual CSV download
            window.open('/models/segmented_customers.csv', '_blank');
        }
    </script>
</body>
</html>
