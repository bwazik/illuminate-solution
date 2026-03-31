<?php

namespace App\Console\Commands;

use App\Models\Neighborhood;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('challenge:find-incidents')]
#[Description('Find incidents in donut around centroid of NB-7A2F and reveal the flag')]
class FindIncidents extends Command
{
    public function handle(): int
    {
        $neighborhood = Neighborhood::where('name', 'NB-7A2F')->firstOrFail();

        $incidents = $neighborhood->incidents()->get();

        $this->info('Found ' . $incidents->count() . ' incidents in donut:');

        $flag = '';
        foreach ($incidents as $incident) {
            $this->line("ID: {$incident->remote_id} | Distance: {$incident->distance_km} | Code: {$incident->code}");
            $flag .= $incident->code;
        }

        $this->info('Flag: ' . $flag);

        return self::SUCCESS;
    }
}
