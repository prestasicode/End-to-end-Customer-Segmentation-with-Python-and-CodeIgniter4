# Quick Start Guide - Customer Segmentation Application

Get up and running in 5 minutes!

---

## Prerequisites Check

Before starting, verify you have:

```bash
# Check Python (need 3.8+)
python3 --version

# Check PHP (need 8.1+)
php --version

# Check pip
pip3 --version
```

If any are missing:
- **Python:** Download from [python.org](https://python.org)
- **PHP:** Download from [php.net](https://php.net)

---

## Option 1: Automated Setup (Recommended)

### Single Command Start

```bash
bash start.sh
```

This will:
1. ✓ Check dependencies
2. ✓ Install Python packages
3. ✓ Train the ML model
4. ✓ Start the web server

Then open: **http://localhost:8080**

**That's it!** 🎉

---

## Option 2: Manual Setup (Step by Step)

### Step 1: Install Python Dependencies

```bash
cd ml_pipeline
pip3 install pandas numpy scikit-learn
```

### Step 2: Train the Model

```bash
python3 customer_segmentation.py
```

Wait ~30 seconds. You should see:
```
Pipeline completed successfully!
```

### Step 3: Start Web Server

```bash
cd ..
php -S localhost:8080 demo.php
```

### Step 4: Open Browser

Visit: **http://localhost:8080**

---

## Verify Installation

### Check 1: Model Files Created

```bash
ls -lh models/
```

Should show:
- `segmentation_model.pkl` (53KB)
- `cluster_profiles.json` (10KB)
- `segmented_customers.csv` (1.3MB)

### Check 2: Test Prediction

```bash
cd ml_pipeline
python3 predict_api.py '{"age": 35, "job": "management", "marital": "married", "education": "tertiary", "default": "no", "balance": 5000, "housing": "yes", "loan": "no", "contact": "cellular", "day": 15, "month": "may", "duration": 300, "campaign": 1, "pdays": -1, "previous": 0, "poutcome": "unknown", "deposit": "yes"}'
```

Should return JSON with cluster prediction.

### Check 3: Web Server Running

```bash
curl http://localhost:8080
```

Should return HTML content.

---

## Using the Application

### 1. Dashboard

**URL:** http://localhost:8080

**Features:**
- View total customers (11,162)
- See 4 customer segments
- View cluster distribution
- Train/retrain model

### 2. Make a Prediction

**URL:** http://localhost:8080/predict

**Steps:**
1. Fill in customer details (age, job, balance, education)
2. Click "Predict Segment"
3. View cluster assignment and profile

**Example Input:**
- Age: 35
- Job: Management
- Balance: $5,000
- Education: Tertiary

**Output:**
- Cluster: 1
- Cluster Size: 4,983 customers (44.6%)
- Confidence: 27.7%

### 3. View Visualization

**URL:** http://localhost:8080/visualize

**Features:**
- 2D scatter plot of all customers
- PCA dimensionality reduction
- Color-coded clusters
- Interactive charts

### 4. Browse Customers

**URL:** http://localhost:8080/customers

**Features:**
- View segmented customer data
- Export to CSV
- Paginated display

---

## Quick Commands Reference

```bash
# Train/retrain model
cd ml_pipeline && python3 customer_segmentation.py

# Start demo server
php -S localhost:8080 demo.php

# Start CodeIgniter server (if installed)
php spark serve

# Test prediction
cd ml_pipeline && python3 predict_api.py '{...json...}'

# Check model files
ls -lh models/

# View logs
tail -f writable/logs/*.php
```

---

## Troubleshooting

### Problem: Python packages not found

**Solution:**
```bash
cd ml_pipeline
pip3 install -r requirements.txt
```

### Problem: Model not found

**Solution:**
```bash
cd ml_pipeline
python3 customer_segmentation.py
```

### Problem: Port 8080 in use

**Solution:**
```bash
# Use different port
php -S localhost:8081 demo.php
```

### Problem: Permission denied

**Solution:**
```bash
chmod +x start.sh
chmod +x ml_pipeline/train_model.sh
chmod 755 models/
```

---

## What's Next?

After successful setup:

1. **Explore the Dashboard**
   - Check cluster statistics
   - View distribution charts

2. **Make Predictions**
   - Try different customer profiles
   - Observe cluster assignments

3. **View Visualization**
   - See 2D customer scatter plot
   - Understand cluster separation

4. **Read Documentation**
   - [README.md](README.md) - Complete documentation
   - [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) - Production deployment
   - [ARCHITECTURE.md](ARCHITECTURE.md) - System design
   - [TESTING.md](TESTING.md) - Testing procedures

5. **Customize**
   - Change number of clusters
   - Add new features
   - Modify UI

---

## API Quick Reference

### Get Statistics
```bash
curl http://localhost:8080/segmentation/getClusterStats
```

### Predict Customer Segment
```bash
curl -X POST http://localhost:8080/segmentation/predictSegment \
  -H "Content-Type: application/json" \
  -d '{"age": 35, "job": "management", "balance": 5000, ...}'
```

### Get Visualization Data
```bash
curl http://localhost:8080/segmentation/getVisualizationData
```

---

## Development Workflow

### Typical Development Cycle

1. **Modify ML Pipeline**
   ```bash
   nano ml_pipeline/customer_segmentation.py
   ```

2. **Retrain Model**
   ```bash
   cd ml_pipeline
   python3 customer_segmentation.py
   ```

3. **Update Web Interface**
   ```bash
   nano app/Views/segmentation/dashboard.php
   ```

4. **Test Changes**
   ```bash
   php -S localhost:8080 demo.php
   ```

5. **Verify Results**
   - Open browser
   - Test functionality
   - Check console for errors

---

## Sample Use Cases

### Use Case 1: Segment New Customer

**Scenario:** Bank has a new customer applying for a loan

**Steps:**
1. Go to /predict
2. Enter customer details:
   - Age: 42
   - Job: Technician
   - Balance: $2,500
   - Education: Secondary
3. Click "Predict Segment"
4. Review cluster assignment
5. Use profile for targeted marketing

### Use Case 2: Analyze Customer Base

**Scenario:** Marketing team wants to understand customer segments

**Steps:**
1. Go to Dashboard
2. View cluster distribution
3. Check cluster profiles
4. Go to Visualization page
5. Observe cluster patterns
6. Export data for further analysis

### Use Case 3: Retrain with New Data

**Scenario:** New customer data available

**Steps:**
1. Update `bank.csv` with new records
2. Go to Dashboard
3. Click "Train Model"
4. Wait for training to complete
5. View updated cluster statistics
6. Compare with previous results

---

## Performance Expectations

| Operation | Expected Time |
|-----------|---------------|
| Model Training | ~30 seconds |
| Single Prediction | <100ms |
| Page Load | <200ms |
| Dashboard Render | <300ms |
| API Response | <150ms |

---

## File Locations Reference

```
Quick access to important files:

Configuration:
  .env.example          - Environment settings

ML Pipeline:
  ml_pipeline/customer_segmentation.py  - Training
  ml_pipeline/predict_api.py            - Predictions

Web App:
  demo.php              - Standalone demo
  app/Controllers/Segmentation.php      - Main controller
  app/Views/segmentation/               - UI templates

Data:
  bank.csv              - Source data
  models/               - Trained models and output

Documentation:
  README.md             - Complete guide
  QUICKSTART.md         - This file
  ARCHITECTURE.md       - System design
```

---

## Getting Help

If you encounter issues:

1. **Check logs:**
   ```bash
   ls writable/logs/
   tail -f writable/logs/log-*.php
   ```

2. **Verify files exist:**
   ```bash
   ls models/
   ls bank.csv
   ```

3. **Test ML pipeline independently:**
   ```bash
   cd ml_pipeline
   python3 customer_segmentation.py
   ```

4. **Check documentation:**
   - README.md for detailed info
   - TESTING.md for test procedures
   - DEPLOYMENT_GUIDE.md for production setup

---

## Success Indicators

You know everything is working when:

✅ Model training completes without errors
✅ Three files created in `models/` directory
✅ Web server starts and responds
✅ Dashboard loads with statistics
✅ Prediction returns valid cluster (0-3)
✅ Visualization shows scatter plot
✅ No errors in browser console

---

## Quick Tips

💡 **Tip 1:** Start with the demo.php for quickest setup
💡 **Tip 2:** Train the model before using the web interface
💡 **Tip 3:** Use Chrome DevTools to debug API calls
💡 **Tip 4:** Check models/ directory for generated files
💡 **Tip 5:** Run predictions from command line first to verify ML works

---

## Next Steps After Setup

1. ✅ **Application is running** - You're ready to use it!

2. 📊 **Explore the data:**
   - Try different customer profiles
   - Observe cluster assignments
   - Export segmented data

3. 🎨 **Customize the application:**
   - Modify cluster count
   - Change visualization colors
   - Add new features

4. 🚀 **Deploy to production:**
   - Follow DEPLOYMENT_GUIDE.md
   - Set up proper web server
   - Configure security

5. 📚 **Learn more:**
   - Study the ML pipeline code
   - Understand the clustering algorithm
   - Explore CodeIgniter 4 features

---

## One-Line Commands

```bash
# Complete setup in one line
bash start.sh

# Or manual three-liner:
cd ml_pipeline && pip3 install -r requirements.txt && python3 customer_segmentation.py && cd .. && php -S localhost:8080 demo.php
```

---

## Congratulations! 🎉

You now have a fully functional customer segmentation system running on your machine!

**What you've built:**
- Machine learning model that segments 11,000+ customers
- Web interface for predictions and visualization
- API for programmatic access
- Complete production-ready application

**Ready to use for:**
- Customer analytics
- Targeted marketing
- Risk assessment
- Business intelligence

---

**Happy Segmenting!** 🚀

For more information, see [README.md](README.md)
