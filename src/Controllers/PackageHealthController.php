<?php

namespace Itzdevsatvik\PackageHealthChecker\Controllers;

use Symfony\Component\Process\Process;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Itzdevsatvik\PackageHealthChecker\Models\PackageHealth;

class PackageHealthController extends Controller
{
    /**
     * Display the package health dashboard.
     */
    public function index()
    {
        // Check if access is restricted
        if (config('packagehealthchecker.dashboard.restrict_access', true)) {
            $this->checkAccess();
        }

        $lastScan = PackageHealth::latest()->first();

        return view('packagehealthchecker::dashboard', compact('lastScan'));
    }

    /**
     * Perform a new scan and return results.
     */
    public function scan(Request $request)
    {
        // Check if access is restricted
        if (config('packagehealthchecker.dashboard.restrict_access', true)) {
            $this->checkAccess();
        }

        // Start the console command in the background so the HTTP request returns fast.
        // Windows: use 'start /B'; *nix: run with & to background.
        try {
            if (stripos(PHP_OS, 'WIN') === 0) {
                $process = new Process(['cmd', '/c', 'start', '/B', 'php', 'artisan', 'package-health:check']);
            } else {
                $process = Process::fromShellCommandline('php artisan package-health:check > /dev/null 2>&1 &');
            }
            $process->setTimeout(null);
            $process->start();
        } catch (\Throwable $e) {
            \Log::error('Failed to start background scan: '.$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not start background scan.'], 500);
        }

        // Return immediately — UI will be responsive; check results after job finishes
        return response()->json(['success' => true, 'message' => 'Scan started in background.']);
    }

    /**
     * Check if the current user has access to the dashboard.
     */
    private function checkAccess()
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized access to package health dashboard');
        }

        $allowedEmails = config('packagehealthchecker.dashboard.allowed_emails', []);
        $allowedDomains = config('packagehealthchecker.dashboard.allowed_domains', []);

        if (!empty($allowedEmails) && !in_array($user->email, $allowedEmails)) {
            abort(403, 'Your email is not authorized to access this dashboard');
        }

        if (!empty($allowedDomains)) {
            $emailDomain = substr(strrchr($user->email, "@"), 1);
            if (!in_array($emailDomain, $allowedDomains)) {
                abort(403, 'Your domain is not authorized to access this dashboard');
            }
        }
    }
}