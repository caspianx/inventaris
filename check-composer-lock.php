<?php
$lock = json_decode(file_get_contents('composer.lock'), true);

echo "=== Checking PHP Requirements ===\n\n";

$found = false;

// Check main packages
if (isset($lock['packages'])) {
    foreach ($lock['packages'] as $pkg) {
        if (isset($pkg['require']['php']) && strpos($pkg['require']['php'], '8.3') !== false) {
            echo $pkg['name'] . ': ' . $pkg['require']['php'] . "\n";
            $found = true;
        }
    }
}

// Check dev packages
if (isset($lock['packages-dev'])) {
    foreach ($lock['packages-dev'] as $pkg) {
        if (isset($pkg['require']['php']) && strpos($pkg['require']['php'], '8.3') !== false) {
            echo $pkg['name'] . ': ' . $pkg['require']['php'] . "\n";
            $found = true;
        }
    }
}

if (!$found) {
    echo "No packages require PHP 8.3\n";
}

// Check platform require
if (isset($lock['platform'])) {
    echo "\n=== Platform Requirements ===\n";
    print_r($lock['platform']);
}
