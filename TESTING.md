# Testing Guide - Customer Segmentation Application

## Quick Test (End-to-End)

### 1. Test Machine Learning Pipeline

```bash
cd ml_pipeline

# Test model training
python3 customer_segmentation.py
```

**Expected Output:**
```
============================================================
Customer Segmentation Pipeline - Bank Marketing Data
============================================================
Loading data from ../bank.csv...
Data loaded: 11162 rows, 17 columns
...
Model trained successfully with 4 clusters
Cluster distribution: [2496 4983 3515  168]
...
Pipeline completed successfully!
```

**Verify Files Created:**
```bash
ls -lh ../models/
# Should show:
# - segmentation_model.pkl (53KB)
# - cluster_profiles.json (10KB)
# - segmented_customers.csv (1.3MB)
```

### 2. Test Prediction API

```bash
# Test single prediction
python3 predict_api.py '{"age": 35, "job": "management", "marital": "married", "education": "tertiary", "default": "no", "balance": 5000, "housing": "yes", "loan": "no", "contact": "cellular", "day": 15, "month": "may", "duration": 300, "campaign": 1, "pdays": -1, "previous": 0, "poutcome": "unknown", "deposit": "yes"}'
```

**Expected Output:**
```json
{
  "cluster": 1,
  "cluster_profile": {
    "cluster_id": 1,
    "size": 4983,
    "percentage": 44.64,
    ...
  },
  "pca_coordinates": {
    "x": 0.394,
    "y": 0.032
  },
  "distance_to_center": 2.611,
  "confidence": 0.277
}
```

### 3. Test Demo Web Application

```bash
cd ..
php -S localhost:8080 demo.php
```

Then open browser: **http://localhost:8080**

**Test Checklist:**
- [ ] Dashboard loads and shows statistics
- [ ] Cluster distribution is displayed
- [ ] Navigate to /predict
- [ ] Fill in prediction form
- [ ] Submit and get prediction result
- [ ] Download segmented CSV works

### 4. Test with CodeIgniter 4 (Full Setup)

If you have CodeIgniter 4 installed:

```bash
php spark serve
```

Visit: **http://localhost:8080/segmentation**

**Test All Pages:**
- [ ] Dashboard (`/segmentation`)
- [ ] Predict (`/segmentation/predict`)
- [ ] Visualize (`/segmentation/visualize`)
- [ ] Customers (`/segmentation/customers`)

**Test API Endpoints:**
```bash
# Get cluster stats
curl http://localhost:8080/segmentation/getClusterStats

# Predict segment
curl -X POST http://localhost:8080/segmentation/predictSegment \
  -H "Content-Type: application/json" \
  -d '{"age": 35, "job": "management", "marital": "married", "education": "tertiary", "default": "no", "balance": 5000, "housing": "yes", "loan": "no", "contact": "cellular", "day": 15, "month": "may", "duration": 300, "campaign": 1, "pdays": -1, "previous": 0, "poutcome": "unknown", "deposit": "yes"}'

# Get visualization data
curl http://localhost:8080/segmentation/getVisualizationData
```

---

## Unit Tests

### Python ML Pipeline Tests

Create `ml_pipeline/test_pipeline.py`:

```python
import unittest
from customer_segmentation import CustomerSegmentationPipeline
import pandas as pd

class TestSegmentationPipeline(unittest.TestCase):

    def setUp(self):
        self.pipeline = CustomerSegmentationPipeline(n_clusters=4)

    def test_load_data(self):
        df = self.pipeline.load_data('../bank.csv')
        self.assertIsNotNone(df)
        self.assertEqual(len(df), 11162)
        self.assertEqual(len(df.columns), 17)

    def test_preprocess_data(self):
        df = self.pipeline.load_data('../bank.csv')
        X = self.pipeline.preprocess_data(df)
        self.assertIsNotNone(X)
        self.assertEqual(len(X), 11162)

    def test_train(self):
        df = self.pipeline.load_data('../bank.csv')
        X = self.pipeline.preprocess_data(df)
        clusters = self.pipeline.train(X)
        self.assertEqual(len(clusters), 11162)
        self.assertTrue(all(0 <= c < 4 for c in clusters))

if __name__ == '__main__':
    unittest.main()
```

Run tests:
```bash
cd ml_pipeline
python3 test_pipeline.py
```

### Test Prediction API

```python
# ml_pipeline/test_predict.py
import json
from predict_api import SegmentationPredictor

predictor = SegmentationPredictor()

test_customer = {
    "age": 35,
    "job": "management",
    "marital": "married",
    "education": "tertiary",
    "default": "no",
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
}

result = predictor.predict(test_customer)
print(json.dumps(result, indent=2))

assert 'cluster' in result
assert 0 <= result['cluster'] < 4
print("✓ Prediction test passed")
```

---

## Performance Testing

### Model Training Performance

```bash
time python3 customer_segmentation.py
```

**Expected:** ~30 seconds for 11,162 records

### Prediction Performance

```python
# ml_pipeline/benchmark.py
import time
from predict_api import SegmentationPredictor

predictor = SegmentationPredictor()

test_data = {
    "age": 35,
    "job": "management",
    "marital": "married",
    "education": "tertiary",
    "default": "no",
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
}

# Warm up
predictor.predict(test_data)

# Benchmark
iterations = 100
start = time.time()

for _ in range(iterations):
    predictor.predict(test_data)

end = time.time()
avg_time = (end - start) / iterations

print(f"Average prediction time: {avg_time*1000:.2f}ms")
print(f"Predictions per second: {iterations/(end-start):.2f}")
```

**Expected:** < 10ms per prediction

### Load Testing

Using Apache Bench:
```bash
# Install Apache Bench
# macOS: comes with Apache
# Linux: apt-get install apache2-utils

# Test dashboard
ab -n 1000 -c 10 http://localhost:8080/

# Test prediction endpoint
ab -n 100 -c 5 -p test_data.json -T application/json http://localhost:8080/api/predict
```

---

## Integration Testing

### Test Complete Workflow

```bash
#!/bin/bash
# test_workflow.sh

echo "Testing complete workflow..."

# 1. Train model
echo "1. Training model..."
cd ml_pipeline
python3 customer_segmentation.py > /dev/null
if [ $? -eq 0 ]; then
    echo "✓ Model training successful"
else
    echo "✗ Model training failed"
    exit 1
fi

# 2. Test prediction
echo "2. Testing prediction..."
result=$(python3 predict_api.py '{"age": 35, "job": "management", "marital": "married", "education": "tertiary", "default": "no", "balance": 5000, "housing": "yes", "loan": "no", "contact": "cellular", "day": 15, "month": "may", "duration": 300, "campaign": 1, "pdays": -1, "previous": 0, "poutcome": "unknown", "deposit": "yes"}')

if echo "$result" | grep -q "cluster"; then
    echo "✓ Prediction successful"
else
    echo "✗ Prediction failed"
    exit 1
fi

# 3. Check files
echo "3. Checking output files..."
if [ -f "../models/segmentation_model.pkl" ] && [ -f "../models/cluster_profiles.json" ]; then
    echo "✓ All files created"
else
    echo "✗ Missing output files"
    exit 1
fi

cd ..

# 4. Start web server in background
echo "4. Testing web server..."
php -S localhost:8081 demo.php > /dev/null 2>&1 &
SERVER_PID=$!
sleep 2

# 5. Test HTTP endpoints
response=$(curl -s http://localhost:8081/)
if echo "$response" | grep -q "Customer Segmentation"; then
    echo "✓ Web server responding"
else
    echo "✗ Web server failed"
    kill $SERVER_PID
    exit 1
fi

# Cleanup
kill $SERVER_PID

echo ""
echo "=========================================="
echo "All tests passed! ✓"
echo "=========================================="
```

Run:
```bash
chmod +x test_workflow.sh
bash test_workflow.sh
```

---

## Test Data

### Sample Test Cases

**Test Case 1: Young Professional**
```json
{
  "age": 28,
  "job": "technician",
  "marital": "single",
  "education": "tertiary",
  "balance": 3000,
  "housing": "no",
  "loan": "no"
}
```

**Test Case 2: Retired Customer**
```json
{
  "age": 65,
  "job": "retired",
  "marital": "married",
  "education": "secondary",
  "balance": 10000,
  "housing": "yes",
  "loan": "no"
}
```

**Test Case 3: Low Balance**
```json
{
  "age": 35,
  "job": "services",
  "marital": "divorced",
  "education": "primary",
  "balance": 100,
  "housing": "yes",
  "loan": "yes"
}
```

---

## Troubleshooting Tests

### Issue: Module not found

**Solution:**
```bash
pip3 install pandas numpy scikit-learn
```

### Issue: Model file not found

**Solution:**
```bash
cd ml_pipeline
python3 customer_segmentation.py
```

### Issue: Permission denied

**Solution:**
```bash
chmod +x test_workflow.sh
chmod 755 models/
```

### Issue: Port already in use

**Solution:**
```bash
# Kill process using port
lsof -ti:8080 | xargs kill -9

# Or use different port
php -S localhost:8081 demo.php
```

---

## Continuous Integration

### GitHub Actions Example

`.github/workflows/test.yml`:

```yaml
name: Test Customer Segmentation

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v2

    - name: Set up Python
      uses: actions/setup-python@v2
      with:
        python-version: '3.10'

    - name: Install Python dependencies
      run: |
        cd ml_pipeline
        pip install -r requirements.txt

    - name: Test ML Pipeline
      run: |
        cd ml_pipeline
        python3 customer_segmentation.py

    - name: Test Prediction API
      run: |
        cd ml_pipeline
        python3 test_predict.py

    - name: Set up PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'

    - name: Test Web Server
      run: |
        php -S localhost:8080 demo.php &
        sleep 5
        curl http://localhost:8080/
```

---

## Test Results Reference

### Expected Model Performance

| Metric | Value |
|--------|-------|
| Training Time | ~30 seconds |
| Prediction Time | < 10ms |
| Model Size | ~53KB |
| Accuracy | N/A (unsupervised) |
| Clusters | 4 |

### Expected Cluster Distribution

| Cluster | Size | Percentage |
|---------|------|------------|
| 0 | ~2,500 | ~22% |
| 1 | ~5,000 | ~45% |
| 2 | ~3,500 | ~31% |
| 3 | ~170 | ~2% |

---

**Testing Complete!**

If all tests pass, your application is ready for deployment.
