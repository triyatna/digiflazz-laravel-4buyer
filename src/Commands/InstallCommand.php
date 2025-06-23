<?php

namespace Triyatna\Digiflazz\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ty-digiflazz:install';

    /**
     * Deskripsi dari console command.
     *
     * @var string
     */
    protected $description = 'Instalasi dan setup konfigurasi untuk paket Digiflazz Buyer API';

    /**
     * Eksekusi console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Memulai instalasi paket Digiflazz Buyer...');


        // Publikasi file konfigurasi
        $this->comment('Mempublikasikan file konfigurasi...');
        $this->call('vendor:publish', [
            '--provider' => 'Triyatna\Digiflazz\DigiflazzServiceProvider',
            '--tag' => 'digiflazz-config',
            '--force' => true,
        ]);
        $this->info('File konfigurasi telah dipublikasikan di config/digiflazz.php');

        // Tambahkan variabel ke file .env
        $this->comment('Menambahkan variabel environment Digiflazz ke file .env...');
        if ($this->addEnvironmentVariables()) {
            $this->info('Variabel environment Digiflazz berhasil ditambahkan.');
            $this->warn('Mohon isi nilai untuk variabel DIGIFLAZZ_* di file .env Anda.');
        } else {
            $this->info('Variabel environment Digiflazz sudah ada. Tidak ada yang ditambahkan.');
        }

        $this->newLine();
        $this->info('Instalasi paket Digiflazz Buyer selesai!');

        return self::SUCCESS;
    }

    /**
     * Menambahkan variabel environment ke file .env jika belum ada.
     *
     * @return bool
     */
    protected function addEnvironmentVariables(): bool
    {
        $envPath = $this->laravel->basePath('.env');

        if (!File::exists($envPath)) {
            $this->error('.env file not found!');
            return false;
        }
        $envContent = File::get($envPath);
        $variablesToAdd = [];
        $configKeys = [
            'DIGIFLAZZ_USERNAME' => '',
            'DIGIFLAZZ_API_KEY' => '',
            'DIGIFLAZZ_WEBHOOK_SECRET' => '',
        ];


        foreach ($configKeys as $key => $defaultValue) {
            // Cek apakah key sudah ada di file .env
            if (!Str::contains($envContent, $key . '=')) {
                $variablesToAdd[] = "{$key}={$defaultValue}\n";
            }
        }

        if (empty($variablesToAdd)) {
            return false;
        }
        $stringToAppend = "\n" . implode($variablesToAdd) . "\n";
        // $stringToAppend = "\n\n# Digiflazz Buyer API Configuration\n" . implode("\n", $variablesToAdd) . "\n";

        File::append($envPath, $stringToAppend);

        return true;
    }
}
