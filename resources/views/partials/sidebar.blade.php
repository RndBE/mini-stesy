{{-- <aside class="fixed inset-y-0 left-0 hidden .w-[260px] border-r border-slate-200 bg-white lg:block"> --}}
<aside id="mainSidebar" class="fixed top-0 left-0 z-40 w-64 h-full transition-all duration-300 bg-white border-r">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between px-6 py-5">
            <img src="{{ asset('images/beacon-logo.png') }}" alt="Beacon Engineering" class="h-full w-auto sidebar-logo">
            <button type="button" onclick="toggleMainSidebar()" aria-label="Toggle sidebar"
                class="ml-auto flex items-center justify-center rounded-md  text-slate-500 sidebar-icon hover:bg-slate-100">
                <img src="{{ asset('images/sidebar.svg') }}" alt="" class="h-5 w-5">
            </button>
        </div>

        <nav class="px-3 sidebar-nav">
            <div class="space-y-2">
                @permission('view_beranda')
                    <a href="{{ route('beranda') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('beranda') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <img src="{{ asset('icons/beranda_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('beranda') ? 'brightness-0 invert' : '' }}" alt="Beranda">
                        <span class="sidebar-text truncate">Beranda</span>
                    </a>
                @endpermission

                @permission('view_peta_lokasi')
                    <a href="{{ route('peta.lokasi') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('peta.lokasi') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <img src="{{ asset('icons/peta_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('peta.lokasi') ? 'brightness-0 invert' : '' }}" alt="Peta Lokasi">
                        <span class="sidebar-text truncate">Peta Lokasi</span>
                    </a>
                @endpermission

                @permission('view_realtime')
                    <a href="{{ route('realtime.index') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('realtime.index') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <img src="{{ asset('icons/realtime_monitoring_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('realtime.index') ? 'brightness-0 invert' : '' }}" alt="Realtime">
                        <span class="sidebar-text truncate">Realtime Monitoring</span>
                    </a>

                    <a href="{{ route('tingkat-siaga-awlr.index') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('tingkat-siaga-awlr.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <img src="{{ asset('icons/pengaturan_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('tingkat-siaga-awlr.*') ? 'brightness-0 invert' : '' }}" alt="Tingkat Siaga">
                        <span class="sidebar-text truncate">Tingkat Siaga AWLR</span>
                    </a>
                @endpermission

                @permission('view_data_perangkat')
                    <a href="{{ route('device.data') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('device.data') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <img src="{{ asset('icons/data_perangkat_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('device.data') ? 'brightness-0 invert' : '' }}" alt="Data Perangkat">
                        <span class="sidebar-text truncate">Data Perangkat</span>
                    </a>
                @endpermission

                @permission('view_device')
                    <a href="{{ route('device.index') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('device.index') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <img src="{{ asset('icons/pengaturan_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('device.index') ? 'brightness-0 invert' : '' }}" alt="Pengaturan Device">
                        <span class="sidebar-text truncate">Pengaturan Device</span>
                    </a>
                @endpermission

                @if (auth()->check() &&
                        (auth()->user()->hasPermission('manage_instansi') ||
                            auth()->user()->hasPermission('manage_rbac') ||
                            auth()->user()->hasPermission('manage_user')))
                    <div x-data="{ open: {{ request()->routeIs('instansi.*') || request()->routeIs('kategori.*') || request()->routeIs('list-parameter.*') || request()->routeIs('parameter-group.*') || request()->routeIs('template-kategori-parameter.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') || request()->routeIs('users.*') ? 'true' : 'false' }} }" class="rounded-xl border border-slate-200 bg-slate-50/60">
                        <button type="button"
                            @click="
                                const sidebar = document.getElementById('mainSidebar');
                                if (sidebar && sidebar.classList.contains('collapsed')) {
                                    toggleMainSidebar();
                                    open = true;
                                } else {
                                    open = !open;
                                }
                            "
                            class="sidebar-parent-btn flex w-full items-center justify-between gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            <span class="sidebar-parent-label flex items-center gap-3">
                                <img src="{{ asset('icons/master_line.svg') }}" class="h-5 w-5 flex-shrink-0" alt="Master Data">
                                <span class="sidebar-text truncate">Master Data</span>
                            </span>
                            <svg class="sidebar-parent-chevron h-4 w-4 text-slate-500 transition-transform"
                                :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div x-show="open" x-collapse x-cloak class="space-y-2 px-2 pb-2 pt-2">
                            @if (auth()->check() && (auth()->user()->hasPermission('manage_instansi') || auth()->user()->isInstansiAdmin()))
                                <a href="{{ route('instansi.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('instansi.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <img src="{{ asset('icons/instansi_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('instansi.*') ? 'brightness-0 invert' : '' }}" alt="Instansi">
                                    <span class="sidebar-text truncate">Instansi</span>
                                </a>
                            @endif

                            @permission('manage_instansi')
                                <a href="{{ route('kategori.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('kategori.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <img src="{{ asset('icons/kategori_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('kategori.*') ? 'brightness-0 invert' : '' }}" alt="Kategori Logger">
                                    <span class="sidebar-text truncate">Kategori Logger</span>
                                </a>
                                <a href="{{ route('list-parameter.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('list-parameter.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <img src="{{ asset('icons/list_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('list-parameter.*') ? 'brightness-0 invert' : '' }}" alt="List Parameter">
                                    <span class="sidebar-text truncate">List Parameter</span>
                                </a>
                                <a href="{{ route('template-kategori-parameter.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('template-kategori-parameter.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <img src="{{ asset('icons/template_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('template-kategori-parameter.*') ? 'brightness-0 invert' : '' }}" alt="Template Parameter">
                                    <span class="sidebar-text truncate">Template Parameter</span>
                                </a>
                            @endpermission

                            @permission('manage_rbac')
                                <a href="{{ route('roles.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('roles.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <img src="{{ asset('icons/rbac_role_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('roles.*') ? 'brightness-0 invert' : '' }}" alt="RBAC Role">
                                    <span class="sidebar-text truncate">RBAC Role</span>
                                </a>
                                <a href="{{ route('permissions.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('permissions.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <img src="{{ asset('icons/rbac_permission_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('permissions.*') ? 'brightness-0 invert' : '' }}" alt="RBAC Permission">
                                    <span class="sidebar-text truncate">RBAC Permission</span>
                                </a>
                            @endpermission

                            @permission('manage_user')
                                <a href="{{ route('users.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('users.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <img src="{{ asset('icons/user_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('users.*') ? 'brightness-0 invert' : '' }}" alt="User">
                                    <span class="sidebar-text truncate">User</span>
                                </a>
                            @endpermission
                        </div>
                    </div>
                @endif

                <a href="{{ route('download.index') }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('download.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <img src="{{ asset('icons/unduh_aplikasi_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('download.*') ? 'brightness-0 invert' : '' }}" alt="Unduh Aplikasi">
                    <span class="sidebar-text truncate">Unduh Aplikasi</span>
                </a>

                <a href="{{ route('audit-log.index') }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('audit-log.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <img src="{{ asset('icons/log_audit_line.svg') }}" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('audit-log.*') ? 'brightness-0 invert' : '' }}" alt="Log Audit">
                    <span class="sidebar-text truncate">Log Audit</span>
                </a>
            </div>
        </nav>

        <div class="mt-auto px-3 pb-5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    <img src="{{ asset('icons/keluar_line.svg') }}" class="h-5 w-5 flex-shrink-0" alt="Logout">
                    <span class="sidebar-text truncate">Logout</span>
                </button>
            </form>

            <div class="mt-4 px-3 text-[11px] text-slate-400 sidebar-footer">© Beacon Engineering {{ now()->year }}
            </div>
        </div>
    </div>
</aside>

<style>
    /* Hide elements until Alpine.js is ready */
    [x-cloak] {
        display: none !important;
    }

    #mainSidebar.collapsed .sidebar-icon {
        margin-left: 0 !important;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        #mainSidebar {
            transform: translateX(-100%);
            width: 16rem;
        }

        #mainSidebar.collapsed {
            transform: translateX(-100%);
            width: 16rem;
        }

        #mainSidebar:not(.collapsed) {
            transform: translateX(0);
            z-index: 1000;
        }

        #mainSidebar.collapsed .sidebar-text,
        #mainSidebar.collapsed .sidebar-logo,
        #mainSidebar.collapsed .sidebar-footer {
            display: none;
        }

        #mainSidebar.collapsed .sidebar-nav a {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        #mainSidebar.collapsed .sidebar-nav a span.text-\[11px\] {
            display: none;
        }

        #mainSidebar.collapsed .sidebar-parent-btn {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        #mainSidebar.collapsed .sidebar-parent-label {
            gap: 0;
        }

        #mainSidebar.collapsed .sidebar-parent-chevron {
            display: none;
        }

        #mainSidebar.collapsed .px-6.py-5 {
            justify-content: center;
            padding: 1.25rem 0;
        }

    }

    /* Desktop styles for collapsed state */
    @media (min-width: 769px) {
        #mainSidebar.collapsed {
            width: 5rem;
            /* Mini sidebar width */
            transform: none;
        }

        #mainSidebar.collapsed .sidebar-text,
        #mainSidebar.collapsed .sidebar-logo,
        #mainSidebar.collapsed .sidebar-footer {
            display: none;
            /* Hide text and other elements */
        }

        #mainSidebar.collapsed .sidebar-nav a {
            justify-content: center;
            /* Center icons */
            padding-left: 0;
            padding-right: 0;
        }

        #mainSidebar.collapsed .sidebar-nav a span.text-\[11px\] {
            display: none;
        }

        #mainSidebar.collapsed .sidebar-parent-btn {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        #mainSidebar.collapsed .sidebar-parent-label {
            gap: 0;
        }

        #mainSidebar.collapsed .sidebar-parent-chevron {
            display: none;
        }

        /* Center brand icon if needed or hide header content strictly */
        #mainSidebar.collapsed .px-6.py-5 {
            justify-content: center;
            padding: 1.25rem 0;
        }

        /* Show a small logo or icon when collapsed if available, else standard logo hidden */
    }
</style>

<script>
    function syncSidebarBackdrop() {
        const sidebar = document.getElementById('mainSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const isMobile = window.innerWidth <= 768;

        if (!sidebar || !backdrop) return;

        const showBackdrop = isMobile && !sidebar.classList.contains('collapsed');
        backdrop.classList.toggle('hidden', !showBackdrop);
        document.body.classList.toggle('overflow-hidden', showBackdrop);
    }

    function applySidebarLayout() {
        const sidebar = document.getElementById('mainSidebar');
        const mainContent = document.getElementById('mainContent');
        const isMobile = window.innerWidth <= 768;

        if (!sidebar || !mainContent) return;

        mainContent.style.paddingLeft = '0';

        if (isMobile) {
            mainContent.style.marginLeft = '0';
            mainContent.style.width = '100%';
            syncSidebarBackdrop();
            return;
        }

        const sidebarWidth = sidebar.classList.contains('collapsed') ? '5rem' : '16rem';
        mainContent.style.marginLeft = sidebarWidth;
        mainContent.style.width = `calc(100% - ${sidebarWidth})`;
        syncSidebarBackdrop();
    }

    function toggleMainSidebar() {
        const sidebar = document.getElementById('mainSidebar');
        if (!sidebar) return;

        sidebar.classList.toggle('collapsed');
        applySidebarLayout();
        localStorage.setItem('sidebarCollapsed', String(sidebar.classList.contains('collapsed')));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const isMobile = window.innerWidth <= 768;
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

        const sidebar = document.getElementById('mainSidebar');
        const mainContent = document.getElementById('mainContent');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (!sidebar || !mainContent) return;

        if (isMobile) {
            sidebar.classList.add('collapsed');
        } else if (isCollapsed) {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }

        applySidebarLayout();

        if (backdrop) {
            backdrop.addEventListener('click', function() {
                const isMobileNow = window.innerWidth <= 768;
                if (!isMobileNow) return;

                sidebar.classList.add('collapsed');
                applySidebarLayout();
            });
        }
    });

    window.addEventListener('resize', function() {
        const isMobile = window.innerWidth <= 768;
        const sidebar = document.getElementById('mainSidebar');
        if (!sidebar) return;

        if (isMobile) {
            sidebar.classList.add('collapsed');
        }

        applySidebarLayout();
    });
</script>
