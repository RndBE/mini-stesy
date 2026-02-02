{{-- <aside class="fixed inset-y-0 left-0 hidden .w-[260px] border-r border-slate-200 bg-white lg:block"> --}}
<aside id="mainSidebar" class="fixed top-0 left-0 z-40 w-64 h-full transition-all duration-300 bg-white border-r">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between px-6 py-5">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/beacon-logo.png') }}" alt="Beacon Engineering" class="h-full w-auto sidebar-logo">
                <div
                    class="ml-auto flex items-center justify-center rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-500 sidebar-icon">
                    ☐
                </div>
            </div>
        </div>

        <nav class="px-3 sidebar-nav">
            <div class="space-y-1">
                <a href="{{ route('beranda') }}"
                    class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('beranda') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="sidebar-text">Beranda</span>
                    {{-- <span
                        class="text-[11px] {{ request()->routeIs('beranda') ? 'text-white/70' : 'text-slate-400' }}">/beranda</span> --}}
                </a>

                <a href="{{ route('peta.lokasi') }}"
                    class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('peta.lokasi') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="sidebar-text">Peta Lokasi</span>
                    {{-- <span
                        class="text-[11px] {{ request()->routeIs('peta.lokasi') ? 'text-white/70' : 'text-slate-400' }}">/peta</span> --}}
                </a>

                <a href="#"
                    class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    <span class="sidebar-text">Realtime Monitoring</span>
                </a>

                <a href="#"
                    class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    <span class="sidebar-text">Data Perangkat</span>
                </a>

                <a href="{{ route('device.index') }}"
                    class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('device.index') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span class="sidebar-text">Pengaturan Device</span>
                </a>

                <a href="#"
                    class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    <span class="sidebar-text">Unduh Aplikasi</span>
                </a>
            </div>
        </nav>

        <div class="mt-auto px-3 pb-5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    <span class="sidebar-text">Logout</span>
                </button>
            </form>

            <div class="mt-4 px-3 text-[11px] text-slate-400 sidebar-footer">© Beacon Engineering {{ now()->year }}</div>
        </div>
    </div>
</aside>

<!-- Toggle Button (Fixed position, always visible) -->
<button onclick="toggleMainSidebar()" id="sidebarToggleBtn"
    class="fixed bottom-5 left-64 z-50 flex h-10 w-10 items-center justify-center rounded-r-lg bg-slate-900 text-white hover:bg-slate-800 transition-all duration-300">
    <svg id="toggleIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
    </svg>
</button>

<style>
    #mainSidebar.collapsed {
        transform: translateX(-100%);
    }

    #mainSidebar.collapsed ~ #sidebarToggleBtn {
        left: 0 !important;
    }

    #mainSidebar:not(.collapsed) ~ #sidebarToggleBtn {
        left: 16rem;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        #mainSidebar {
            transform: translateX(-100%);
        }

        #mainSidebar:not(.collapsed) {
            transform: translateX(0);
            z-index: 1000;
        }

        #sidebarToggleBtn {
            left: 0 !important;
        }

        #mainSidebar:not(.collapsed) ~ #sidebarToggleBtn {
            left: 16rem !important;
        }

        /* Overlay when sidebar is open on mobile */
        #mainSidebar:not(.collapsed)::before {
            content: '';
            position: fixed;
            top: 0;
            left: 16rem;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }
    }
</style>

<script>
    function toggleMainSidebar() {
        const sidebar = document.getElementById('mainSidebar');
        const icon = document.getElementById('toggleIcon');
        const mainContent = document.getElementById('mainContent');
        const isMobile = window.innerWidth <= 768;

        sidebar.classList.toggle('collapsed');

        if (sidebar.classList.contains('collapsed')) {
            // Sidebar collapsed - show open icon (pointing right)
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />';
            if (mainContent && !isMobile) {
                mainContent.style.marginLeft = '0';
            }
        } else {
            // Sidebar expanded - show close icon (pointing left)
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />';
            if (mainContent && !isMobile) {
                mainContent.style.marginLeft = '16rem';
            }
        }

        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }

    // Restore sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const isMobile = window.innerWidth <= 768;
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

        // On mobile, always start collapsed
        if (isMobile) {
            const sidebar = document.getElementById('mainSidebar');
            const mainContent = document.getElementById('mainContent');
            if (sidebar && !sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
            }
            if (mainContent) {
                mainContent.style.marginLeft = '0';
            }
        } else if (isCollapsed) {
            toggleMainSidebar();
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        const isMobile = window.innerWidth <= 768;
        const mainContent = document.getElementById('mainContent');
        const sidebar = document.getElementById('mainSidebar');

        if (isMobile && mainContent) {
            mainContent.style.marginLeft = '0';
        } else if (!isMobile && mainContent && !sidebar.classList.contains('collapsed')) {
            mainContent.style.marginLeft = '16rem';
        }
    });
</script>
