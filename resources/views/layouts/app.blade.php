<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Analitik Penjualan | 411231139 - Muhamad Aditya Saputra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #624bff;
            --primary-light: rgba(98, 75, 255, 0.1);
            --success: #19cb98;
            --warning: #ffaa46;
            --danger: #e53f3c;
            --dark: #1e293b;
            --body-bg: #f1f5f9;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--body-bg);
            color: #334155;
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #fff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: transform 0.3s;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
        }

        .sidebar-brand .brand-text {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--dark);
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0.75rem;
            overflow-y: auto;
        }

        .sidebar-nav .nav-section {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            padding: 0.5rem 1rem;
            margin-top: 0.5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.15rem;
            transition: all 0.15s;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        .sidebar-nav a i {
            font-size: 1.15rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            color: var(--dark);
        }

        .content-area {
            flex: 1;
            padding: 1.5rem;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card .card-body {
            padding: 1.25rem 1.5rem;
        }

        .stat-card .stat-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .stat-card .stat-label {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        .chart-card {
            border: none;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .chart-card .card-header {
            background: transparent;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--dark);
        }

        .chart-card .card-body {
            padding: 1.25rem 1.5rem;
        }

        .insight-box {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary);
        }

        .export-btn .dropdown-toggle {
            background: var(--primary);
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }

        .bg-primary-light { background: var(--primary-light); color: var(--primary); }
        .bg-success-light { background: rgba(25, 203, 152, 0.1); color: var(--success); }
        .bg-warning-light { background: rgba(255, 170, 70, 0.1); color: var(--warning); }
        .bg-danger-light { background: rgba(229, 63, 60, 0.1); color: var(--danger); }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        @include('partials.sidebar')

        <div class="main-content flex-grow-1">
            <div class="topbar d-flex justify-content-between align-items-center">
                <h1>@yield('title', 'Dashboard Analitik Penjualan')</h1>
                <div class="d-flex align-items-center gap-3">
                    <div class="export-btn dropdown">
                        <button class="btn dropdown-toggle text-white" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i> Export Data
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="{{ route('export.excel') }}"><i class="bi bi-file-earmark-excel me-2 text-success"></i> Export Excel (All Data)</a></li>
                            <li><a class="dropdown-item" href="{{ route('export.product-excel') }}"><i class="bi bi-file-earmark-excel me-2 text-success"></i> Export Excel (By Product)</a></li>
                            <li><a class="dropdown-item" href="{{ route('export.category-excel') }}"><i class="bi bi-file-earmark-excel me-2 text-success"></i> Export Excel (By Category)</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('export.pdf') }}"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i> Export PDF</a></li>
                        </ul>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">411231139 - Muhamad Aditya Saputra</small>
                    </div>
                </div>
            </div>

            <div class="content-area">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @yield('scripts')
</body>
</html>
