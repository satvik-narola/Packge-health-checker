<?php

namespace Itzdevsatvik\PackageHealthChecker\Console\Commands;

use Illuminate\Console\Command;
use Itzdevsatvik\PackageHealthChecker\Models\PackageHealth;

class CheckPackageHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-health:check {--no-cache : Force a fresh check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all installed packages for deprecated code, security vulnerabilities, and compatibility issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting package health check...');

        // Check if we should use cache
        $useCache = !$this->option('no-cache') && config('packagehealthchecker.cache.enabled', true);

        if ($useCache) {
            $cachedResult = PackageHealth::latest()->first();
            if ($cachedResult && $cachedResult->created_at->diffInSeconds(now()) < config('packagehealthchecker.cache.duration', 3600)) {
                $this->info('Using cached results from ' . $cachedResult->created_at->diffForHumans());
                $results = $cachedResult->results;
            } else {
                $results = \Itzdevsatvik\PackageHealthChecker\PackageHealthChecker::checkAll();
            }
        } else {
            $results = \Itzdevsatvik\PackageHealthChecker\PackageHealthChecker::checkAll();
        }

        // Display results
        $this->displayResults($results);

        // Store results
        PackageHealth::create([
            'results' => $results,
            'scan_time' => now(),
        ]);

        $this->info('Package health check completed!');
    }

    /**
     * Display results in a table.
     */
    protected function displayResults($results)
    {
        $tableData = [];

        foreach ($results as $package) {
            $status = match ($package['status']) {
                'healthy' => '<fg=green>✓ Healthy</>',
                'warning' => '<fg=yellow>⚠ Warning</>',
                'critical' => '<fg=red>✗ Critical</>',
                default => '<fg=gray>Unknown</>'
            };

            $issues = implode("\n", $package['issues'] ?? []);

            $tableData[] = [
                $package['name'],
                $package['version'],
                $status,
                $issues ?: 'No issues',
            ];
        }

        $this->table(['Package', 'Version', 'Status', 'Issues'], $tableData);
    }
}