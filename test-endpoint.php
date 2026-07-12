<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Create a simulated request to test the endpoint
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;

// Get the current authenticated user (simulate one if needed)
$user = \App\Models\User::first();
if ($user) {
    auth()->login($user);
    echo "Logged in as: " . $user->name . "\n";
    echo "User role: " . $user->role . "\n\n";
} else {
    echo "No user found in database\n";
    exit;
}

// Call the controller method directly
$controller = new DashboardController();
$response = $controller->incomeTrendData();

// Get the JSON content
$data = json_decode($response->getContent(), true);

echo "Response Data:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// Test if the structure matches what JavaScript expects
echo "\n\nStructure validation:\n";
foreach (['daily', 'monthly', 'yearly'] as $type) {
    if (isset($data[$type])) {
        echo "✓ $type has: ";
        echo implode(', ', array_keys($data[$type]));
        echo "\n";
    }
}
