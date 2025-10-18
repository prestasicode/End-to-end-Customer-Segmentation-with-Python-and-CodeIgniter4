#!/bin/bash
# Quick start script for Customer Segmentation Application

echo "==========================================="
echo "Customer Segmentation Application"
echo "==========================================="
echo ""

# Navigate to project directory
cd "$(dirname "$0")"

# Check Python
echo "Checking Python installation..."
if ! command -v python3 &> /dev/null; then
    echo "Error: Python3 is not installed"
    echo "Please install Python 3.8 or higher"
    exit 1
fi
echo "✓ Python3 found: $(python3 --version)"

# Check PHP
echo "Checking PHP installation..."
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed"
    echo "Please install PHP 8.1 or higher"
    exit 1
fi
echo "✓ PHP found: $(php --version | head -1)"

# Install Python dependencies
echo ""
echo "Installing Python dependencies..."
cd ml_pipeline
pip3 install -q -r requirements.txt
if [ $? -eq 0 ]; then
    echo "✓ Python dependencies installed"
else
    echo "⚠ Warning: Some Python packages may not be installed"
fi
cd ..

# Train model if not exists
if [ ! -f "models/segmentation_model.pkl" ]; then
    echo ""
    echo "Training machine learning model..."
    echo "This may take a few minutes..."
    cd ml_pipeline
    python3 customer_segmentation.py
    if [ $? -eq 0 ]; then
        echo "✓ Model trained successfully"
    else
        echo "✗ Model training failed"
        exit 1
    fi
    cd ..
else
    echo ""
    echo "✓ Model already trained"
fi

# Create .env if not exists
if [ ! -f ".env" ]; then
    echo ""
    echo "Creating .env file..."
    cp .env.example .env
    echo "✓ .env file created"
fi

# Start server
echo ""
echo "==========================================="
echo "Starting CodeIgniter 4 development server"
echo "==========================================="
echo ""
echo "Application will be available at:"
echo "➜  http://localhost:8080"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

php spark serve
