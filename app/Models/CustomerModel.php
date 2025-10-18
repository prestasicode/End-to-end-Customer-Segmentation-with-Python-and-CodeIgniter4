<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'age', 'job', 'marital', 'education', 'default', 'balance',
        'housing', 'loan', 'contact', 'day', 'month', 'duration',
        'campaign', 'pdays', 'previous', 'poutcome', 'deposit',
        'cluster', 'pca_1', 'pca_2'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all customers with pagination
     */
    public function getAllCustomers($limit = 100, $offset = 0)
    {
        return $this->findAll($limit, $offset);
    }

    /**
     * Get customers by cluster
     */
    public function getCustomersByCluster($clusterId)
    {
        return $this->where('cluster', $clusterId)->findAll();
    }

    /**
     * Get cluster distribution
     */
    public function getClusterDistribution()
    {
        // Try to read from segmented data file
        $segmentedFile = ROOTPATH . 'models/segmented_customers.csv';

        if (!file_exists($segmentedFile)) {
            return [];
        }

        $data = array_map('str_getcsv', file($segmentedFile));
        $header = array_shift($data);

        // Find cluster column index
        $clusterIndex = array_search('cluster', $header);

        if ($clusterIndex === false) {
            return [];
        }

        // Count clusters
        $distribution = [];
        foreach ($data as $row) {
            $cluster = intval($row[$clusterIndex]);
            if (!isset($distribution[$cluster])) {
                $distribution[$cluster] = 0;
            }
            $distribution[$cluster]++;
        }

        // Calculate percentages
        $total = count($data);
        $result = [];
        foreach ($distribution as $cluster => $count) {
            $result[] = [
                'cluster' => $cluster,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 2)
            ];
        }

        return $result;
    }

    /**
     * Import customers from CSV
     */
    public function importFromCSV($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }

        $data = array_map('str_getcsv', file($filepath));
        $header = array_shift($data);

        $imported = 0;
        foreach ($data as $row) {
            $customerData = array_combine($header, $row);

            // Insert or update
            if ($this->insert($customerData)) {
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Get customer statistics
     */
    public function getStatistics()
    {
        $segmentedFile = ROOTPATH . 'models/segmented_customers.csv';

        if (!file_exists($segmentedFile)) {
            return null;
        }

        $data = array_map('str_getcsv', file($segmentedFile));
        $header = array_shift($data);

        $totalCustomers = count($data);

        // Get age statistics
        $ageIndex = array_search('age', $header);
        $ages = array_column($data, $ageIndex);
        $avgAge = array_sum($ages) / count($ages);

        // Get balance statistics
        $balanceIndex = array_search('balance', $header);
        $balances = array_column($data, $balanceIndex);
        $avgBalance = array_sum($balances) / count($balances);

        // Get cluster count
        $clusterIndex = array_search('cluster', $header);
        $clusters = array_column($data, $clusterIndex);
        $uniqueClusters = count(array_unique($clusters));

        return [
            'total_customers' => $totalCustomers,
            'average_age' => round($avgAge, 1),
            'average_balance' => round($avgBalance, 2),
            'number_of_clusters' => $uniqueClusters
        ];
    }

    /**
     * Search customers
     */
    public function searchCustomers($keyword, $limit = 50)
    {
        return $this->like('job', $keyword)
            ->orLike('education', $keyword)
            ->orLike('marital', $keyword)
            ->findAll($limit);
    }
}
