<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema de Tickets')</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Iconos Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="bg-light">

    @php
        // Detectar si estamos en la página de login
        $isLoginPage = request()->routeIs('login');
    @endphp

    <!-- NAVBAR SUPERIOR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">

            <a class="navbar-brand fw-bold" 
               href="{{ Auth::check() ? route(Auth::user()->role . '.dashboard') : route('login') }}">
                Sistema de Tickets
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav" aria-controls="navbarNav" 
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav ms-auto">

                    {{-- NO mostrar botones si estamos en el LOGIN --}}
                    @if(!$isLoginPage)

                        {{-- Usuario autenticado → Mostrar saludo + Cerrar sesión --}}
                        @auth
                            <li class="nav-item me-3">
                                <span class="text-white fw-bold">
                                    Hola, {{ Auth::user()->name }}
                                </span>
                            </li>

                            <li class="nav-item">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="btn btn-light btn-sm">
                                        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        @endauth

                        {{-- Invitado → Mostrar Iniciar sesión --}}
                        @guest
                            <li class="nav-item">
                                <a href="{{ route('login') }}" class="btn btn-light btn-sm">
                                    Iniciar sesión
                                </a>
                            </li>
                        @endguest

                    @endif

                </ul>

            </div>
        </div>
    </nav>


    <!-- CONTENIDO -->
    <main class="container py-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
