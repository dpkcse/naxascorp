@props(['title' => 'Dashboard', 'breadcrumbs' => [['label' => 'Dashboard']]])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    @include('partials.head', ['title' => $title.' — Naxora CMS'])
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="same-origin">
    <meta http-equiv="Cache-Control" content="no-store, private">
</head>
<body class="min-h-full overflow-x-hidden bg-admin-background text-admin-text antialiased">
<a href="#main-content" class="sr-only z-50 rounded-md bg-white px-4 py-2 text-navy-950 focus:not-sr-only focus:fixed focus:left-3 focus:top-3">Skip to main content</a>
<div x-data="{ navigationOpen: false, closeNavigation() { this.navigationOpen = false; this.$nextTick(() => this.$refs.navigationToggle?.focus()) } }" x-effect="document.body.classList.toggle('overflow-hidden', navigationOpen)" x-on:keydown.escape.window="if (navigationOpen) closeNavigation()" class="min-h-screen lg:flex">
    <div x-cloak x-show="navigationOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden" x-on:click="closeNavigation()" aria-hidden="true"></div>
    <aside id="admin-navigation" :class="navigationOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-[min(19rem,88vw)] flex-col overflow-y-auto bg-navy-950 text-slate-100 shadow-2xl transition-transform motion-reduce:transition-none lg:sticky lg:top-0 lg:h-screen lg:w-72 lg:translate-x-0">
        <div class="flex min-h-20 items-center justify-between border-b border-white/10 px-5">
            <a href="{{ route('dashboard') }}" class="rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-300">
                <span class="block text-lg font-bold tracking-tight">Naxora CMS</span><span class="block text-xs text-slate-400">Naxas Innovations Limited</span>
            </a>
            <button x-ref="mobileClose" type="button" class="inline-flex size-11 items-center justify-center rounded-md hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-blue-300 lg:hidden" x-on:click="closeNavigation()" aria-label="Close navigation"><span aria-hidden="true">×</span></button>
        </div>
        <nav aria-label="Administration" class="flex-1 space-y-5 px-3 py-5">
            @foreach (\App\Support\AdminNavigation::groups() as $group)
                <section x-data="{ open: true }">
                    <button type="button" class="flex min-h-10 w-full items-center justify-between rounded-md px-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 focus-visible:ring-2 focus-visible:ring-blue-300" x-on:click="open = ! open" :aria-expanded="open.toString()"><span>{{ $group['label'] }}</span><span aria-hidden="true">⌄</span></button>
                    <ul x-show="open" class="mt-1 space-y-1">
                        @foreach ($group['items'] as $item)
                            <li>
                                @if ($item['route'] && Route::has($item['route']))
                                    <a href="{{ route($item['route']) }}" @class(['flex min-h-11 items-center rounded-md px-3 text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-300', 'bg-blue-600 text-white shadow-sm' => request()->routeIs($item['route']), 'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs($item['route'])]) @if(request()->routeIs($item['route'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                                @else
                                    <span class="flex min-h-11 cursor-not-allowed items-center justify-between rounded-md px-3 text-sm text-slate-500" aria-disabled="true"><span>{{ $item['label'] }}</span><span class="text-[10px] uppercase">Later</span></span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-4 text-xs text-slate-400"><p>Naxora CMS {{ config('app.version', '1.0.0') }}</p><p class="mt-1">Licensed corporate CMS</p></div>
    </aside>
    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-30 border-b border-admin-border bg-white/95 backdrop-blur">
            <div class="flex min-h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button x-ref="navigationToggle" type="button" class="inline-flex size-11 items-center justify-center rounded-md border border-admin-border text-navy-950 focus-visible:ring-2 focus-visible:ring-blue-600 lg:hidden" x-on:click="navigationOpen = true; $nextTick(() => $refs.mobileClose?.focus())" aria-controls="admin-navigation" :aria-expanded="navigationOpen.toString()" aria-label="Open navigation"><span aria-hidden="true">☰</span></button>
                <div class="min-w-0 flex-1"><x-admin.breadcrumb :items="$breadcrumbs" /></div>
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="hidden min-h-11 items-center rounded-md px-3 text-sm font-semibold text-blue-700 hover:bg-blue-50 focus-visible:ring-2 focus-visible:ring-blue-600 sm:inline-flex">View website</a>
                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="ghost" class="min-h-11" aria-label="Open administrator menu">{{ auth()->user()->initials() }}</flux:button>
                    <flux:menu class="w-64"><div class="px-3 py-2"><p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p></div><flux:menu.separator /><flux:menu.item href="{{ route('settings.profile') }}">Profile settings</flux:menu.item><form method="POST" action="{{ route('logout') }}">@csrf<flux:menu.item as="button" type="submit" class="w-full">Log out</flux:menu.item></form></flux:menu>
                </flux:dropdown>
            </div>
        </header>
        <main id="main-content" tabindex="-1" class="mx-auto w-full max-w-[100rem] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">{{ $slot }}</main>
        <footer class="px-6 py-5 text-center text-xs text-slate-500">Naxora — Premium Corporate Website CMS</footer>
    </div>
</div>
<flux:toast />
@fluxScripts
</body>
</html>
