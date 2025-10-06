<?php

namespace Triyatna\DigiflazzBuyer\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallEnvCommand extends Command
{
    protected $signature = 'digiflazz:install';
    protected $description = 'Ensure Digiflazz env keys exist and are grouped in .env';

    public function handle(): int
    {
        $fs = new Filesystem();
        $path = base_path('.env');

        if (!$fs->exists($path)) {
            $fs->put($path, implode(PHP_EOL, [
                'DIGIFLAZZ_USERNAME=',
                'DIGIFLAZZ_API_KEY=',
                'DIGIFLAZZ_WEBHOOK_SECRET=',
                '',
            ]));
            $this->info('Created .env with Digiflazz keys.');
            return self::SUCCESS;
        }

        $content = $fs->get($path);
        $lines = preg_split('/\r\n|\n|\r/', $content);

        // Detect existing keys
        $existing = [];
        foreach ($lines as $line) {
            if (preg_match('/^\s*(DIGIFLAZZ_(USERNAME|API_KEY|WEBHOOK_SECRET))\s*=/', $line, $m)) {
                $existing[$m[1]] = true;
            }
        }

        $allKeys = ['DIGIFLAZZ_USERNAME', 'DIGIFLAZZ_API_KEY', 'DIGIFLAZZ_WEBHOOK_SECRET'];
        $toAdd = [];
        foreach ($allKeys as $k) {
            if (!isset($existing[$k])) {
                $toAdd[] = $k . '=';
            }
        }

        if (empty($toAdd)) {
            $this->info('All Digiflazz env keys already exist. Nothing to do.');
            return self::SUCCESS;
        }

        // Find insertion point: near existing Digiflazz header or env lines
        $insertStart = null;
        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match('/^\s*##\s*Digiflazz/i', (string) $lines[$i]) || preg_match('/^\s*DIGIFLAZZ_/', (string) $lines[$i])) {
                $insertStart = $i;
                break;
            }
        }

        if ($insertStart !== null) {
            // Extend to the end of the contiguous Digiflazz block
            $j = $insertStart;
            while ($j < count($lines)) {
                $cur = (string) $lines[$j];
                if (
                    preg_match('/^\s*##\s*Digiflazz/i', $cur) ||
                    preg_match('/^\s*DIGIFLAZZ_/', $cur) ||
                    trim($cur) === ''
                ) {
                    $j++;
                    continue;
                }
                break;
            }

            // Make sure header exists inside the block
            $hasHeader = false;
            for ($k = $insertStart; $k < $j; $k++) {
                if (preg_match('/^\s*##\s*Digiflazz/i', (string) $lines[$k])) {
                    $hasHeader = true;
                    break;
                }
            }
            if (!$hasHeader) {
                array_splice($lines, $insertStart, 0, ['## Digiflazz']);
                $j++;
            }

            // Insert missing keys at the end of the block
            array_splice($lines, $j, 0, $toAdd);
        } else {
            // Append with a blank line if the last line is not blank
            if (count($lines) > 0) {
                $last = $lines[count($lines) - 1];
                if (trim((string) $last) !== '') {
                    $lines[] = '';
                }
            }
            $lines[] = '## Digiflazz';
            foreach ($toAdd as $add) {
                $lines[] = $add;
            }
        }

        // Write back (ensure trailing newline)
        $new = implode(PHP_EOL, $lines);
        if (!str_ends_with($new, PHP_EOL)) {
            $new .= PHP_EOL;
        }
        $fs->put($path, $new);

        $this->info('Inserted missing Digiflazz env keys.');
        return self::SUCCESS;
    }
}
