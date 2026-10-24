<?php

namespace TjAup\PassportScanner\Laravel;

class AssetController
{
    public function show(string $path)
    {
        $dist = realpath(PassportScannerAssets::distPath());
        $file = $dist === false ? false : realpath($dist . DIRECTORY_SEPARATOR . $path);

        if ($dist === false || $file === false ||
            strpos($file, $dist . DIRECTORY_SEPARATOR) !== 0 || !is_file($file)) {
            abort(404);
        }

        $headers = [
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ];
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($extension === 'js') {
            $headers['Content-Type'] = 'text/javascript; charset=utf-8';
        } elseif ($extension === 'wasm') {
            $headers['Content-Type'] = 'application/wasm';
        } elseif ($extension === 'gz') {
            $headers['Content-Type'] = 'application/gzip';
        }

        return response()->file($file, $headers);
    }
}
