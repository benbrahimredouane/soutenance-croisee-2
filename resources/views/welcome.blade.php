<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'EasyColoc') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900">

    <!-- NAV -->
    <nav class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-4 flex items-center justify-between">
            <span class="text-lg font-semibold tracking-tight text-slate-900">
                EasyColoc
            </span>

            @if (Route::has('login'))
                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 transition">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition shadow-sm">
                                Sign up
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </nav>

    <!-- HERO -->
    <main class="flex-1 flex items-center">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 py-16 sm:py-20 text-center">

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight text-slate-900">
                Smarter shared living.
            </h1>

            <p class="mt-4 text-slate-600 text-base sm:text-lg max-w-xl mx-auto">
                Split bills, track expenses, and manage your colocation effortlessly —
                all in one simple place.
            </p>

            @if (Route::has('register'))
                <div class="mt-8">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition shadow-sm">
                        Get started for free
                    </a>
                </div>
            @endif

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-slate-500">
            <span>© {{ date('Y') }} EasyColoc</span>
            <span>Better shared living </span>
        </div>
    </footer>

</body>
</html>