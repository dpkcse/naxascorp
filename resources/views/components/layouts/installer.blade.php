@props(['currentStep' => 1])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <meta name="referrer" content="same-origin">
        <title>{{ $title ?? 'Installation' }} — Naxora CMS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-svh overflow-x-hidden bg-slate-100 text-slate-950 antialiased">
        <div class="min-h-svh bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.12),_transparent_36rem)]">
            <header class="border-b border-white/10 bg-slate-950 text-white">
                <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-5 sm:px-8">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-blue-600 font-bold shadow-lg shadow-blue-950/30">N</span>
                        <div>
                            <p class="font-semibold tracking-tight">Naxora CMS</p>
                            <p class="text-xs text-slate-400">Secure installation wizard</p>
                        </div>
                    </div>
                    <span class="rounded-full border border-slate-700 px-3 py-1 text-xs font-medium text-slate-300">Version {{ config('app.version', '1.0.0') }}</span>
                </div>
            </header>

            <main class="mx-auto grid max-w-6xl gap-6 px-5 py-8 sm:px-8 lg:grid-cols-[17rem_minmax(0,1fr)] lg:gap-10 lg:py-14">
                <aside aria-label="Installation progress">
                    @php
                        $steps = [1 => ['Welcome', 'Overview'], 2 => ['Requirements', 'Server readiness'], 3 => ['Permissions', 'File access'], 4 => ['Database', 'Connection and schema'], 5 => ['Administrator', 'Secure account']];
                    @endphp
                    <ol class="grid grid-cols-5 gap-2 lg:grid-cols-1 lg:gap-3">
                        @foreach ($steps as $number => [$label, $description])
                            <li class="min-w-0 rounded-xl border px-2 py-3 lg:flex lg:items-center lg:gap-3 lg:px-4 {{ $number === $currentStep ? 'border-blue-500 bg-white shadow-sm' : ($number < $currentStep ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white/60') }}">
                                <span class="mx-auto flex size-8 items-center justify-center rounded-full text-sm font-bold lg:mx-0 {{ $number === $currentStep ? 'bg-blue-600 text-white' : ($number < $currentStep ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600') }}">{{ $number < $currentStep ? '✓' : $number }}</span>
                                <span class="mt-2 block min-w-0 text-center lg:mt-0 lg:text-left">
                                    <span class="block truncate text-xs font-semibold sm:text-sm">{{ $label }}</span>
                                    <span class="hidden text-xs text-slate-500 lg:block">{{ $description }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                    <p class="mt-5 hidden text-sm leading-6 text-slate-500 lg:block">Early progress stays in this browser session. Database preparation and administrator creation occur only after successful verification.</p>
                </aside>

                <section class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
                    <div class="p-6 sm:p-9 lg:p-11">{{ $slot }}</div>
                </section>
            </main>

            <footer class="px-5 pb-8 text-center text-xs text-slate-500">Developed by Naxas Innovations Limited</footer>
        </div>
        @fluxScripts
    </body>
</html>
