<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('challenge:submit-repo {repo_url} {cv_path}')]
#[Description('Submit GitHub repo and CV to the challenge API')]

class SubmitRepo extends Command
{
    public function handle(): int
    {
        $repoUrl = $this->argument('repo_url');
        $cvPath  = $this->argument('cv_path');

        if (!file_exists($cvPath)) {
            $this->error('CV file not found: ' . $cvPath);
            return self::FAILURE;
        }

        $this->info('Submitting repo and CV...');

        $response = Http::withToken('8043df41-400c-4c9f-ba5e-10930964a404')
            ->withOptions(['verify' => false])
            ->attach('cv', file_get_contents($cvPath), basename($cvPath))
            ->post('https://illuminate.bitech.com.sa/api/challenge/submit-repo', [
                'repo_url' => $repoUrl,
            ]);

        if ($response->successful()) {
            $this->info('Submitted successfully!');
            $this->line($response->body());
        } else {
            $this->error('Failed: ' . $response->body());
        }

        return self::SUCCESS;
    }
}
