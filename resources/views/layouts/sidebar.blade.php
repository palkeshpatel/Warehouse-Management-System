<aside class="sidebar">
    <div class="sidebar-header">
        <h4 class="logo-text">Warehouse System</h4>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
            <i class="bi bi-speedometer2"></i>
            <span class="nav-text">Dashboard</span>
        </a>
        @if (auth()->user()->isSuperAdmin())
            <a href="{{ route('warehouses.index') }}"
                class="nav-item {{ request()->routeIs('warehouses.*') ? 'active' : '' }}" data-bs-toggle="tooltip"
                data-bs-placement="right" title="Warehouses">
                <i class="bi bi-building"></i>
                <span class="nav-text">Warehouses</span>
            </a>
            <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}"
                data-bs-toggle="tooltip" data-bs-placement="right" title="Users">
                <i class="bi bi-people"></i>
                <span class="nav-text">Users</span>
            </a>
            <a href="{{ route('masters.index') }}" class="nav-item {{ request()->routeIs('masters.*') ? 'active' : '' }}"
                data-bs-toggle="tooltip" data-bs-placement="right" title="Masters Management">
                <i class="bi bi-diagram-3"></i>
                <span class="nav-text">Masters</span>
            </a>
        @endif
        <a href="{{ route('inventory.index') }}"
            class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}" data-bs-toggle="tooltip"
            data-bs-placement="right" title="Inventory">
            <i class="bi bi-box-seam"></i>
            <span class="nav-text">Inventory</span>
        </a>
        @if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
            <a href="{{ route('reports.index') }}"
                class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" data-bs-toggle="tooltip"
                data-bs-placement="right" title="Reports">
                <i class="bi bi-graph-up"></i>
                <span class="nav-text">Reports</span>
            </a>
        @endif
    </nav>
</aside>

