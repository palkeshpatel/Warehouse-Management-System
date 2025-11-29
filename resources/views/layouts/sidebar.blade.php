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
            <a href="javascript:void(0)" class="nav-item {{ request()->routeIs('masters.*') ? 'active' : '' }}"
                id="mastersToggle" onclick="toggleMastersMenu()">
                <i class="bi bi-sliders"></i>
                <span class="nav-text">Masters</span>
                <i class="bi bi-chevron-{{ request()->routeIs('masters.*') ? 'down' : 'right' }} ms-auto"></i>
            </a>
            <div class="submenu" id="mastersSubmenu"
                style="{{ request()->routeIs('masters.*') ? '' : 'display: none;' }} margin-left: 20px;">
                <a href="{{ route('masters.index') }}"
                    class="nav-item {{ request()->routeIs('masters.index') ? 'active' : '' }}" data-bs-toggle="tooltip"
                    data-bs-placement="right" title="Tree View">
                    <i class="bi bi-diagram-3"></i>
                    <span class="nav-text">Tree View</span>
                </a>
                <a href="{{ route('masters.categories.index') }}"
                    class="nav-item {{ request()->routeIs('masters.categories.*') ? 'active' : '' }}"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Categories">
                    <i class="bi bi-folder"></i>
                    <span class="nav-text">Category</span>
                </a>
                <a href="{{ route('masters.subcategories.index') }}"
                    class="nav-item {{ request()->routeIs('masters.subcategories.*') ? 'active' : '' }}"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Subcategories">
                    <i class="bi bi-folder2"></i>
                    <span class="nav-text">Subcategory</span>
                </a>
                <a href="{{ route('masters.models.index') }}"
                    class="nav-item {{ request()->routeIs('masters.models.*') ? 'active' : '' }}"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Models">
                    <i class="bi bi-box"></i>
                    <span class="nav-text">Model</span>
                </a>
            </div>
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

<script>
    function toggleMastersMenu() {
        const submenu = document.getElementById('mastersSubmenu');
        const toggle = document.getElementById('mastersToggle');
        const icon = toggle.querySelector('.bi-chevron-down, .bi-chevron-right');

        if (submenu.style.display === 'none' || !submenu.style.display) {
            submenu.style.display = 'block';
            if (icon) {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            }
        } else {
            submenu.style.display = 'none';
            if (icon) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            }
        }
    }
</script>
