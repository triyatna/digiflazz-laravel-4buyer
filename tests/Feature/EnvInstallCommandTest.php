<?php

use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase;

class EnvInstallCommandTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [\Triyatna\DigiflazzBuyer\DigiflazzServiceProvider::class];
    }

    public function test_inserts_missing_keys()
    {
        $fs = new Filesystem();
        $tmp = base_path('.env');
        $fs->put($tmp, "APP_NAME=Laravel\nAPP_ENV=local\n");
        $this->artisan('digiflazz:install-env')->assertExitCode(0);
        $content = $fs->get($tmp);
        $this->assertStringContainsString('DIGIFLAZZ_USERNAME=', $content);
        $this->assertStringContainsString('DIGIFLAZZ_API_KEY=', $content);
        $this->assertStringContainsString('DIGIFLAZZ_WEBHOOK_SECRET=', $content);
    }
}
