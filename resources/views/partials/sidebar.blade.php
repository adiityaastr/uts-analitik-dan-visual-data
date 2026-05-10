<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="brand-text">Sales Analytics</div>
    </div>
    <div class="sidebar-nav">
        <div class="nav-section">Main Menu</div>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('analysis') }}" class="{{ request()->routeIs('analysis') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill"></i> Analysis
        </a>
        <a href="{{ route('insights') }}" class="{{ request()->routeIs('insights') ? 'active' : '' }}">
            <i class="bi bi-lightbulb-fill"></i> Insights
        </a>

        <div class="nav-section">Data Management</div>
        <a href="{{ route('import.index') }}" class="{{ request()->routeIs('import.*') ? 'active' : '' }}">
            <i class="bi bi-upload"></i> Import Data
        </a>

        <div class="nav-section">Exports</div>
        <a href="{{ route('export.excel') }}">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
        <a href="{{ route('export.pdf') }}">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
    </div>
    <div class="sidebar-footer">
        <div class="fw-semibold">Muhamad Aditya Saputra</div>
        <div>NIM: 411231139</div>
        <div class="mt-1">UTS - Dashboard Analitik</div>
    </div>
</nav>
