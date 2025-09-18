<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Package Health Checker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }

        .package-card {
            transition: transform 0.2s;
        }

        .package-card:hover {
            transform: translateY(-5px);
        }

        .scan-btn {
            transition: all 0.3s;
        }

        .scan-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="bi bi-heart-pulse-fill text-danger me-2"></i>
                        Laravel Package Health Checker
                    </h1>
                    <button id="scanBtn" class="btn btn-primary scan-btn">
                        <i class="bi bi-arrow-repeat me-2"></i>Run Scan
                    </button>
                </div>
                <p class="text-muted">Monitor the health of your Laravel packages</p>
            </div>
        </div>

        <div id="loading" class="d-none">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Scanning packages, please wait...</p>
            </div>
        </div>

        <div id="results">
            @if($lastScan)
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h2 class="card-title">
                                        {{ count(array_filter($lastScan->results, function ($pkg) {
                return $pkg['status'] === 'healthy'; })) }}
                                    </h2>
                                    <p class="card-text">Healthy Packages</p>
                                    <i class="bi bi-check-circle-fill display-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h2 class="card-title">
                                        {{ count(array_filter($lastScan->results, function ($pkg) {
                return $pkg['status'] === 'warning'; })) }}
                                    </h2>
                                    <p class="card-text">Warnings</p>
                                    <i class="bi bi-exclamation-triangle-fill display-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h2 class="card-title">
                                        {{ count(array_filter($lastScan->results, function ($pkg) {
                return $pkg['status'] === 'critical'; })) }}
                                    </h2>
                                    <p class="card-text">Critical Issues</p>
                                    <i class="bi bi-x-circle-fill display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Package Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Package</th>
                                                    <th>Version</th>
                                                    <th>Status</th>
                                                    <th>Issues</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lastScan->results as $package)
                                                    <tr>
                                                        <td>{{ $package['name'] }}</td>
                                                        <td><span class="badge bg-secondary">{{ $package['version'] }}</span></td>
                                                        <td>
                                                            @if($package['status'] === 'healthy')
                                                                <span class="badge bg-success">Healthy</span>
                                                            @elseif($package['status'] === 'warning')
                                                                <span class="badge bg-warning text-dark">Warning</span>
                                                            @else
                                                                <span class="badge bg-danger">Critical</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if(!empty($package['issues']))
                                                                <ul class="mb-0 ps-3">
                                                                    @foreach($package['issues'] as $issue)
                                                                        <li>{{ $issue }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <span class="text-muted">No issues found</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if(!empty($package['suggestions']))
                                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="popover"
                                                                    title="Suggestions"
                                                                    data-bs-content="{{ implode(', ', $package['suggestions']) }}">
                                                                    <i class="bi bi-lightbulb"></i> Suggestions
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer text-muted">
                                    Last scanned: {{ $lastScan->scan_time->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-check display-1 text-muted"></i>
                    <h3 class="mt-3">No scan results yet</h3>
                    <p class="text-muted">Click the "Run Scan" button to check your package health</p>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('scanBtn').addEventListener('click', function () {
            const btn = this;
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');

            // Show loading, hide results
            loading.classList.remove('d-none');
            results.classList.add('d-none');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-arrow-repeat spin me-2"></i>Scanning...';

            // Add spin animation
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes spin { 
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .spin { animation: spin 1s linear infinite; }
            `;
            document.head.appendChild(style);

            // Make API request
            fetch('{{ route("package-health.scan") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show new results
                        window.location.reload();
                    } else {
                        alert('Scan failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Scan failed. Please check the console for details.');
                })
                .finally(() => {
                    loading.classList.add('d-none');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>Run Scan';
                });
        });

        // Initialize popovers
        document.addEventListener('DOMContentLoaded', function () {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>
</body>

</html>