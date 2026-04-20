<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Alcaldía Municipal') - Sistema de Gestión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/turbolinks/5.2.0/turbolinks.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.4);
            --secondary: #10b981;
            --dark-bg: #0f172a;
            --sidebar-bg: rgba(15, 23, 42, 0.8);
            --card-bg: rgba(30, 41, 59, 0.5);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --sidebar-width: 240px; /* Reduced from 280px */
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem; /* Compact base font */
            background-color: var(--dark-bg);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 0px 0px, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(16, 185, 129, 0.1) 0%, transparent 40%);
        }

        h1, h2, h3, h4, .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            padding: 16px; /* Reduced from 24px */
        }

        .sidebar.closed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 32px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 24px;
        }

        .logo-box {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 0 20px var(--primary-glow);
        }

        .brand-name {
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: -0.5px;
        }

        .brand-sub {
            font-size: 0.75rem;
            color: var(--text-dim);
            display: block;
        }

        .nav-section {
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-dim);
            margin-bottom: 12px;
            padding-left: 12px;
            font-weight: 700;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px; /* Reduced */
            color: var(--text-dim);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s;
            margin-bottom: 2px;
            font-weight: 500;
            font-size: 0.85rem; /* Smaller icons/text */
        }

        .nav-link:hover {
            color: var(--text-main);
            background: var(--glass);
        }

        .nav-link.active {
            color: white;
            background: linear-gradient(90deg, var(--primary), transparent);
            border-left: 3px solid var(--primary);
        }

        .nav-link i {
            width: 18px;
            height: 18px;
        }

        /* MAIN CONTENT */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-wrapper.full {
            margin-left: 0;
        }

        .top-bar {
            height: 60px; /* Reduced from 70px */
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px; /* Reduced */
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
        }

        .menu-toggle {
            background: none;
            border: none;
            color: var(--text-main);
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-toggle:hover {
            background: var(--glass);
        }

        .page-title-area h1 {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .page-title-area span {
            font-size: 0.8rem;
            color: var(--text-dim);
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-trigger {
            color: var(--text-dim);
            cursor: pointer;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--glass);
            padding: 6px 12px;
            border-radius: 30px;
            border: 1px solid var(--glass-border);
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .content-body {
            padding: 20px; /* Reduced from 32px */
            flex: 1;
        }

        /* ANIMATIONS */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        /* MOBILE RESPONSIVE */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
        }

    </style>
    @yield('styles')
</head>
<body>

    <aside class="sidebar" id="mainSidebar">
        <div class="sidebar-header">
            <div class="logo-box">
                <i data-lucide="building-2"></i>
            </div>
            <div class="brand-info">
                <span class="brand-name">MuniGest</span>
                <span class="brand-sub">Administración Digital</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="section-title">Navegación</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    Dashboard Global
                </a>
            </div>

            <div class="nav-section">
                <div class="section-title">Dependencias</div>
                @foreach(\App\Models\Area::all() as $navArea)
                @php
                    $route = '#';
                    if($navArea->slug == 'almacen') $route = route('almacen.index');
                    if($navArea->slug == 'talento-humano') $route = route('hr.index');
                    if($navArea->slug == 'gestion-riesgo') $route = route('risk.index');
                    if($navArea->slug == 'obras-publicas') $route = route('planning.index');
                @endphp
                <a href="{{ $route }}" class="nav-link {{ request()->is($navArea->slug . '*') ? 'active' : '' }}">
                    <span style="margin-right: 12px;">{{ $navArea->icon ?: '🏢' }}</span>
                    {{ $navArea->name }}
                </a>
                @endforeach
            </div>

            <div class="nav-section" style="margin-top: auto;">
                <div class="section-title">Sistema</div>
                <a href="#" class="nav-link">
                    <i data-lucide="settings"></i>
                    Configuración
                </a>
                <a href="#" class="nav-link" id="logoutBtn">
                    <i data-lucide="log-out"></i>
                    Cerrar Sesión
                </a>
            </div>
        </nav>
    </aside>

    <div class="main-wrapper" id="mainWrapper">
        <header class="top-bar">
            <div style="display: flex; align-items: center; gap: 20px;">
                <button class="menu-toggle" id="toggleSidebar">
                    <i data-lucide="menu"></i>
                </button>
                <div class="page-title-area">
                    <h1>@yield('page_title', 'Inicio')</h1>
                    <span>@yield('page_subtitle', 'Bienvenido al sistema municipal')</span>
                </div>
            </div>

            <div class="user-actions">
                <div class="search-trigger">
                    <i data-lucide="search"></i>
                </div>
                <div class="user-badge">
                    <div class="user-avatar">AD</div>
                    <span style="font-size: 0.85rem; font-weight: 500;">Administrador</span>
                    <i data-lucide="chevron-down" style="width: 14px; height: 14px; color: var(--text-dim);"></i>
                </div>
            </div>
        </header>

        <main class="content-body fade-in">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('turbolinks:load', () => {
            // Lucide Icons
            lucide.createIcons();

            // Sidebar Toggle
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.getElementById('mainSidebar');
            const wrapper = document.getElementById('mainWrapper');

            if(toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    if (window.innerWidth > 1024) {
                        sidebar.classList.toggle('closed');
                        wrapper.classList.toggle('full');
                    } else {
                        sidebar.classList.toggle('open');
                    }
                });
            }
        });

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', (e) => {
            const toggleBtn = document.getElementById('toggleSidebar');
            const sidebar = document.getElementById('mainSidebar');
            if (window.innerWidth <= 1024 && sidebar && toggleBtn) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
