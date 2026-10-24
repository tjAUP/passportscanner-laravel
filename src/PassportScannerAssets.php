<?php

namespace TjAup\PassportScanner\Laravel;

use Composer\InstalledVersions;

class PassportScannerAssets
{
    public static function distPath(): string
    {
        return InstalledVersions::getInstallPath('tjaup/passportscanner') . '/dist';
    }

    public static function scriptTag(): string
    {
        $url = route('passportscanner.asset', ['path' => 'passport-scanner.js']);

        return '<script src="' . e($url) . '" defer></script>';
    }
}
