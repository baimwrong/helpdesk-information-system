<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'HelpDesk Information System') }}</title>

    <!-- Local Offline Assets (Bootstrap 5.3, FontAwesome 6, Bootstrap Icons) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">

    <!-- Vite Bundled & Custom Theme Styling -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Local Chart.js for Analytics -->
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar Navigation -->
    <aside class="hd-sidebar d-flex flex-column p-3">
        <div class="d-flex align-items-center gap-2 mb-4 px-2 pt-2 border-bottom border-secondary border-opacity-25 pb-3">
            <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="fa-solid fa-headset fs-5"></i>
            </div>
            <div>
                <span class="brand-title d-block lh-1">HelpDesk</span>
                <small class="text-secondary font-monospace" style="font-size: 0.65rem;">ENTERPRISE IS</small>
            </div>
        </div>

        <ul class="nav nav-pills flex-column mb-auto">
            @auth
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie w-20"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="nav-section-title">Support & Tickets</li>
                <li class="nav-item">
                    <a href="{{ route('tickets.index') }}" class="nav-link {{ request()->routeIs('tickets.index') && !request()->query('tab') ? 'active' : '' }}">
                        <i class="fa-solid fa-ticket-simple w-20"></i>
                        <span>All Tickets</span>
                    </a>
                </li>
                @if(auth()->user()->isStaff())
                    <li class="nav-item">
                        <a href="{{ route('tickets.index', ['tab' => 'my_assigned']) }}" class="nav-link {{ request()->query('tab') === 'my_assigned' ? 'active' : '' }}">
                            <i class="fa-solid fa-user-check w-20"></i>
                            <span>My Assigned</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('tickets.index', ['tab' => 'unassigned']) }}" class="nav-link {{ request()->query('tab') === 'unassigned' ? 'active' : '' }}">
                            <i class="fa-solid fa-inbox w-20"></i>
                            <span>Queue / Unassigned</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('tickets.index', ['tab' => 'breached']) }}" class="nav-link {{ request()->query('tab') === 'breached' ? 'active' : '' }}">
                            <i class="fa-solid fa-triangle-exclamation text-danger w-20"></i>
                            <span>SLA Breached</span>
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('tickets.create') }}" class="nav-link text-white bg-primary bg-opacity-75 my-1 {{ request()->routeIs('tickets.create') ? 'active' : '' }}">
                        <i class="fa-solid fa-plus-circle w-20"></i>
                        <span>New Ticket</span>
                    </a>
                </li>
            @endauth

            <li class="nav-section-title">Self Service</li>
            <li class="nav-item">
                <a href="{{ route('kb.index') }}" class="nav-link {{ request()->routeIs('kb.*') && !request()->routeIs('kb.manage') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open w-20"></i>
                    <span>Knowledge Base</span>
                </a>
            </li>
            @if(auth()->check() && auth()->user()->isStaff())
                <li class="nav-item">
                    <a href="{{ route('kb.manage') }}" class="nav-link {{ request()->routeIs('kb.manage') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-pen w-20"></i>
                        <span>Manage Articles</span>
                    </a>
                </li>
            @endif

            @if(auth()->check() && auth()->user()->isAdmin())
                <li class="nav-section-title">Administration</li>
                <li class="nav-item">
                    <a href="{{ route('admin.bulk-import.index') }}" class="nav-link {{ request()->routeIs('admin.bulk-import.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-file-import text-info w-20"></i>
                        <span>Bulk Import Data</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line w-20"></i>
                        <span>Analytics & SLA</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users w-20"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-building-user w-20"></i>
                        <span>Departments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-tags w-20"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.sla.index') }}" class="nav-link {{ request()->routeIs('admin.sla.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clock w-20"></i>
                        <span>SLA Policies</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.canned.index') }}" class="nav-link {{ request()->routeIs('admin.canned.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-comment-dots w-20"></i>
                        <span>Canned Responses</span>
                    </a>
                </li>
            @endif
        </ul>

        @auth
            <!-- Bottom User Profile Card -->
            <div class="border-top border-secondary border-opacity-25 pt-3 mt-3">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-white" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-weight: 600;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="me-auto text-truncate" style="max-width: 140px;">
                            <span class="d-block small text-truncate fw-semibold">{{ auth()->user()->name }}</span>
                            <span class="badge bg-{{ auth()->user()->isAdmin() ? 'danger' : (auth()->user()->isAgent() ? 'success' : 'secondary') }} text-uppercase" style="font-size: 0.65rem;">
                                {{ auth()->user()->role }}
                            </span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fa-solid fa-user-gear me-2"></i>My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Sign out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        @else
            <div class="border-top border-secondary border-opacity-25 pt-3 mt-3 text-center">
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light w-100">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Sign In
                </a>
            </div>
        @endauth
    </aside>

    <!-- Main Content Area -->
    <div class="hd-main-wrapper">
        <!-- Top Navbar -->
        <nav class="hd-topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary small fw-semibold">
                    <i class="fa-regular fa-calendar me-1"></i> {{ date('l, d F Y') }}
                </span>
                <span class="badge bg-light text-secondary border">PostgreSQL 18 Ready</span>
                <span class="badge bg-light text-success border"><i class="fa-solid fa-signal me-1"></i> Intranet Offline Ready</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('kb.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-book-bookmark me-1"></i> FAQ / KB
                </a>
                @auth
                    <a href="{{ route('tickets.create') }}" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-plus me-1"></i> Submit Ticket
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In
                    </a>
                @endauth
            </div>
        </nav>

        <!-- Flash Messages -->
        <main class="hd-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-info me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-circle-xmark me-2 mt-1"></i>
                        <div>
                            <strong>Please resolve the following errors:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="text-center py-3 text-secondary small border-top bg-white">
            HelpDesk Information System &copy; {{ date('Y') }} &bull; Enterprise IT Service Management &bull; Built with Laravel 13, PostgreSQL 18 & Bootstrap 5.3
        </footer>
    </div>
</div>

@stack('scripts')
</body>
</html>
