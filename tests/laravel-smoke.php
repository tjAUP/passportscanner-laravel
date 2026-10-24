<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TjAup\PassportScanner\Laravel\AssetController;

if ($argc !== 2 || !is_file($argv[1] . '/bootstrap/app.php')) {
    fwrite(STDERR, "Usage: php tests/laravel-smoke.php /path/to/laravel/app\n");
    exit(2);
}

require $argv[1] . '/vendor/autoload.php';
$app = require $argv[1] . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$scriptUrl = '/_passportscanner/assets/passport-scanner.js';
$script = $kernel->handle(Request::create($scriptUrl));

if (!$script instanceof BinaryFileResponse || $script->getStatusCode() !== 200 ||
    basename((string) $script->getFile()) !== 'passport-scanner.js' ||
    strpos($script->headers->get('Content-Type', ''), 'text/javascript') !== 0) {
    throw new RuntimeException('The Laravel asset route did not serve the scanner script.');
}

$worker = $kernel->handle(Request::create('/_passportscanner/assets/ocr/worker.min.js'));
if (!$worker instanceof BinaryFileResponse || $worker->getStatusCode() !== 200 ||
    strpos($worker->headers->get('Content-Type', ''), 'text/javascript') !== 0) {
    throw new RuntimeException('The Laravel asset route did not serve the OCR worker as JavaScript.');
}

$ocrUrl = '/_passportscanner/assets/ocr/lang/eng.traineddata.gz';
$ocr = $kernel->handle(Request::create($ocrUrl));
if (!$ocr instanceof BinaryFileResponse || $ocr->getStatusCode() !== 200 ||
    basename((string) $ocr->getFile()) !== 'eng.traineddata.gz' ||
    $ocr->headers->has('Content-Encoding')) {
    throw new RuntimeException('The Laravel asset route did not serve the binary OCR data correctly.');
}

$rendered = Blade::render('@passportScannerAssets');
if (strpos($rendered, '<script src=') === false ||
    strpos($rendered, $scriptUrl) === false ||
    strpos($rendered, ' defer></script>') === false) {
    throw new RuntimeException('The Blade directive did not render the scanner script.');
}

try {
    (new AssetController())->show('../composer.json');
    throw new RuntimeException('The asset controller allowed a path outside dist.');
} catch (NotFoundHttpException $error) {
    // Expected.
}

$kernel->terminate(Request::create($scriptUrl), $script);
echo "Laravel asset route, OCR data, Blade directive, and path guard passed\n";
