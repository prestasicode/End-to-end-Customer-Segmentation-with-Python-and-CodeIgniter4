<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="/segmentation">
            <i class="fas fa-brain"></i> ML Segmentation
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($active_menu === 'dashboard') ? 'active' : '' ?>" href="/segmentation">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_menu === 'predict') ? 'active' : '' ?>" href="/segmentation/predict">
                        <i class="fas fa-magic"></i> Predict
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_menu === 'visualize') ? 'active' : '' ?>" href="/segmentation/visualize">
                        <i class="fas fa-chart-scatter"></i> Visualize
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($active_menu === 'customers') ? 'active' : '' ?>" href="/segmentation/customers">
                        <i class="fas fa-users"></i> Customers
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
