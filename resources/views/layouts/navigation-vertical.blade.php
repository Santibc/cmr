<div class="d-flex flex-column h-100">
    {{-- Logo --}}
    <div class="d-flex justify-content-center align-items-center py-3 border-bottom">
        <a href="/" class="text-decoration-none">
            <img src="{{ asset('images/logodorado2.png') }}" class="logo-full" width="100" alt="Logo">
            <img src="{{ asset('images/logoico.png') }}" class="logo-icon d-none" width="40" alt="Logo Icon">
        </a>
    </div>

    {{-- Navegación --}}
    <nav class="nav flex-column px-2 py-3">
<a href="/dashboard"
   class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('dashboard') ? 'active' : 'text-dark' }}">
    <i class="bi bi-speedometer2"></i>
    <span>Inicio</span>
</a>
@if (auth()->user()->getRoleNames()->first() == 'admin') 
<a href="/usuarios"
   class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('usuarios*') ? 'active' : 'text-dark' }}">
    <i class="bi bi-person-lines-fill"></i>
    <span>Usuarios</span>
</a>
@endif
@if (auth()->user()->getRoleNames()->first() == 'admin' || auth()->user()->getRoleNames()->first() == 'closer')
    <a href="/leads"
    class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('leads*') && !request()->is('onboarding/leads*') ? 'active' : 'text-dark' }}">
        <i class="bi bi-person-plus-fill"></i>
        <span>Leads</span>
    </a>

    <a href="/llamadas"
    class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('llamadas*') ? 'active' : 'text-dark' }}">
        <i class="bi bi-telephone-fill"></i>
        <span>Llamadas</span>
    </a>
@endif

@if (auth()->user()->getRoleNames()->first() == 'admin' || auth()->user()->getRoleNames()->first() == 'cms')
    <!-- Onboarding Dropdown -->
    <div class="nav-item dropdown mb-2">
        <a class="nav-link d-flex align-items-center gap-2 dropdown-toggle {{ request()->is('onboarding*') ? 'active' : 'text-dark' }}" 
           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-headset"></i>
            <span>Onboarding</span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('onboarding/dashboard*') ? 'active' : '' }}" 
                   href="/onboarding/dashboard">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('onboarding/leads*') ? 'active' : '' }}"
                   href="/onboarding/leads">
                    <i class="bi bi-people"></i>
                    <span>Gestión de Leads</span>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('contracts/approval*') ? 'active' : '' }}"
                   href="/contracts/approval">
                    <i class="bi bi-file-earmark-check"></i>
                    <span>Aprobación de Contratos</span>
                </a>
            </li>
        </ul>
    </div>
@endif

        {{-- Agrega más enlaces aquí si lo deseas --}}
    </nav>

    {{-- Botón Salir --}}
    <div class="mt-auto p-3 border-top">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-start gap-2">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span class="logout-label">Salir</span>
            </button>
        </form>
    </div>
</div>