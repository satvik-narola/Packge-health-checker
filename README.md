# Packge-health-checker
A Laravel package to check all installed packages for deprecated code, security vulnerabilities, and version compatibility with your Laravel installation.
# Laravel Package Health Checker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itzdevsatvik/laravel-package-health-checker.svg?style=flat-square)](https://packagist.org/packages/itzdevsatvik/laravel-package-health-checker)
[![Total Downloads](https://img.shields.io/packagist/dt/itzdevsatvik/laravel-package-health-checker.svg?style=flat-square)](https://packagist.org/packages/itzdevsatvik/laravel-package-health-checker)
[![License](https://img.shields.io/packagist/l/itzdevsatvik/laravel-package-health-checker.svg?style=flat-square)](https://packagist.org/packages/itzdevsatvik/laravel-package-health-checker)

A Laravel package to check all installed packages for deprecated code, security vulnerabilities, and version compatibility with your Laravel installation.

## Features

- ✅ Check for deprecated/abandoned packages
- 🔒 Security vulnerability scanning
- 🔄 Laravel version compatibility checking
- 📊 Beautiful dashboard to view results
- ⚡ Console command for CI/CD integration
- 🔧 Configurable features and settings

## Installation

You can install the package via Composer:

```bash
composer require itzdevsatvik/laravel-package-health-checker:dev-main

php artisan vendor:publish --provider="Itzdevsatvik\PackageHealthChecker\Providers\PackageHealthCheckerServiceProvider" --tag=packagehealthchecker-config

## Console Command

Run the health check from the command line:
php artisan package-health:check

Use the --no-cache option to force a fresh check:
php artisan package-health:check --no-cache

PACKAGE_HEALTH_ALLOWED_EMAILS="admin@example.com,user@example.com"
PACKAGE_HEALTH_ALLOWED_DOMAINS="example.com,company.com"
