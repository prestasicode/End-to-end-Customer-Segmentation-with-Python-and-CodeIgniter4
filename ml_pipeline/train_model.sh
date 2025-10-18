#!/bin/bash
# Training script for customer segmentation model

echo "==============================================="
echo "Customer Segmentation Model Training"
echo "==============================================="

# Navigate to ml_pipeline directory
cd "$(dirname "$0")"

# Check if Python is available
if ! command -v python3 &> /dev/null; then
    echo "Error: Python3 is not installed"
    exit 1
fi

# Install requirements if needed
echo "Checking dependencies..."
pip3 install -q -r requirements.txt

# Run training pipeline
echo ""
echo "Starting training pipeline..."
python3 customer_segmentation.py

# Check if training was successful
if [ $? -eq 0 ]; then
    echo ""
    echo "==============================================="
    echo "Training completed successfully!"
    echo "Model saved in ../models/"
    echo "==============================================="
else
    echo ""
    echo "Error: Training failed"
    exit 1
fi
