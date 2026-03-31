<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use PDO;

#[Signature('challenge:fetch-flag')]
#[Description('Fetch flag from challenge database via SSH tunnel')]
class FetchFlag extends Command
{
    public function handle(): int
    {
        // Step 1: Write SSH private key to temp file
        $keyPath = $this->writePrivateKey();
        if (!$keyPath) {
            $this->error('Failed to write SSH key.');
            return self::FAILURE;
        }

        // Step 2: Open SSH tunnel (local port 15432 → remote PostgreSQL)
        $this->info('Opening SSH tunnel...');
        $process = proc_open(
            'ssh -i "' . $keyPath . '" -L 15432:localhost:5432 -N -o StrictHostKeyChecking=no illuminate@illuminate.bitech.com.sa',
            [], $pipes
        );

        sleep(3);

        // Step 3: Connect to PostgreSQL through the tunnel
        $this->info('Connecting to database...');
        $pdo = new PDO(
            'pgsql:host=127.0.0.1;port=15432;dbname=illuminate_challenge',
            'candidate_019d3fbe-5d13-71c5-974c-5ea34e51d507',
            'QVHLfnYbyFCEDjyfumniIxeW'
        );

        // Step 4: Find the flag
        $stmt = $pdo->query("SELECT value FROM gis_data.config WHERE key = 'system_token'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->info('Flag: ' . $row['value']);

        proc_close($process);

        return self::SUCCESS;
    }

    private function writePrivateKey(): string|false
    {
        $key = "-----BEGIN OPENSSH PRIVATE KEY-----\nb3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW\nQyNTUxOQAAACDcxWeLGfhmZjjuYvxfyum9Cl+ag+Kuu1MY3aGXf1ORpAAAAKA7DmgJOw5o\nCQAAAAtzc2gtZWQyNTUxOQAAACDcxWeLGfhmZjjuYvxfyum9Cl+ag+Kuu1MY3aGXf1ORpA\nAAAEATd8Sz7zADTPqzEbVAzE++C+OqY3TtLo6GDkRtAsCTadzFZ4sZ+GZmOO5i/F/K6b0K\nX5qD4q67UxjdoZd/U5GkAAAAF3BocHNlY2xpYi1nZW5lcmF0ZWQta2V5AQIDBAUG\n-----END OPENSSH PRIVATE KEY-----\n";

        $keyPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ssh_key.pem';

        if (file_put_contents($keyPath, $key) === false) {
            return false;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            exec('icacls "' . $keyPath . '" /inheritance:r /grant:r "' . getenv('USERNAME') . ':R"');
        } else {
            chmod($keyPath, 0600);
        }
        
        return $keyPath;
    }
}
