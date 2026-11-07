<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\CustomCakeController;
use App\Http\Controllers\CatalogController;

echo "=== TESTING UPLOAD FUNCTIONALITY ===" . PHP_EOL;

// Create dummy image file
$tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_upload_' . time() . '.jpg';
$img = imagecreatetruecolor(200, 200);
$bg = imagecolorallocate($img, 222, 27, 84);
imagefilledrectangle($img, 0, 0, 200, 200, $bg);
imagejpeg($img, $tmpPath);
imagedestroy($img);

echo "Created temp image at: $tmpPath (" . filesize($tmpPath) . " bytes)" . PHP_EOL;

// 1. Test Custom Cake upload
$file1 = new UploadedFile($tmpPath, 'sample_cake.jpg', 'image/jpeg', null, true);
$req1 = Request::create('/api/custom-cakes/upload-image', 'POST', [], [], ['image' => $file1]);

$cakeCtrl = new CustomCakeController();
try {
    $res1 = $cakeCtrl->uploadImage($req1);
    echo "1. Custom Cake Upload: HTTP " . $res1->status() . " -> " . $res1->getContent() . PHP_EOL;
} catch (Exception $e) {
    echo "1. Custom Cake Upload FAILED: " . $e->getMessage() . PHP_EOL;
}

// 2. Test Product upload
$file2 = new UploadedFile($tmpPath, 'sample_product.jpg', 'image/jpeg', null, true);
$req2 = Request::create('/api/admin/products/upload-image', 'POST', [], [], ['image' => $file2]);

$catCtrl = new CatalogController();
try {
    $res2 = $catCtrl->uploadImage($req2);
    echo "2. Product Upload: HTTP " . $res2->status() . " -> " . $res2->getContent() . PHP_EOL;
} catch (Exception $e) {
    echo "2. Product Upload FAILED: " . $e->getMessage() . PHP_EOL;
}

@unlink($tmpPath);
echo "=== TEST COMPLETED ===" . PHP_EOL;
