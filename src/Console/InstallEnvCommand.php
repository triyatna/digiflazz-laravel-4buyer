<?php

namespace Triyatna\DigiflazzBuyer\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallEnvCommand extends Command
{
    protected $signature = 'digiflazz:install-env';
    protected $description = 'Ensure DIGIFLAZZ_* keys exist in your .env with proper grouping and spacing.';

    public function handle(): int
    {
        $fs = new Filesystem();
        $envPath = base_path('.env');
        if (!$fs->exists($envPath)) {
            $this->error('.env not found');
            return self::FAILURE;
        }

        $content = $fs->get($envPath);
        $lines = preg_split("/(\r\n|\n|\r)/", $content);

        $keys = ['DIGIFLAZZ_USERNAME', 'DIGIFLAZZ_API_KEY', 'DIGIFLAZZ_WEBHOOK_SECRET'];
        $present = array_fill_keys($keys, false);

        foreach ($lines as $i => $line) {
            foreach ($keys as $k) {
                if (preg_match('/^'.preg_quote($k, '/').'=/', $line)) {
                    $present[$k] = true;
                }
            }
        }

        $missing = array_values(array_filter($keys, fn($k) => !$present[$k]));
        if (empty($missing)) {
            $this->info('All DIGIFLAZZ_* keys already present.');
            return self::SUCCESS;
        }

        $start = null; $end = null;
        foreach ($lines as $i => $line) {
            if (preg_match('/^DIGIFLAZZ_/', $line)) {
                if ($start === null) $start = $i;
                $end = $i;
            }
        }

        $insertion = array_map(fn($k) => $k.'=', $missing);

        if ($start !== null) {
            $insertPos = $end + 1;
            if ($insertPos < count($lines) && $lines[$insertPos] !== '') {
                array_splice($lines, $insertPos, 0, ['']);
                $insertPos++;
            }
            array_splice($lines, $insertPos, 0, $insertion);
        } else {
            if (count($lines) > 0 and $lines[-1:] != ['']):
                $lines[] = '';
            endif;
            $lines[] = '## Digiflazz';
            foreach ($insertion as $line) $lines[] = $line;
        }

        $new = implode(PHP_EOL, $lines);
        if (!str_ends_with($new, PHP_EOL)) $new .= PHP_EOL;
        $fs->put($envPath, $new);
        $this->info('Inserted missing keys: '.implode(', ', $missing));
        return self::SUCCESS;
    }
}
