<?php

require __DIR__ . '/vendor/autoload.php';

// Load .env file
$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\User;

// Create a test image in base64 format (1x1 pixel red PNG)
$base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==';

// Test base64 image upload (simulate what would happen in LogbookDataController)
$imageData = explode(',', $base64Image);
$imageData = isset($imageData[1]) ? $imageData[1] : $imageData[0];

$filename = 'logbook_' . time() . '_' . uniqid() . '.png';

// Store the image
Storage::disk('public')->put('logbook_images/' . $filename, base64_decode($imageData));

echo "Image uploaded successfully!\n";
echo "Filename: " . $filename . "\n";
echo "Path: storage/app/public/logbook_images/" . $filename . "\n";
echo "URL: " . url('/api/images/logbook/' . $filename) . "\n";

// Check if file exists
if (Storage::disk('public')->exists('logbook_images/' . $filename)) {
    echo "✓ File exists in storage\n";
} else {
    echo "✗ File does not exist in storage\n";
}

// Test file access
echo "\nTesting file access...\n";
try {
    $file = Storage::disk('public')->get('logbook_images/' . $filename);
    echo "✓ File can be read from storage\n";
    echo "File size: " . strlen($file) . " bytes\n";
} catch (Exception $e) {
    echo "✗ Error reading file: " . $e->getMessage() . "\n";
}
