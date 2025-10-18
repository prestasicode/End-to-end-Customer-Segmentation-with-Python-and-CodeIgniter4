# Deployment Guide - Customer Segmentation Application

## Quick Start (Development)

### Option 1: Using the start script
```bash
bash start.sh
```

This will:
1. Check Python and PHP installations
2. Install Python dependencies
3. Train the ML model (if not already trained)
4. Start the CodeIgniter development server

### Option 2: Manual setup
```bash
# 1. Install Python dependencies
cd ml_pipeline
pip3 install -r requirements.txt

# 2. Train the model
python3 customer_segmentation.py

# 3. Configure environment
cp .env.example .env

# 4. Start server
php spark serve
```

Then visit: **http://localhost:8080**

---

## System Requirements

### Minimum Requirements
- **PHP**: 8.1 or higher
- **Python**: 3.8 or higher
- **Memory**: 512MB RAM
- **Disk**: 100MB free space

### Recommended Requirements
- **PHP**: 8.2+
- **Python**: 3.10+
- **Memory**: 2GB RAM
- **Disk**: 500MB free space

---

## Installation Steps

### 1. Check Prerequisites

**Check PHP:**
```bash
php --version
```

**Check Python:**
```bash
python3 --version
```

**Check pip:**
```bash
pip3 --version
```

### 2. Clone/Download Project

```bash
cd /path/to/your/projects
# Project should be in: MachineLearningwithCodeIgniter/
```

### 3. Install Dependencies

**Python packages:**
```bash
cd ml_pipeline
pip3 install pandas numpy scikit-learn matplotlib seaborn
```

**Or use requirements.txt:**
```bash
pip3 install -r requirements.txt
```

**PHP packages (if using Composer):**
```bash
composer install
```

### 4. Configure Environment

```bash
cp .env.example .env
```

Edit `.env`:
```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
python.path = python3
```

### 5. Train the Model

```bash
cd ml_pipeline
bash train_model.sh
```

Or directly:
```bash
python3 customer_segmentation.py
```

**Expected output:**
- `models/segmentation_model.pkl` (53KB)
- `models/cluster_profiles.json` (10KB)
- `models/segmented_customers.csv` (1.3MB)

### 6. Verify Installation

**Test prediction API:**
```bash
cd ml_pipeline
python3 predict_api.py '{"age": 35, "job": "management", "marital": "married", "education": "tertiary", "default": "no", "balance": 5000, "housing": "yes", "loan": "no", "contact": "cellular", "day": 15, "month": "may", "duration": 300, "campaign": 1, "pdays": -1, "previous": 0, "poutcome": "unknown", "deposit": "yes"}'
```

Should return JSON with cluster prediction.

### 7. Start Development Server

```bash
php spark serve
```

Or specify port:
```bash
php spark serve --port=8000
```

### 8. Access Application

Open browser: **http://localhost:8080**

You should see the Customer Segmentation Dashboard.

---

## Production Deployment

### Apache Configuration

**Enable mod_rewrite:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Virtual Host Configuration:**
```apache
<VirtualHost *:80>
    ServerName segmentation.example.com
    DocumentRoot /var/www/segmentation/public

    <Directory /var/www/segmentation/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/segmentation-error.log
    CustomLog ${APACHE_LOG_DIR}/segmentation-access.log combined
</VirtualHost>
```

**public/.htaccess:**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name segmentation.example.com;
    root /var/www/segmentation/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/segmentation

# Set directory permissions
find /var/www/segmentation -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/segmentation -type f -exec chmod 644 {} \;

# Make writable directories
chmod -R 777 /var/www/segmentation/writable
chmod -R 755 /var/www/segmentation/models
```

### Environment Configuration

**Production .env:**
```ini
CI_ENVIRONMENT = production

app.baseURL = 'https://segmentation.example.com/'
app.indexPage = ''

# Security
app.CSRFProtection = true
app.CSRFTokenName = 'csrf_token'
app.CSRFHeaderName = 'X-CSRF-TOKEN'

# Python
python.path = /usr/bin/python3

# Disable debugging
CI_DEBUG = false
```

### Python on Production Server

```bash
# Install Python and pip
sudo apt-get update
sudo apt-get install python3 python3-pip

# Install packages
cd /var/www/segmentation/ml_pipeline
pip3 install -r requirements.txt

# Or install globally
sudo pip3 install pandas numpy scikit-learn
```

### Train Model on Server

```bash
cd /var/www/segmentation/ml_pipeline
python3 customer_segmentation.py
```

### SSL/HTTPS (Let's Encrypt)

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-apache

# Get certificate (Apache)
sudo certbot --apache -d segmentation.example.com

# Or for Nginx
sudo certbot --nginx -d segmentation.example.com
```

---

## Troubleshooting

### Issue: Python not found

**Solution:**
```bash
# Find Python path
which python3

# Update in controller
# app/Controllers/Segmentation.php
$this->pythonPath = '/usr/bin/python3';
```

### Issue: Permission denied on models/

**Solution:**
```bash
chmod 755 models/
chown www-data:www-data models/
```

### Issue: Module not found (Python)

**Solution:**
```bash
# Check installed packages
pip3 list

# Install missing package
pip3 install package_name

# Or reinstall all
pip3 install -r requirements.txt
```

### Issue: 404 Not Found

**Solution:**
- Check `.htaccess` in `public/` directory
- Verify `mod_rewrite` is enabled
- Check base URL in `.env`

### Issue: Prediction fails

**Solution:**
```bash
# Test Python script directly
cd ml_pipeline
python3 predict_api.py '{"age": 35, ...}'

# Check if model exists
ls -l ../models/segmentation_model.pkl

# Retrain if needed
python3 customer_segmentation.py
```

### Issue: Slow predictions

**Solution:**
- Use PHP's `exec()` with proper timeouts
- Consider caching predictions
- Use a message queue for batch processing
- Optimize model (reduce features or use faster algorithm)

---

## Performance Optimization

### 1. Enable OPcache (PHP)

Edit `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

### 2. Model Caching

The model is automatically loaded once and cached in memory.

### 3. Result Caching

Implement Redis for prediction caching:
```php
// In controller
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$cacheKey = 'prediction_' . md5(json_encode($input));
if ($redis->exists($cacheKey)) {
    return $redis->get($cacheKey);
}

// Make prediction
$result = // ... prediction logic

// Cache for 1 hour
$redis->setex($cacheKey, 3600, json_encode($result));
```

### 4. Async Processing

For batch predictions, use background jobs:
```bash
# Install Supervisor
sudo apt-get install supervisor

# Create job config
# /etc/supervisor/conf.d/segmentation-worker.conf
[program:segmentation-worker]
command=php /var/www/segmentation/spark queue:work
directory=/var/www/segmentation
autostart=true
autorestart=true
user=www-data
```

---

## Monitoring

### Application Logs

```bash
# CodeIgniter logs
tail -f writable/logs/log-*.php

# Python errors
# Check in training.log or redirect stderr to file
```

### System Monitoring

```bash
# Check PHP processes
ps aux | grep php

# Check memory usage
free -h

# Check disk space
df -h
```

### Error Tracking

Consider integrating:
- **Sentry** for error tracking
- **New Relic** for APM
- **Prometheus + Grafana** for metrics

---

## Backup & Maintenance

### Backup Strategy

```bash
# Backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/segmentation"

# Backup models
tar -czf $BACKUP_DIR/models_$DATE.tar.gz models/

# Backup data
cp bank.csv $BACKUP_DIR/bank_$DATE.csv

# Backup code
tar -czf $BACKUP_DIR/code_$DATE.tar.gz app/ ml_pipeline/
```

### Model Retraining

Schedule regular retraining with new data:
```bash
# Crontab
0 2 * * 0 cd /var/www/segmentation/ml_pipeline && python3 customer_segmentation.py
```

### Updates

```bash
# Update Python packages
pip3 install --upgrade pandas numpy scikit-learn

# Update PHP dependencies (if using Composer)
composer update
```

---

## Security Checklist

- [ ] Set `CI_ENVIRONMENT = production`
- [ ] Disable debug mode
- [ ] Enable CSRF protection
- [ ] Use HTTPS
- [ ] Validate all user inputs
- [ ] Sanitize file uploads
- [ ] Set proper file permissions
- [ ] Keep dependencies updated
- [ ] Implement rate limiting
- [ ] Add authentication/authorization
- [ ] Monitor logs for suspicious activity

---

## Support

For issues or questions:
1. Check the README.md
2. Review logs in `writable/logs/`
3. Test ML pipeline independently
4. Check CodeIgniter documentation
5. Verify all dependencies are installed

---

## Version History

- **v1.0.0** (2025-10-18): Initial release
  - K-Means clustering with 4 clusters
  - Web dashboard with visualization
  - Real-time prediction API
  - Batch processing support

---

**Happy Deploying!**
