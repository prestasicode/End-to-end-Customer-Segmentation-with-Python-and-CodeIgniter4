<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use CodeIgniter\Controller;

class Segmentation extends Controller
{
    protected $customerModel;
    protected $pythonPath;
    protected $scriptPath;
    protected $modelPath;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();

        // Set paths
        $this->pythonPath = 'python3';
        $this->scriptPath = ROOTPATH . 'ml_pipeline/predict_api.py';
        $this->modelPath = ROOTPATH . 'models/';
    }

    /**
     * Dashboard - Main page
     */
    public function index()
    {
        $data = [
            'title' => 'Customer Segmentation Dashboard',
            'active_menu' => 'dashboard'
        ];

        return view('segmentation/dashboard', $data);
    }

    /**
     * View all customers with their segments
     */
    public function customers()
    {
        $customers = $this->customerModel->getAllCustomers();

        $data = [
            'title' => 'Customer Segments',
            'active_menu' => 'customers',
            'customers' => $customers
        ];

        return view('segmentation/customers', $data);
    }

    /**
     * Single customer prediction form
     */
    public function predict()
    {
        $data = [
            'title' => 'Predict Customer Segment',
            'active_menu' => 'predict'
        ];

        return view('segmentation/predict', $data);
    }

    /**
     * API: Get cluster statistics
     */
    public function getClusterStats()
    {
        try {
            $profilesPath = $this->modelPath . 'cluster_profiles.json';

            if (!file_exists($profilesPath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Model not trained yet. Please train the model first.'
                ]);
            }

            $profiles = json_decode(file_get_contents($profilesPath), true);

            // Get customer distribution
            $distribution = $this->customerModel->getClusterDistribution();

            return $this->response->setJSON([
                'success' => true,
                'profiles' => $profiles,
                'distribution' => $distribution
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Predict segment for a customer
     */
    public function predictSegment()
    {
        try {
            // Get input data
            $input = $this->request->getJSON(true);

            if (empty($input)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No input data provided'
                ]);
            }

            // Prepare data for Python script
            $jsonInput = json_encode($input);

            // Execute Python prediction script
            $command = sprintf(
                '%s %s %s 2>&1',
                escapeshellcmd($this->pythonPath),
                escapeshellarg($this->scriptPath),
                escapeshellarg($jsonInput)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Prediction failed',
                    'error' => implode("\n", $output)
                ]);
            }

            // Parse output
            $result = json_decode(implode("\n", $output), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid prediction output',
                    'output' => implode("\n", $output)
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'prediction' => $result
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Batch prediction
     */
    public function predictBatch()
    {
        try {
            $file = $this->request->getFile('file');

            if (!$file || !$file->isValid()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid file upload'
                ]);
            }

            // Read CSV file
            $filepath = $file->getTempName();
            $data = array_map('str_getcsv', file($filepath));
            $header = array_shift($data);

            // Convert to array of objects
            $customers = [];
            foreach ($data as $row) {
                $customers[] = array_combine($header, $row);
            }

            // Prepare for Python
            $jsonInput = json_encode($customers);

            // Execute prediction
            $command = sprintf(
                '%s %s %s 2>&1',
                escapeshellcmd($this->pythonPath),
                escapeshellarg($this->scriptPath),
                escapeshellarg($jsonInput)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Batch prediction failed',
                    'error' => implode("\n", $output)
                ]);
            }

            $result = json_decode(implode("\n", $output), true);

            return $this->response->setJSON([
                'success' => true,
                'predictions' => $result,
                'total' => count($result)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Visualization page
     */
    public function visualize()
    {
        $data = [
            'title' => 'Cluster Visualization',
            'active_menu' => 'visualize'
        ];

        return view('segmentation/visualize', $data);
    }

    /**
     * API: Get visualization data
     */
    public function getVisualizationData()
    {
        try {
            $segmentedDataPath = $this->modelPath . 'segmented_customers.csv';

            if (!file_exists($segmentedDataPath)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No segmented data available'
                ]);
            }

            // Read CSV
            $data = array_map('str_getcsv', file($segmentedDataPath));
            $header = array_shift($data);

            // Get sample for visualization (limit to 1000 points for performance)
            $sample = array_slice($data, 0, 1000);

            $visualData = [];
            foreach ($sample as $row) {
                $rowData = array_combine($header, $row);
                $visualData[] = [
                    'pca_1' => floatval($rowData['pca_1'] ?? 0),
                    'pca_2' => floatval($rowData['pca_2'] ?? 0),
                    'cluster' => intval($rowData['cluster'] ?? 0),
                    'age' => intval($rowData['age'] ?? 0),
                    'balance' => floatval($rowData['balance'] ?? 0)
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $visualData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Train/Retrain model
     */
    public function trainModel()
    {
        try {
            $trainScript = ROOTPATH . 'ml_pipeline/train_model.sh';

            if (!file_exists($trainScript)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Training script not found'
                ]);
            }

            // Execute training script in background
            $command = sprintf('bash %s > %s 2>&1 &',
                escapeshellarg($trainScript),
                escapeshellarg(ROOTPATH . 'models/training.log')
            );

            exec($command);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Training started. Check logs for progress.'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
