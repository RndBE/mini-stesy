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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="sidebar-text truncate">Beranda</span>
                    </a>
                @endpermission

                @permission('view_peta_lokasi')
                    <a href="{{ route('peta.lokasi') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('peta.lokasi') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span class="sidebar-text truncate">Peta Lokasi</span>
                    </a>
                @endpermission

                @permission('view_realtime')
                    <a href="{{ route('realtime.index') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('realtime.index') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                        <span class="sidebar-text truncate">Realtime Monitoring</span>
                    </a>

                    {{-- <a href="{{ route('tingkat-siaga-awlr.index') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('tingkat-siaga-awlr.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4V7m4 12H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2Z" />
                        </svg>
                        <span class="sidebar-text truncate">Tingkat Siaga AWLR</span>
                    </a> --}}
                @endpermission

                @permission('view_data_perangkat')
                    <a href="{{ route('device.data') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('device.data') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="sidebar-text truncate">Data Perangkat</span>
                    </a>
                @endpermission

                @permission('view_device')
                    <a href="{{ route('device.index') }}"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('device.index') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4h16v16H4z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 4v16M15 4v16M4 9h16M4 15h16" />
                                </svg>
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
                                    <span class="sidebar-text truncate">Instansi</span>
                                </a>
                            @endif

                            @permission('manage_instansi')
                                <a href="{{ route('kategori.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('kategori.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <span class="sidebar-text truncate">Kategori Logger</span>
                                </a>
                                <a href="{{ route('list-parameter.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('list-parameter.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <span class="sidebar-text truncate">List Parameter</span>
                                </a>
                                <a href="{{ route('template-kategori-parameter.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('template-kategori-parameter.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <span class="sidebar-text truncate">Template Parameter</span>
                                </a>
                            @endpermission

                            @permission('manage_rbac')
                                <a href="{{ route('roles.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('roles.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <span class="sidebar-text truncate">RBAC Role</span>
                                </a>
                                <a href="{{ route('permissions.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('permissions.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <span class="sidebar-text truncate">RBAC Permission</span>
                                </a>
                            @endpermission

                            @permission('manage_user')
                                <a href="{{ route('users.index') }}"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs('users.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-white' }}">
                                    <span class="sidebar-text truncate">User</span>
                                </a>
                            @endpermission
                        </div>
                    </div>
                @endif

                <a href="{{ route('download.index') }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold {{ request()->routeIs('download.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="sidebar-text truncate">Unduh Aplikasi</span>
                </a>

                <a href="{{ route('audit-log.index') }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('audit-log.*') ? 'bg-[#303481] hover:bg-[#10134B] text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="sidebar-text truncate">Log Audit</span>
                </a>
            </div>
        </nav>

        <div class="mt-auto px-3 pb-5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
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
