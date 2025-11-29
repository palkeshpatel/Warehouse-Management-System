<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <button class="btn btn-link text-dark" id="sidebarToggle" style="color: var(--primary-color) !important;">
            <i class="bi bi-list"></i>
        </button>
        <div class="ms-auto d-flex align-items-center gap-3">
            <div class="dropdown">
                <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown"
                    style="color: var(--text-color) !important;">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=FF9900&color=fff"
                        class="rounded-circle me-2" width="32" height="32" alt="Profile">
                    <span>{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"
                            onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
