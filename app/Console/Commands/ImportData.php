<?php

namespace App\Console\Commands;

use App\Models\Incident;
use App\Models\Neighborhood;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use PDO;

#[Signature('challenge:import-data')]
#[Description('Import neighborhoods and incidents from remote PostgreSQL into local SQLite')]
class ImportData extends Command
{
    public function handle(): int
    {
        $this->info('Connecting to remote database via SSH tunnel...');

        $pdo = new PDO(
            'pgsql:host=127.0.0.1;port=15432;dbname=illuminate_challenge',
            'candidate_019d3fbe-5d13-71c5-974c-5ea34e51d507',
            'QVHLfnYbyFCEDjyfumniIxeW'
        );

        // Import neighborhoods
        $this->info('Importing neighborhoods...');
        $neighborhoods = $pdo->query("SELECT * FROM gis_data.neighborhoods")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($neighborhoods as $row) {
            Neighborhood::updateOrCreate(
                ['remote_id' => $row['id']],
                [
                    'name'       => $row['name'],
                    'boundary'   => $row['boundary'],
                    'properties' => $row['properties'],
                ]
            );
        }

        $this->info('Imported ' . count($neighborhoods) . ' neighborhoods.');

        // Import incidents
        $this->info('Importing incidents...');
        $incidents = $pdo->query("SELECT * FROM gis_data.incidents")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($incidents as $row) {
            Incident::updateOrCreate(
                ['remote_id' => $row['id']],
                [
                    'location'    => $row['location'],
                    'metadata'    => $row['metadata'],
                    'tags'        => json_encode($row['tags'] ?? []),
                    'reports'     => $row['reports'],
                    'occurred_at' => $row['occurred_at'] ?? null,
                ]
            );
        }

        $this->info('Imported ' . count($incidents) . ' incidents.');
        $this->info('Done!');

        return self::SUCCESS;
    }
}
