<?php

namespace Itzdevsatvik\PackageHealthChecker;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class PackageHealthChecker
{
    /**
     * Check all packages for issues.
     */
    public static function checkAll()
    {
        $composerLock = json_decode(file_get_contents(base_path('composer.lock')), true);
        $packages = $composerLock['packages'] ?? [];
        $devPackages = $composerLock['packages-dev'] ?? [];
        $allPackages = array_merge($packages, $devPackages);

        $results = [];

        foreach ($allPackages as $package) {
            $results[] = self::checkPackage($package);
        }

        return $results;
    }

    /**
     * Check a single package for issues.
     */
    public static function checkPackage($package)
    {
        $result = [
            'name' => $package['name'],
            'version' => $package['version'],
            'status' => 'healthy',
            'issues' => [],
            'suggestions' => [],
        ];

        // Check for deprecated packages
        if (config('packagehealthchecker.features.deprecated_check', true)) {
            $deprecated = self::checkDeprecated($package);
            if ($deprecated) {
                $result['issues'][] = 'Package is marked as deprecated';
                $result['status'] = 'critical';
            }
        }

        // Check for security vulnerabilities
        if (config('packagehealthchecker.features.security_check', true)) {
            try {
                $securityIssues = self::checkSecurity($package);
                if (!empty($securityIssues)) {
                    $result['issues'] = array_merge($result['issues'], $securityIssues);
                    $result['status'] = 'critical';
                }
            } catch (\Exception $e) {
                // Log the error but don't mark as critical for API failures
                \Log::warning('Security check failed for ' . $package['name'] . ': ' . $e->getMessage());
                $result['issues'][] = 'Security check temporarily unavailable';
                // Only set to warning, not critical, for API failures
                if ($result['status'] === 'healthy') {
                    $result['status'] = 'warning';
                }
            }
        }

        // Check Laravel compatibility
        if (config('packagehealthchecker.features.compatibility_check', true)) {
            $compatibilityIssues = self::checkCompatibility($package);
            if (!empty($compatibilityIssues)) {
                $result['issues'] = array_merge($result['issues'], $compatibilityIssues);
                if ($result['status'] !== 'critical') {
                    $result['status'] = 'warning';
                }
            }
        }

        // Add suggestions if there are issues
        if (!empty($result['issues'])) {
            $result['suggestions'] = self::getSuggestions($package, $result['issues']);
        }

        return $result;
    }

    /**
     * Check if a package is deprecated.
     */
    protected static function checkDeprecated($package)
    {
        return $package['abandoned'] ?? false;
    }

    /**
     * Check for security vulnerabilities.
     */
    protected static function checkSecurity($package)
    {
        $issues = [];

        try {
            // Use the official FriendsOfPHP security advisories database
            $client = new Client(['timeout' => config('packagehealthchecker.security.timeout', 15)]);

            // Check against FriendsOfPHP security database
            $url = 'https://raw.githubusercontent.com/FriendsOfPHP/security-advisories/master/database/'
                . str_replace('/', '%2F', $package['name']) . '.json';
            $response = $client->get($url);

            if ($response->getStatusCode() === 200) {
                $advisories = json_decode($response->getBody(), true) ?: [];

                foreach ($advisories as $advisory) {
                    if (
                        is_array($advisory) && isset($advisory['affectedVersions']) &&
                        self::isVersionAffected($package['version'], $advisory['affectedVersions'])
                    ) {

                        $title = $advisory['title'] ?? 'Unknown vulnerability';
                        $cve = $advisory['cve'] ?? 'Unknown';

                        $issues[] = "Security vulnerability: {$title} (CVE: {$cve})";
                    }
                }

            }
            // If we get a 404, it means no advisories exist for this package (which is good)
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                // No security advisories found for this package - this is good!
                return [];
            }
            \Log::error('Security check failed for ' . $package['name'] . ': ' . $e->getMessage());
            $issues[] = 'Could not complete security check due to an API error';
        } catch (\Exception $e) {
            \Log::error('Security check failed: ' . $e->getMessage());
            $issues[] = 'Could not complete security check due to an API error';
        }

        return $issues;
    }

    protected static function isVersionAffected($version, $versionConstraint)
    {
        // Simple version check - in a real implementation, use composer/semver
        try {
            // Remove 'v' prefix if present
            $version = ltrim($version, 'v');

            // This is a simplified check - consider using composer/semver for proper constraint checking
            return version_compare($version, $versionConstraint, '>=');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check Laravel compatibility.
     */
    protected static function checkCompatibility($package)
    {
        $issues = [];
        $laravelVersion = app()->version();

        // This is a simplified check - in a real implementation, you'd parse
        // the composer.json requirements more carefully
        if (isset($package['require']['laravel/framework'])) {
            $required = $package['require']['laravel/framework'];

            // Simple check for major version compatibility
            $laravelMajor = explode('.', $laravelVersion)[0];
            if (
                !preg_match("/\^{$laravelMajor}\.\d+/", $required) &&
                !preg_match("/~{$laravelMajor}\.\d+/", $required) &&
                !preg_match("/{$laravelMajor}\.\d+/", $required)
            ) {
                $issues[] = "May not be compatible with Laravel {$laravelVersion}";
            }
        }

        return $issues;
    }

    /**
     * Get suggestions for package issues.
     */
    protected static function getSuggestions($package, $issues)
    {
        $suggestions = [];

        foreach ($issues as $issue) {
            if (str_contains($issue, 'deprecated')) {
                $suggestions[] = "Consider replacing {$package['name']} with an alternative package";
            } elseif (str_contains($issue, 'Security vulnerability')) {
                $suggestions[] = "Update {$package['name']} to the latest secure version";
            } elseif (str_contains($issue, 'compatible')) {
                $suggestions[] = "Check if there's a newer version of {$package['name']} compatible with your Laravel version";
            }
        }

        return $suggestions;
    }

    protected static function checkSecurityFallback($package)
    {
        // Implement a fallback check using local database or cached advisories
        // For now, we'll return no issues as a safe fallback
        return [];
    }
}