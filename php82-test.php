<?php
// Test PHP Compatibility
echo "=== PHP 8.2 Compatibility Test ===\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Major Version: " . PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION . "\n\n";

// Check requirements
$phpVersion = phpversion();
$required = "8.2";

if (version_compare($phpVersion, $required, '>=')) {
    echo "✅ PHP Version compatible (requires >= {$required})\n\n";
} else {
    echo "❌ PHP Version NOT compatible (requires >= {$required})\n\n";
    exit(1);
}

// Test Autoloader
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Composer Autoloader loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Failed to load Composer Autoloader: " . $e->getMessage() . "\n";
    exit(1);
}

// Test Laravel
try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "✅ Laravel Bootstrap loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Failed to load Laravel Bootstrap: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== All Tests Passed! ===\n";
echo "Application is ready for PHP 8.2+\n";
