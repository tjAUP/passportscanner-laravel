<?php

namespace TjAup\PassportScanner\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PassportScannerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        Blade::directive('passportScannerAssets', static function () {
            return '<?php echo \\TjAup\\PassportScanner\\Laravel\\PassportScannerAssets::scriptTag(); ?>';
        });
    }
}
