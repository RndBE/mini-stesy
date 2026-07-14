<aside id="mainSidebar" class="fixed top-0 left-0 z-[40] w-64 h-full transition-all duration-300">
    <div class="flex h-full flex-col">
        <div class="flex items-center justify-between px-6 py-5 sidebar-header">
            <img src="{{ asset('images/beacon-logo.png') }}" alt="Beacon Engineering" class="h-full w-auto sidebar-logo">
            <button type="button" onclick="toggleMainSidebar()" aria-label="Toggle sidebar"
                class="ml-auto flex items-center justify-center rounded-lg text-slate-500 sidebar-icon hover:bg-slate-100">
                <img src="{{ asset('images/sidebar.svg') }}" alt="" class="h-5 w-5">
            </button>
        </div>

        <nav class="px-3 sidebar-nav flex-1 overflow-y-auto">
            <div class="space-y-1">

                {{-- ── MONITORING ── --}}
                @if (auth()->check() && (auth()->user()->hasPermission('view_beranda') || auth()->user()->hasPermission('view_peta_lokasi') || auth()->user()->hasPermission('view_realtime')))
                    <p class="nav-section"><span class="sidebar-text">Monitoring</span></p>
                @endif

                @permission('view_beranda')
                    <a href="{{ route('beranda') }}"
                        class="nav-link {{ request()->routeIs('beranda') ? 'is-active' : '' }}">
                        <img src="{{ asset(request()->routeIs('beranda') ? 'icons/beranda_fill.svg' : 'icons/beranda_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('beranda') ? 'brightness-0 invert' : '' }}" alt="Beranda">
                        <span class="sidebar-text truncate">Beranda</span>
                    </a>
                @endpermission

                @if (auth()->check() && auth()->user()->canViewSkemaPipa())
                    <a href="{{ route('skema-pipa') }}"
                        class="nav-link {{ request()->routeIs('skema-pipa') ? 'is-active' : '' }}">
                        <img src="{{ asset(request()->routeIs('skema-pipa') ? 'icons/skema_pipa_fill.svg' : 'icons/skema_pipa.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('skema-pipa') ? 'brightness-0 invert' : '' }}" alt="Skema Pipa">
                        <span class="sidebar-text truncate">Skema Pipa</span>
                    </a>
                @endif

                @permission('view_peta_lokasi')
                    <a href="{{ route('peta.lokasi') }}"
                        class="nav-link {{ request()->routeIs('peta.lokasi') ? 'is-active' : '' }}">
                        <img src="{{ asset(request()->routeIs('peta.lokasi') ? 'icons/peta_fill.svg' : 'icons/peta_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('peta.lokasi') ? 'brightness-0 invert' : '' }}" alt="Peta Lokasi">
                        <span class="sidebar-text truncate">Peta Lokasi</span>
                    </a>

                    <a href="{{ route('rekap-data.index') }}"
                        class="nav-link {{ request()->routeIs('rekap-data.*') ? 'is-active' : '' }}">
                        <img src="{{ asset(request()->routeIs('rekap-data.*') ? 'icons/data_perangkat_fill.svg' : 'icons/data_perangkat_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('rekap-data.*') ? 'brightness-0 invert' : '' }}" alt="Rekap Data">
                        <span class="sidebar-text truncate">Rekap Data</span>
                    </a>
                @endpermission

                @permission('view_realtime')
                    <a href="{{ route('realtime.index') }}"
                        class="nav-link {{ request()->routeIs('realtime.index') ? 'is-active' : '' }}">
                        <img src="{{ asset(request()->routeIs('realtime.index') ? 'icons/realtime_monitoring_fill.svg' : 'icons/realtime_monitoring_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('realtime.index') ? 'brightness-0 invert' : '' }}" alt="Realtime">
                        <span class="sidebar-text truncate">Realtime Monitoring</span>
                    </a>

                    <a href="{{ route('tingkat-siaga-awlr.index') }}"
                        class="nav-link {{ request()->routeIs('tingkat-siaga-awlr.*') ? 'is-active' : '' }}">
                        <img src="{{ asset(request()->routeIs('tingkat-siaga-awlr.*') ? 'icons/tingkat_siaga_fill.svg' : 'icons/tingkat_siaga.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('tingkat-siaga-awlr.*') ? 'brightness-0 invert' : '' }}" alt="Tingkat Siaga">
                        <span class="sidebar-text truncate">Tingkat Siaga</span>
                    </a>
                @endpermission

                {{-- ── PERANGKAT ── --}}
                @if (auth()->check() && (auth()->user()->hasPermission('view_data_perangkat') || auth()->user()->hasPermission('view_device')))
                    <p class="nav-section"><span class="sidebar-text">Perangkat</span></p>
                @endif

                @permission('view_data_perangkat')
                    <a href="{{ route('device.data') }}"
                        class="nav-link {{ request()->routeIs('device.data') ? 'is-active' : '' }}">
                        <img src="{{ asset(request()->routeIs('device.data') ? 'icons/data_perangkat_fill.svg' : 'icons/data_perangkat_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('device.data') ? 'brightness-0 invert' : '' }}" alt="Data Perangkat">
                        <span class="sidebar-text truncate">Data Perangkat</span>
                    </a>
                @endpermission

                @permission('view_device')
                    <a href="{{ route('device.index') }}"
                        class="nav-link {{ request()->routeIs('device.index') ? 'is-active' : '' }}">
                        <img src="{{ asset(request()->routeIs('device.index') ? 'icons/pengaturan_fill.svg' : 'icons/pengaturan_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('device.index') ? 'brightness-0 invert' : '' }}" alt="Pengaturan Device">
                        <span class="sidebar-text truncate">Pengaturan Device</span>
                    </a>
                @endpermission

                {{-- ── ADMINISTRASI ── --}}
                @if (auth()->check() && (auth()->user()->hasPermission('manage_instansi') || auth()->user()->hasPermission('manage_rbac') || auth()->user()->hasPermission('manage_user') || auth()->user()->isSuperAdmin()))
                    <p class="nav-section"><span class="sidebar-text">Administrasi</span></p>
                @endif

                @if (auth()->check() &&
                        (auth()->user()->hasPermission('manage_instansi') ||
                            auth()->user()->hasPermission('manage_rbac') ||
                            auth()->user()->hasPermission('manage_user')))
                    <div x-data="{ open: {{ request()->routeIs('instansi.*') || request()->routeIs('kategori.*') || request()->routeIs('list-parameter.*') || request()->routeIs('parameter-group.*') || request()->routeIs('template-kategori-parameter.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*') || request()->routeIs('users.*') ? 'true' : 'false' }} }" class="nav-group-card">
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
                            class="nav-link sidebar-parent-btn w-full justify-between">
                            <span class="sidebar-parent-label flex items-center gap-3">
                                <img src="{{ asset('icons/master_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0" alt="Master Data">
                                <span class="sidebar-text truncate">Master Data</span>
                            </span>
                            <svg class="sidebar-parent-chevron h-4 w-4 text-slate-400 transition-transform"
                                :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div x-show="open" x-collapse x-cloak class="relative space-y-1 pb-2 pt-1 pl-3 pr-2 ml-[22px] nav-subtree">
                            @if (auth()->check() && (auth()->user()->hasPermission('manage_instansi') || auth()->user()->isInstansiAdmin()))
                                <a href="{{ route('instansi.index') }}"
                                    class="nav-sublink {{ request()->routeIs('instansi.*') ? 'is-active' : '' }}">
                                    @if(request()->routeIs('instansi.*')) <div class="subrail"></div> @endif
                                    <img src="{{ asset(request()->routeIs('instansi.*') ? 'icons/instansi_fill.svg' : 'icons/instansi_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('instansi.*') ? 'brightness-0 invert' : '' }}" alt="Instansi">
                                    <span class="sidebar-text truncate">Instansi</span>
                                </a>
                            @endif

                            @permission('manage_instansi')
                                <a href="{{ route('kategori.index') }}"
                                    class="nav-sublink {{ request()->routeIs('kategori.*') ? 'is-active' : '' }}">
                                    @if(request()->routeIs('kategori.*')) <div class="subrail"></div> @endif
                                    <img src="{{ asset(request()->routeIs('kategori.*') ? 'icons/kategori_fill.svg' : 'icons/kategori_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('kategori.*') ? 'brightness-0 invert' : '' }}" alt="Kategori Logger">
                                    <span class="sidebar-text truncate">Kategori Logger</span>
                                </a>
                                <a href="{{ route('list-parameter.index') }}"
                                    class="nav-sublink {{ request()->routeIs('list-parameter.*') ? 'is-active' : '' }}">
                                    @if(request()->routeIs('list-parameter.*')) <div class="subrail"></div> @endif
                                    <img src="{{ asset(request()->routeIs('list-parameter.*') ? 'icons/list_line_fill.svg' : 'icons/list_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('list-parameter.*') ? 'brightness-0 invert' : '' }}" alt="List Parameter">
                                    <span class="sidebar-text truncate">List Parameter</span>
                                </a>
                                <a href="{{ route('template-kategori-parameter.index') }}"
                                    class="nav-sublink {{ request()->routeIs('template-kategori-parameter.*') ? 'is-active' : '' }}">
                                    @if(request()->routeIs('template-kategori-parameter.*')) <div class="subrail"></div> @endif
                                    <img src="{{ asset(request()->routeIs('template-kategori-parameter.*') ? 'icons/template_fill.svg' : 'icons/template_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('template-kategori-parameter.*') ? 'brightness-0 invert' : '' }}" alt="Template Parameter">
                                    <span class="sidebar-text truncate">Template Parameter</span>
                                </a>
                            @endpermission

                            @permission('manage_rbac')
                                <a href="{{ route('roles.index') }}"
                                    class="nav-sublink {{ request()->routeIs('roles.*') ? 'is-active' : '' }}">
                                    @if(request()->routeIs('roles.*')) <div class="subrail"></div> @endif
                                    <img src="{{ asset(request()->routeIs('roles.*') ? 'icons/rbac_role_fill.svg' : 'icons/rbac_role_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('roles.*') ? 'brightness-0 invert' : '' }}" alt="RBAC Role">
                                    <span class="sidebar-text truncate">RBAC Role</span>
                                </a>
                                <a href="{{ route('permissions.index') }}"
                                    class="nav-sublink {{ request()->routeIs('permissions.*') ? 'is-active' : '' }}">
                                    @if(request()->routeIs('permissions.*')) <div class="subrail"></div> @endif
                                    <img src="{{ asset(request()->routeIs('permissions.*') ? 'icons/rbac_permission_fill.svg' : 'icons/rbac_permission_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('permissions.*') ? 'brightness-0 invert' : '' }}" alt="RBAC Permission">
                                    <span class="sidebar-text truncate">RBAC Permission</span>
                                </a>
                            @endpermission

                            @permission('manage_user')
                                <a href="{{ route('users.index') }}"
                                    class="nav-sublink {{ request()->routeIs('users.*') ? 'is-active' : '' }}">
                                    @if(request()->routeIs('users.*')) <div class="subrail"></div> @endif
                                    <img src="{{ asset(request()->routeIs('users.*') ? 'icons/user_fill.svg' : 'icons/user_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('users.*') ? 'brightness-0 invert' : '' }}" alt="User">
                                    <span class="sidebar-text truncate">User</span>
                                </a>
                            @endpermission
                        </div>
                    </div>
                @endif

                @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('manage_rbac')))
                <a href="{{ route('settings.index') }}"
                    class="nav-link {{ request()->routeIs('settings.*') ? 'is-active' : '' }}">
                    <img src="{{ asset(request()->routeIs('settings.*') ? 'icons/pengaturan_fill.svg' : 'icons/pengaturan_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('settings.*') ? 'brightness-0 invert' : '' }}" alt="Pengaturan Sistem">
                    <span class="sidebar-text truncate">Pengaturan Sistem</span>
                </a>
                @endif

                @if(auth()->check() && auth()->user()->isSuperAdmin())
                <a href="{{ route('notifikasi.index') }}"
                    class="nav-link {{ request()->routeIs('notifikasi.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('notifikasi.*') ? 'brightness-0 invert' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="sidebar-text truncate">Kirim Notifikasi</span>
                </a>
                @endif

                {{-- ── LAINNYA ── --}}
                <p class="nav-section"><span class="sidebar-text">Lainnya</span></p>

                <a href="{{ route('download.index') }}"
                    class="nav-link {{ request()->routeIs('download.*') ? 'is-active' : '' }}">
                    <img src="{{ asset(request()->routeIs('download.*') ? 'icons/unduh_aplikasi_fill.svg' : 'icons/unduh_aplikasi_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('download.*') ? 'brightness-0 invert' : '' }}" alt="Unduh Aplikasi">
                    <span class="sidebar-text truncate">Unduh Aplikasi</span>
                </a>

                @permission('view_audit_log')
                <a href="{{ route('audit-log.index') }}"
                    class="nav-link {{ request()->routeIs('audit-log.*') ? 'is-active' : '' }}">
                    <img src="{{ asset('icons/log_audit_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0 {{ request()->routeIs('audit-log.*') ? 'brightness-0 invert' : '' }}" alt="Log Audit">
                    <span class="sidebar-text truncate">Log Audit</span>
                </a>
                @endpermission
            </div>
        </nav>

        <div class="mt-auto px-3 pb-5 sidebar-foot-wrap">
            <div class="sys-status">
                <span class="sys-dot"></span>
                <span class="sidebar-text sys-label">
                    <span class="sys-title">Sistem Aktif</span>
                    <span class="sys-sub">Realtime · Terhubung</span>
                </span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link nav-logout w-full text-left">
                    <img src="{{ asset('icons/keluar_line.svg') }}" class="nav-ico h-5 w-5 flex-shrink-0" alt="Logout">
                    <span class="sidebar-text truncate">Logout</span>
                </button>
            </form>

            <div class="mt-3 px-3 text-[11px] text-slate-400 sidebar-footer">© Beacon Engineering {{ now()->year }}
            </div>
        </div>
    </div>
</aside>

<style>
    [x-cloak] {
        display: none !important;
    }

    /* ── Shell ── */
    #mainSidebar {
        background:
            radial-gradient(120% 60% at 0% 0%, rgba(48, 52, 129, .05), transparent 60%),
            linear-gradient(180deg, #ffffff 0%, #fafbfd 100%);
        border-right: 1px solid rgba(15, 23, 42, .07);
        box-shadow: 1px 0 0 rgba(255, 255, 255, .6) inset;
    }

    .sidebar-header {
        border-bottom: 1px solid rgba(15, 23, 42, .06);
    }

    .sidebar-icon {
        height: 2rem;
        width: 2rem;
        transition: background .18s ease, transform .18s ease;
    }
    .sidebar-icon:active { transform: scale(.92); }

    .sidebar-nav {
        animation: navFade .5s ease both;
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, .5) transparent;
    }
    .sidebar-nav::-webkit-scrollbar { width: 6px; }
    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .45);
        border-radius: 99px;
    }
    @keyframes navFade {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: none; }
    }

    /* ── Section micro-labels ── */
    .nav-section {
        display: flex;
        align-items: center;
        gap: .55rem;
        margin: 1.85rem .15rem .45rem;
        padding: 0 .7rem;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #9aa6bb;
    }
    .nav-section::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(148, 163, 184, .35), transparent);
    }
    .nav-section:first-child { margin-top: 1rem; }

    /* Tailwind `space-y-1` (.space-y-1 > :not([hidden]) ~ :not([hidden]), spec 0,3,0)
       would otherwise clobber the section label's margin-top down to .25rem.
       Scope to #mainSidebar (spec 1,1,0) so the intended breathing room actually applies. */
    #mainSidebar .nav-section { margin-top: 1.1rem; margin-bottom: .4rem; }
    #mainSidebar .nav-section:first-child { margin-top: .85rem; }

    /* ── Nav links ── */
    .nav-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: .75rem;
        border-radius: 12px;
        padding: .68rem .75rem;
        font-size: .875rem;
        font-weight: 600;
        color: #3f4a5c;
        transition: color .2s ease, background .2s ease, box-shadow .25s ease, transform .15s ease;
    }
    .nav-link .nav-ico { transition: transform .2s ease, filter .2s ease; }

    .nav-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        height: 58%;
        width: 3px;
        border-radius: 0 4px 4px 0;
        background: #e11d2a;            /* Beacon red accent */
        transform: translateY(-50%) scaleY(0);
        transform-origin: center;
        transition: transform .25s cubic-bezier(.4, 0, .2, 1);
    }

    .nav-link:hover {
        background: #eef1f7;
        color: #1e293b;
    }
    .nav-link:hover .nav-ico { transform: translateX(2px); }

    .nav-link.is-active {
        background: linear-gradient(135deg, #3a3f9a 0%, #2a2e73 100%);
        color: #fff;
        box-shadow: 0 12px 24px -14px rgba(48, 52, 129, .7),
                    inset 0 1px 0 rgba(255, 255, 255, .12);
    }
    .nav-link.is-active::before { transform: translateY(-50%) scaleY(1); }
    .nav-link.is-active:hover { background: linear-gradient(135deg, #353a8f 0%, #23275f 100%); }

    /* Master Data parent button */
    .sidebar-parent-btn { color: #3f4a5c; }

    /* ── Sub-tree (Master Data) ── */
    .nav-subtree {
        border-left: 2px solid rgba(148, 163, 184, .3);
    }
    .nav-sublink {
        position: relative;
        display: flex;
        align-items: center;
        gap: .7rem;
        border-radius: 10px;
        padding: .5rem .7rem;
        font-size: .82rem;
        font-weight: 600;
        color: #4b5566;
        transition: color .18s ease, background .18s ease;
    }
    .nav-sublink:hover { background: #eef1f7; color: #1e293b; }
    .nav-sublink.is-active {
        background: linear-gradient(135deg, #3a3f9a 0%, #2a2e73 100%);
        color: #fff;
        box-shadow: 0 10px 20px -14px rgba(48, 52, 129, .7);
    }
    .subrail {
        position: absolute;
        left: -14px;
        top: 6px;
        bottom: 6px;
        width: 3px;
        border-radius: 0 4px 4px 0;
        background: #e11d2a;
    }

    /* ── Logout ── */
    .nav-logout { color: #475569; }
    .nav-logout::before { background: #dc2626; }
    .nav-logout:hover {
        background: #fef2f2;
        color: #dc2626;
    }
    .nav-logout:hover::before { transform: translateY(-50%) scaleY(1); }

    /* ── Footer status ── */
    .sidebar-foot-wrap { border-top: 1px solid rgba(15, 23, 42, .06); padding-top: .85rem; }
    .sys-status {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin: 0 .15rem .6rem;
        padding: .55rem .7rem;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(34, 197, 94, .08), rgba(34, 197, 94, .02));
        border: 1px solid rgba(34, 197, 94, .18);
    }
    .sys-dot {
        flex-shrink: 0;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, .5);
        animation: sysPulse 2.2s infinite;
    }
    @keyframes sysPulse {
        0%   { box-shadow: 0 0 0 0 rgba(34, 197, 94, .5); }
        70%  { box-shadow: 0 0 0 7px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }
    .sys-label { display: flex; flex-direction: column; line-height: 1.15; }
    .sys-title { font-size: .76rem; font-weight: 700; color: #15803d; }
    .sys-sub   { font-size: .66rem; font-weight: 500; color: #64748b; }

    #mainSidebar.collapsed .sidebar-icon {
        margin-left: 0 !important;
    }

    /* Hide labels/section headers + center the status chip when collapsed */
    #mainSidebar.collapsed .nav-section {
        justify-content: center;
        padding: 0;
        margin: .6rem 0 .25rem;
    }
    #mainSidebar.collapsed .nav-section::after { display: none; }
    #mainSidebar.collapsed .sys-status {
        justify-content: center;
        padding: .55rem 0;
    }
    #mainSidebar.collapsed .nav-logout {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    @media (max-width: 1023px) {
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

    @media (min-width: 1024px) {
        #mainSidebar.collapsed {
            width: 5rem;
            transform: none;
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
</style>

<script>
    function syncSidebarBackdrop() {
        const sidebar = document.getElementById('mainSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const isMobile = window.innerWidth < 1024;

        if (!sidebar || !backdrop) return;

        const showBackdrop = isMobile && !sidebar.classList.contains('collapsed');
        backdrop.classList.toggle('hidden', !showBackdrop);
        document.body.classList.toggle('overflow-hidden', showBackdrop);
        const mapContainer = document.getElementById('mapContainer');
        if (mapContainer) {
            mapContainer.style.zIndex = showBackdrop ? '1' : '';
            mapContainer.style.position  = showBackdrop ? 'relative' : '';
        }
    }

    function applySidebarLayout() {
        const sidebar = document.getElementById('mainSidebar');
        const mainContent = document.getElementById('mainContent');
        const isMobile = window.innerWidth < 1024;

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
        if (typeof map !== 'undefined') {
            const dur = 320, step = 16;
            let t = 0;
            const iv = setInterval(() => {
                map.invalidateSize({ animate: false });
                t += step;
                if (t >= dur) clearInterval(iv);
            }, step);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const isMobile = window.innerWidth < 1024;
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const isSkemaIrigasi = {{ request()->routeIs('skema-irigasi.*') ? 'true' : 'false' }};

        const sidebar = document.getElementById('mainSidebar');
        const mainContent = document.getElementById('mainContent');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (!sidebar || !mainContent) return;

        if (isMobile) {
            sidebar.classList.add('collapsed');
        } else if (isSkemaIrigasi) {
            sidebar.classList.add('collapsed');
        } else if (isCollapsed) {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }

        applySidebarLayout();

        if (backdrop) {
            backdrop.addEventListener('click', function() {
                const isMobileNow = window.innerWidth < 1024;
                if (!isMobileNow) return;

                sidebar.classList.add('collapsed');
                applySidebarLayout();
            });
        }
    });

    window.addEventListener('resize', function() {
        const isMobile = window.innerWidth < 1024;
        const sidebar = document.getElementById('mainSidebar');
        if (!sidebar) return;

        if (isMobile) {
            sidebar.classList.add('collapsed');
        }

        applySidebarLayout();
    });
</script>
