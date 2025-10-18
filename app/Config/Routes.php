<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Segmentation::index');

// Segmentation routes
$routes->group('segmentation', function($routes) {
    // Pages
    $routes->get('/', 'Segmentation::index');
    $routes->get('dashboard', 'Segmentation::index');
    $routes->get('customers', 'Segmentation::customers');
    $routes->get('predict', 'Segmentation::predict');
    $routes->get('visualize', 'Segmentation::visualize');

    // API endpoints
    $routes->post('predictSegment', 'Segmentation::predictSegment');
    $routes->post('predictBatch', 'Segmentation::predictBatch');
    $routes->get('getClusterStats', 'Segmentation::getClusterStats');
    $routes->get('getVisualizationData', 'Segmentation::getVisualizationData');
    $routes->post('trainModel', 'Segmentation::trainModel');
});
