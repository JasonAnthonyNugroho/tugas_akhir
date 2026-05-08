<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    {{-- CDN SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- CDN Real-time (Laravel Reverb) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
        // Inisialisasi Laravel Echo
        window.Pusher = Pusher;

        // Ambil konfigurasi dari Laravel ke JS dengan aman untuk menghindari error IDE
        const reverbKey = "{{ config('broadcasting.connections.reverb.key') }}";
        const reverbHost = "{{ config('broadcasting.connections.reverb.options.host') }}";
        const reverbPort = "{{ config('broadcasting.connections.reverb.options.port', 8080) }}";
        const reverbScheme = "{{ config('broadcasting.connections.reverb.options.scheme', 'http') }}";

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: parseInt(reverbPort),
            wssPort: parseInt(reverbPort),
            forceTLS: reverbScheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        
        // Debug: Monitor Reverb connection status
        window.Echo.connector.pusher.connection.bind('connected', function() {
            console.log('Reverb: Connected successfully!');
        });
        
        window.Echo.connector.pusher.connection.bind('disconnected', function() {
            console.warn('Reverb: Disconnected!');
        });
        
        window.Echo.connector.pusher.connection.bind('error', function(err) {
            console.error('Reverb: Connection error:', err);
        });
        
        console.log('Reverb initialized with config:', {
            key: reverbKey,
            host: reverbHost,
            port: reverbPort,
            scheme: reverbScheme
        });
    </script>
    <title>Rector Cup - @yield('title')</title>
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: #1e293b;
            --accent-primary: #6366f1;
            --accent-secondary: #8b5cf6;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --success: #10b981;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.01em;
            overflow-x: hidden;
        }

        /* Navbar/Header Modern */
        .top-navbar {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .sidebar {
            min-height: 100vh;
            background: var(--card-bg);
            border-right: 1px solid var(--glass-border);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            padding-top: 1rem;
        }

        .nav-link {
            color: var(--text-muted);
            padding: 0.8rem 1.2rem;
            margin: 0.2rem 1rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            text-decoration: none !important;
        }

        .nav-link i {
            font-size: 1.2rem;
            margin-right: 12px;
        }

        .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(4px);
        }

        .nav-link.active {
            color: #fff !important;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .card-header {
            background: rgba(255, 255, 255, 0.03) !important;
            border-bottom: 1px solid var(--glass-border);
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            color: #fff;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
            color: #fff;
        }

        .badge-live {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 6px 14px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-primary {
            background: rgba(99, 102, 241, 0.15);
            color: var(--accent-primary);
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-primary i {
            font-size: 1rem;
            color: var(--accent-secondary);
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white !important;
        }

        .dropdown-item i {
            transition: transform 0.2s;
        }

        .dropdown-item:hover i {
            transform: translateX(3px);
            color: var(--accent-primary) !important;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 0 10px #ef4444;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .table {
            color: var(--text-main);
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table thead th {
            border: none;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 1rem 1.5rem;
        }

        .table tbody tr {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 16px;
            transition: all 0.3s;
        }

        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: scale(1.01);
        }

        .table td {
            border: none;
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
        }

        .table tbody tr td:first-child {
            border-radius: 16px 0 0 16px;
        }

        .table tbody tr td:last-child {
            border-radius: 0 16px 16px 0;
        }

        .form-control {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 12px;
            padding: 0.8rem 1.2rem;
            height: auto;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            color: #fff;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -280px;
                height: 100%;
                width: 280px;
                z-index: 1050;
            }

            .sidebar.active {
                left: 0;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(15, 23, 42, 0.8);
                backdrop-filter: blur(4px);
                z-index: 1040;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        /* Bracket Styles */
        .bracket-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .bracket-wrapper::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .bracket-wrapper::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.3);
            border-radius: 10px;
        }

        .bracket-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.5);
        }

        .bracket-match {
            transition: all 0.3s;
            z-index: 2;
        }

        .bracket-match:hover {
            border-color: var(--accent-primary) !important;
            background: rgba(255, 255, 255, 0.08) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .bracket-match-link {
            transition: all 0.3s ease;
            display: block;
        }

        .bracket-match-link:hover {
            text-decoration: none;
        }

        .bracket-connector-v {
            position: absolute;
            right: -25px;
            width: 2px;
            background: var(--glass-border);
            z-index: 1;
        }
    </style>

    @yield('styles')
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="container-fluid px-0">
        <!-- Modern Top Navbar -->
        <div class="top-navbar px-4 py-3 shadow-sm">
            <div class="row align-items-center">
                <div class="col-auto d-lg-none">
                    <button class="btn text-white p-0" id="sidebarToggle">
                        <i class="bi bi-list h2 mb-0"></i>
                    </button>
                </div>
                <div class="col d-flex align-items-center">
                    <div class="d-flex align-items-center ml-2 ml-lg-0">
                        <div class="bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center mr-3"
                            style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)) !important;">
                            <i class="bi bi-trophy-fill text-white"></i>
                        </div>
                        <h4 class="mb-0 font-weight-bold tracking-tighter"
                            style="letter-spacing: -1px; background: linear-gradient(to right, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            RECTOR CUP
                        </h4>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="dropdown">
                        <button
                            class="btn d-flex align-items-center text-white font-weight-600 bg-white-10 border-0 rounded-pill px-3 py-2"
                            style="background: rgba(255,255,255,0.05);" type="button" data-toggle="dropdown">
                            <div class="bg-secondary rounded-circle mr-2 d-flex align-items-center justify-content-center"
                                style="width: 24px; height: 24px;">
                                <i class="bi bi-person text-white" style="font-size: 14px;"></i>
                            </div>
                            <span class="d-none d-sm-inline mr-2 small">{{ Auth::user()->name ?? "Guest" }}</span>
                            <i class="bi bi-chevron-down small"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right mt-2 shadow-lg border-0"
                            style="background: var(--card-bg); border-radius: 16px; min-width: 200px;">
                            @auth
                                <div class="px-4 py-3 border-bottom border-secondary mb-2">
                                    <p class="small text-muted mb-0">Signed in as</p>
                                    <p class="font-weight-bold mb-0 text-white">{{ Auth::user()->name }}</p>
                                </div>
                                <a class="dropdown-item py-2 px-4 d-flex align-items-center text-white"
                                    href="{{ route('admin.index') }}">
                                    <i class="bi bi-speedometer2 mr-3 text-white"></i> Admin Dashboard
                                </a>
                                <div class="dropdown-divider border-secondary"></div>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item py-2 px-4 d-flex align-items-center text-danger">
                                        <i class="bi bi-box-arrow-right mr-3"></i> Logout
                                    </button>
                                </form>
                            @else
                                <a class="dropdown-item py-3 px-4 d-flex align-items-center text-white"
                                    href="{{ route('login') }}">
                                    <i class="bi bi-shield-lock mr-3 text-white"></i> Login Panitia
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row no-gutters">
            <!-- Modern Sidebar -->
            <div class="col-lg-2 sidebar shadow-lg" id="sidebarMenu">
                <div class="nav flex-column nav-pills">
                    <div class="px-4 mb-4 d-lg-none">
                        <h5 class="text-white font-weight-bold">Navigation</h5>
                    </div>

                    <small class="text-muted text-uppercase font-weight-bold px-4 mb-2 small"
                        style="letter-spacing: 2px; font-size: 0.65rem;">Main Menu</small>

                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">
                        <i class="bi bi-grid-1x2"></i> <span>Dashboard</span>
                    </a>
                    <a class="nav-link {{ request()->is('history') ? 'active' : '' }}" href="{{ route('history') }}">
                        <i class="bi bi-archive"></i> <span>History</span>
                    </a>

                    @auth
                        <div class="mt-4">
                            <small class="text-muted text-uppercase font-weight-bold px-4 mb-2 small"
                                style="letter-spacing: 2px; font-size: 0.65rem;">Administrator</small>
                            <a class="nav-link {{ request()->is('admin') ? 'active' : '' }}" href="/admin">
                                <i class="bi bi-calendar-event"></i> <span>Kelola Jadwal</span>
                            </a>
                            <a class="nav-link {{ request()->is('admin/skor') ? 'active' : '' }}"
                                href="{{ route('admin.skor') }}">
                                <i class="bi bi-pencil-square"></i> <span>Kelola Skor</span>
                            </a>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-lg-10 p-4 min-vh-100">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    @yield('scripts')

    @if(session('success') && !session('updated_id'))
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                background: '#1a1a1a',
                color: '#ffffff',
                confirmButtonColor: '#10b981'
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            Swal.fire({
                title: 'Gagal!',
                text: "{{ session('error') }}",
                icon: 'error',
                background: '#1a1a1a',
                color: '#ffffff',
                confirmButtonColor: '#ff4d4d'
            });
        </script>
    @endif

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#sidebarToggle, #sidebarOverlay').on('click', function () {
                $('#sidebarMenu').toggleClass('active');
                $('#sidebarOverlay').toggleClass('active');
            });
        });
    </script>
</body>

</html>