<!-- main-sidebar -->
<div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
<div class="sticky">
    <aside class="app-sidebar sidebar-scroll">
        <div class="main-sidemenu">
            <div class="app-sidebar__user">
                <div class="dropdown user-pro-body text-center">
                    <div class="user-pic">
                        <img alt="user-img" class="avatar avatar-xl brround mb-1"
                            src="{{ asset('images/logo/2023-02-09-Pioneers-SA-Badge-Clear-Background.png') }}">
                    </div>
                    <div class="user-info text-center">
                        <h5 class=" mb-1 font-weight-bold">{{ auth()->user()->name }}</h5>
                    </div>
                </div>
            </div>
            <div class="slide-left disabled" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>
            <ul class="side-menu">
                <li class="slide">
                    <a class="side-menu__item  @if (Route::is('index')) active @endif" data-bs-toggle="slide"
                        href="{{ route('index') }}">
                        <i class="fa fa-dashboard fa-2x mx-3"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>
                @php
                    $user = \App\Models\User::with(['roles'])->find(auth()->id());
                    $userRoles = $user->roles;
                    $isAdmin = false;
                    foreach ($userRoles as $role) {
                        if ($role?->name == 'Admin') {
                            $isAdmin = true;
                            break;
                        }
                    }
                @endphp
                <li class="slide">
                    <a class="side-menu__item  @if (Route::is('payment.list')) active @endif" data-bs-toggle="slide"
                        href="{{ route('payment.list') }}">
                        <i class="fa fa-money fa-2x mx-3"></i>
                        <span class="side-menu__label">Payments List</span>
                    </a>
                </li>
                @if (!$isAdmin)
                    <li class="slide">
                        <a class="side-menu__item  @if (Route::is('profile')) active @endif"
                            data-bs-toggle="slide" href="{{ route('profile') }}">
                            <i class="fa fa-user fa-2x mx-3"></i>
                            <span class="side-menu__label">Profile</span>
                        </a>
                    </li>
                @endif
                @if (!$isAdmin)
                    <li class="slide">
                        <a class="side-menu__item  @if (Route::is('juniors')) active @endif"
                            data-bs-toggle="slide" href="{{ route('juniors') }}">
                            <i class="fa fa-child fa-2x mx-3"></i>
                            <span class="side-menu__label">Juniors</span>
                        </a>
                    </li>
                @endif
                @if (!$isAdmin)
                    <li class="slide">
                        <a class="side-menu__item  @if (Route::is('partner')) active @endif"
                            data-bs-toggle="slide" href="{{ route('partner') }}">
                            <i class="fa fa-handshake-o fa-2x mx-3"></i>
                            <span class="side-menu__label">Partner</span>
                        </a>
                    </li>
                @endif
                {{-- @canany(['payment-list']) --}}
                {{-- @endcanany --}}
                @canany(['membership-list'])
                    <li class="slide @if (Route::is('gl_codes.create', 'gl_codes.index')) is-expanded @endif">
                        <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                            <i class="fa fa-user fa-2x mx-3"></i>
                            <span class="side-menu__label">Finance</span><i class="angle fe fe-chevron-right"></i></a>
                        <ul class="slide-menu @if (Route::is('gl_codes.create', 'gl_codes.index')) open @endif">
                            @can('membership-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('gl_codes.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('gl_codes.index') }}">
                                        <span class="sub-side-menu__label">GL Code List</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['membership-list'])
                    <li class="slide @if (Route::is('ancestor-data.create', 'ancestor-data.index')) is-expanded @endif">
                        <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                            <i class="fa fa-user fa-2x mx-3"></i>
                            <span class="side-menu__label">Memberships</span><i class="angle fe fe-chevron-right"></i></a>
                        <ul class="slide-menu @if (Route::is('ancestor-data.create', 'ancestor-data.index')) open @endif">
                            @can('membership-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('members.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('members.index') }}">
                                        <span class="sub-side-menu__label">Member List</span>
                                    </a>
                                </li>
                            @endcan
                            {{-- @can('membership-create')
                                <li class="sub-slide">
                                    {{-- @if (Route::is('ancestor-data.create')) active @endif --}}
                                    {{-- {{ route('ancestor-data.create') }}
                                    <a class="sub-side-menu__item mx-5 " data-bs-toggle="sub-slide" href="">
                                        <span class="sub-side-menu__label">Add</span>
                                    </a>
                                </li>
                            @endcan
                            --}}

                            @can('membership-create')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('members.create')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('members.create') }}">
                                        <span class="sub-side-menu__label">Add</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['ancestor-create', 'ancestor-list'])
                    <li class="slide @if (Route::is('ancestor-data.create', 'ancestor-data.index')) is-expanded @endif">
                        <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                            <i class="fa fa-sitemap fa-2x mx-3"></i>
                            <span class="side-menu__label">Ancestors</span><i class="angle fe fe-chevron-right"></i></a>
                        <ul class="slide-menu @if (Route::is('ancestor-data.create', 'ancestor-data.index')) open @endif">
                            @can('ancestor-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('ancestor-data.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('ancestor-data.index') }}">
                                        <span class="sub-side-menu__label">List</span>
                                    </a>
                                </li>
                            @endcan
                            @can('ancestor-create')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('ancestor-data.create')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('ancestor-data.create') }}">
                                        <span class="sub-side-menu__label">Add</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['mode-of-arrival-create', 'mode-of-arrival-list'])
                    <li class="slide @if (Route::is('mode-of-arrivals.create', 'mode-of-arrivals.index')) is-expanded @endif">
                        <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                            <i class="fa fa-ship fa-2x mx-3"></i>
                            <span class="side-menu__label">Journey</span><i class="angle fe fe-chevron-right"></i></a>
                        <ul class="slide-menu  @if (Route::is('mode-of-arrivals.create', 'mode-of-arrivals.index')) open @endif">
                            @can('mode-of-arrival-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('mode-of-arrivals.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('mode-of-arrivals.index') }}">
                                        <span class="sub-side-menu__label">List</span>
                                    </a>
                                </li>
                            @endcan
                            @can('mode-of-arrival-create')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('mode-of-arrivals.create')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('mode-of-arrivals.create') }}">
                                        <span class="sub-side-menu__label">Add</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @canany(['user-list', 'states-list', 'ports-list', 'counties-list', 'occupations-list', 'rigs-list',
                    'ships-list', 'role-list', 'source-of-arrival-list', 'cities-list', 'subscription-plans-list'])
                    <li class="slide @if (Route::is(
                            'user.index',
                            'states.index',
                            'ports.index',
                            'counties.index',
                            'occupations.index',
                            'rigs.index',
                            'ship.index',
                            'source-of-arrivals.index',
                            'subscription-plans.index',
                            'cities.index')) is-expanded @endif">
                        <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)">
                            <i class="fa fa-gears fa-2x mx-3"></i>
                            <span class="side-menu__label">Settings</span><i class="angle fe fe-chevron-right"></i></a>
                        <ul class="slide-menu @if (Route::is(
                                'user.index',
                                'states.index',
                                'ports.index',
                                'counties.index',
                                'occupations.index',
                                'rigs.index',
                                'ship.index',
                                'subscription-plans.index')) open @endif">
                            @can('user-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('user.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('user.index') }}">
                                        <span class="sub-side-menu__label">Users</span>
                                    </a>
                                </li>
                            @endcan
                            @can('role-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('roles.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('roles.index') }}">
                                        <span class="sub-side-menu__label">Roles</span>
                                    </a>
                                </li>
                            @endcan
                            @can('cities-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('cities.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('cities.index') }}">
                                        <span class="sub-side-menu__label">Cities</span>
                                    </a>
                                </li>
                            @endcan
                            {{-- @can('states-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('states.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('states.index') }}">
                        <span class="sub-side-menu__label">States</span>
                        </a>
                </li>
                @endcan--> --}}
                            @can('counties-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('counties.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('counties.index') }}">
                                        <span class="sub-side-menu__label">Counties / States</span>
                                    </a>
                                </li>
                            @endcan
                            @can('countries-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('countries.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('countries.index') }}">
                                        <span class="sub-side-menu__label">Countries</span>
                                    </a>
                                </li>
                            @endcan
                            @can('ports-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('ports.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('ports.index') }}">
                                        <span class="sub-side-menu__label">Ports of Arrival in SA</span>
                                    </a>
                                </li>
                            @endcan
                            @can('ships-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('ship.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('ship.index') }}">
                                        <span class="sub-side-menu__label">Ships</span>
                                    </a>
                                </li>
                            @endcan
                            @can('rigs-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('rigs.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('rigs.index') }}">
                                        <span class="sub-side-menu__label">Rigs</span>
                                    </a>
                                </li>
                            @endcan
                            @can('occupations-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('occupations.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('occupations.index') }}">
                                        <span class="sub-side-menu__label">Occupations</span>
                                    </a>
                                </li>
                            @endcan
                            @can('source-of-arrival-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('source-of-arrivals.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('source-of-arrivals.index') }}">
                                        <span class="sub-side-menu__label">Mode of Travel</span>
                                    </a>
                                </li>
                            @endcan

                            @can('subscription-plans-list')
                                <li class="sub-slide">
                                    <a class="sub-side-menu__item mx-5 @if (Route::is('subscription-plans.index')) active @endif"
                                        data-bs-toggle="sub-slide" href="{{ route('subscription-plans.index') }}">
                                        <span class="sub-side-menu__label">Membership Categories</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
            </ul>
            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </aside>
</div>
<!-- main-sidebar -->
