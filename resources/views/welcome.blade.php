<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Libra') }} - Osnažujemo mlade umove</title>
    <meta name="description" content="Libra - Edukativne radionice koje pripremaju djecu za uspjeh u školi kroz radosno, praktično učenje.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/images/logo-libra.png" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-libra-warm-white text-libra-warm-text antialiased overflow-x-hidden">

    {{-- ============================================================ --}}
    {{-- NAVIGACIJA --}}
    {{-- ============================================================ --}}
    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 bg-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-20 gap-3 md:justify-between">
                {{-- Logo --}}
                <a href="#home" class="flex items-center shrink-0">
                    <img src="/images/libra-logo-vectors.svg" alt="Libra" class="h-10 w-auto" />
                </a>

                {{-- Mobile: Prijava + Menu Button (sits right after logo) --}}
                <div class="flex md:hidden items-center gap-2">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-lg bg-gradient-to-r from-libra-amber-500 to-libra-coral-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">
                                Ploča
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg bg-gradient-to-r from-libra-amber-500 to-libra-coral-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm">
                                Prijava
                            </a>
                        @endauth
                    @endif
                    <button id="mobile-menu-toggle" class="p-2 rounded-lg hover:bg-libra-amber-50 transition-colors" aria-label="Otvori izbornik">
                        <svg class="menu-open-icon size-6 text-libra-warm-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="menu-close-icon size-6 text-libra-warm-text hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Desktop Nav Links --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="#home" class="nav-link text-sm font-medium text-libra-warm-text-secondary hover:text-libra-amber-600">Početna</a>
                    <a href="#about" class="nav-link text-sm font-medium text-libra-warm-text-secondary hover:text-libra-amber-600">O nama</a>
                    <a href="#workshops" class="nav-link text-sm font-medium text-libra-warm-text-secondary hover:text-libra-amber-600">Radionice</a>
                    <a href="#contact" class="nav-link text-sm font-medium text-libra-warm-text-secondary hover:text-libra-amber-600">Kontakt</a>
                </div>

                {{-- Auth Buttons (Desktop) --}}
                <div class="hidden md:flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-libra-secondary text-sm py-2 px-5">Nadzorna ploča</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-libra-warm-text-secondary hover:text-libra-amber-600 transition-colors">Prijava</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-libra-primary text-sm py-2 px-5">Registracija</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden md:hidden bg-white/95 backdrop-blur-lg border-t border-libra-amber-100">
            <div class="px-4 py-2 space-y-0.5 max-w-[200px]">
                <a href="#home" class="block px-3 py-1.5 text-xs font-medium text-libra-warm-text-secondary hover:bg-libra-amber-50 rounded-lg transition-colors">Početna</a>
                <a href="#about" class="block px-3 py-1.5 text-xs font-medium text-libra-warm-text-secondary hover:bg-libra-amber-50 rounded-lg transition-colors">O nama</a>
                <a href="#workshops" class="block px-3 py-1.5 text-xs font-medium text-libra-warm-text-secondary hover:bg-libra-amber-50 rounded-lg transition-colors">Radionice</a>
                <a href="#contact" class="block px-3 py-1.5 text-xs font-medium text-libra-warm-text-secondary hover:bg-libra-amber-50 rounded-lg transition-colors">Kontakt</a>
                @if (Route::has('login'))
                    <div class="pt-2 border-t border-libra-amber-100 flex flex-col gap-1">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-libra-secondary text-xs text-center py-1.5">Nadzorna ploča</a>
                        @else
                            <a href="{{ route('login') }}" class="text-xs text-center font-medium text-libra-warm-text-secondary py-1.5 hover:text-libra-amber-600 transition-colors">Prijava</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-libra-primary text-xs text-center py-1.5">Registracija</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    {{-- ============================================================ --}}
    {{-- HERO SEKCIJA --}}
    {{-- ============================================================ --}}
    <section id="home" class="relative min-h-screen flex items-center bg-gradient-libra-hero overflow-hidden pt-20">
        {{-- Decorative floating shapes --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute top-24 left-[8%] w-16 h-16 sm:w-20 sm:h-20 bg-libra-amber-200/40 rounded-full animate-float"></div>
            <div class="absolute top-40 right-[12%] w-24 h-24 sm:w-32 sm:h-32 bg-libra-purple-200/30 rounded-full animate-float-delayed"></div>
            <div class="absolute bottom-32 left-1/4 w-12 h-12 sm:w-16 sm:h-16 bg-libra-coral-200/40 rounded-lg rotate-45 animate-float"></div>
            <div class="absolute top-1/3 right-1/3 w-10 h-10 sm:w-12 sm:h-12 bg-libra-amber-300/30 rounded-full animate-pulse-soft"></div>
            <div class="absolute bottom-24 right-[8%] w-20 h-20 sm:w-24 sm:h-24 bg-libra-purple-100/40 rounded-full animate-float-delayed"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                {{-- Text content --}}
                <div class="text-center lg:text-left">
                    <div data-aos="fade-up">
                        <span class="inline-block px-4 py-1.5 bg-libra-amber-100 text-libra-amber-700 text-sm font-medium rounded-full mb-6">
                            Pripremamo djecu za svijetlu budućnost
                        </span>
                    </div>
                    <h1 data-aos="fade-up" data-aos-delay="100" class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-tight mb-6">
                        Osnažujemo mlade umove
                        <span class="text-gradient-libra block mt-1">kroz radosno učenje</span>
                    </h1>
                    <p data-aos="fade-up" data-aos-delay="200" class="text-lg sm:text-xl text-libra-warm-text-secondary max-w-xl mx-auto lg:mx-0 mb-10 leading-relaxed">
                        U Libri vjerujemo da svako dijete zaslužuje najbolji početak. Naše zanimljive radionice grade samopouzdanje,
                        znatiželju i spremnost za školu kroz praktično, dobi prilagođeno obrazovanje.
                    </p>
                    <div data-aos="fade-up" data-aos-delay="300" class="flex flex-col sm:flex-row items-center lg:items-start justify-center lg:justify-start gap-4">
                        <a href="#workshops" class="btn-libra-primary text-base px-8 py-3.5 w-full sm:w-auto text-center">
                            Istraži radionice
                        </a>
                        <a href="#about" class="btn-libra-secondary text-base px-8 py-3.5 w-full sm:w-auto text-center">
                            Saznaj više
                        </a>
                    </div>
                </div>

                {{-- Hero image --}}
                <div data-aos="fade-left" data-aos-delay="200" class="relative">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl">
                        <img
                            src="/images/libra-profil.jpg"
                            alt="Dijete sjedi na vagi Libre s knjigama — akvarel ilustracija"
                            class="w-full h-auto object-cover aspect-[4/3]"
                            loading="eager"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-libra-amber-600/10 to-transparent"></div>
                    </div>
                    {{-- Decorative accent behind image --}}
                    <div class="absolute -bottom-4 -right-4 w-full h-full rounded-2xl bg-gradient-to-br from-libra-amber-200 to-libra-purple-200 -z-10"></div>
                </div>
            </div>
        </div>

        {{-- Bottom wave divider --}}
        <div class="absolute bottom-0 left-0 right-0" aria-hidden="true">
            <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 32L48 37.3C96 43 192 53 288 53.3C384 53 480 43 576 37.3C672 32 768 32 864 37.3C960 43 1056 53 1152 53.3C1248 53 1344 43 1392 37.3L1440 32V80H1392C1344 80 1248 80 1152 80C1056 80 960 80 864 80C768 80 672 80 576 80C480 80 384 80 288 80C192 80 96 80 48 80H0V32Z" fill="white"/>
            </svg>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- O NAMA SEKCIJA --}}
    {{-- ============================================================ --}}
    <section id="about" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Section header --}}
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="inline-block px-4 py-1.5 bg-libra-purple-100 text-libra-purple-700 text-sm font-medium rounded-full mb-4">
                    O nama
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold text-libra-warm-text mb-4">
                    Gradimo temelje za
                    <span class="text-gradient-libra">cjeloživotno učenje</span>
                </h2>
                <p class="text-libra-warm-text-secondary text-lg leading-relaxed">
                    Libra je posvećena pružanju visokokvalitetnih obrazovnih iskustava koja pripremaju
                    djecu za školu i inspiriraju ljubav prema učenju od samog početka.
                </p>
            </div>

            {{-- Value cards --}}
            <div class="grid md:grid-cols-3 gap-8 mb-20">
                {{-- Card 1: Poticajno okruženje --}}
                <div class="libra-card bg-white rounded-2xl p-8 border border-libra-amber-100 shadow-sm" data-aos="zoom-in" data-aos-delay="0">
                    <div class="w-14 h-14 bg-libra-amber-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-libra-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-libra-warm-text mb-3">Poticajno okruženje</h3>
                    <p class="text-libra-warm-text-secondary leading-relaxed">
                        Sigurni, topli i ohrabrujući prostori u kojima se djeca osjećaju samopouzdano istraživati, postavljati pitanja i rasti vlastitim tempom.
                    </p>
                </div>

                {{-- Card 2: Stručni edukatori --}}
                <div class="libra-card bg-white rounded-2xl p-8 border border-libra-coral-100 shadow-sm" data-aos="zoom-in" data-aos-delay="100">
                    <div class="w-14 h-14 bg-libra-coral-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-libra-coral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-libra-warm-text mb-3">Stručni edukatori</h3>
                    <p class="text-libra-warm-text-secondary leading-relaxed">
                        Naš tim iskusnih, certificiranih edukatora unosi strast i stručnost u svaku radionicu, osiguravajući nastavu najviše kvalitete.
                    </p>
                </div>

                {{-- Card 3: Provjerene metode --}}
                <div class="libra-card bg-white rounded-2xl p-8 border border-libra-purple-100 shadow-sm" data-aos="zoom-in" data-aos-delay="200">
                    <div class="w-14 h-14 bg-libra-purple-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-libra-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-libra-warm-text mb-3">Provjerene metode</h3>
                    <p class="text-libra-warm-text-secondary leading-relaxed">
                        Istraživanjem potkrijepljeni pristupi učenju koji čine učenje zabavnim i učinkovitim, gradeći stvarne vještine koje će djeca koristiti tijekom cijelog obrazovanja.
                    </p>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 bg-libra-warm-cream/50 rounded-2xl p-8 lg:p-12" data-aos="fade-up">
                <div class="text-center">
                    <div class="stat-number text-3xl sm:text-4xl font-bold text-libra-amber-600 mb-1">500+</div>
                    <div class="text-sm text-libra-warm-text-secondary font-medium">Polaznika</div>
                </div>
                <div class="text-center">
                    <div class="stat-number text-3xl sm:text-4xl font-bold text-libra-coral-500 mb-1">25+</div>
                    <div class="text-sm text-libra-warm-text-secondary font-medium">Aktivnih radionica</div>
                </div>
                <div class="text-center">
                    <div class="stat-number text-3xl sm:text-4xl font-bold text-libra-purple-500 mb-1">98%</div>
                    <div class="text-sm text-libra-warm-text-secondary font-medium">Zadovoljstvo roditelja</div>
                </div>
                <div class="text-center">
                    <div class="stat-number text-3xl sm:text-4xl font-bold text-libra-red-500 mb-1">10+</div>
                    <div class="text-sm text-libra-warm-text-secondary font-medium">Godina iskustva</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- RADIONICE SEKCIJA --}}
    {{-- ============================================================ --}}
    <section id="workshops" class="py-20 lg:py-28 bg-gradient-libra-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Section header --}}
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="inline-block px-4 py-1.5 bg-libra-coral-100 text-libra-coral-600 text-sm font-medium rounded-full mb-4">
                    Naše radionice
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold text-libra-warm-text mb-4">
                    Pronađite savršenu
                    <span class="text-gradient-libra">radionicu</span>
                </h2>
                <p class="text-libra-warm-text-secondary text-lg leading-relaxed">
                    Svaki program je pažljivo osmišljen kako bi angažirao mlade učenike i izgradio ključne vještine za spremnost za školu.
                </p>
            </div>

            {{-- Workshop cards grid --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse ($workshops as $workshop)
                    @php $colors = $workshop->colorClasses(); @endphp
                    <div class="workshop-card bg-white rounded-2xl p-8 shadow-sm" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 100 }}">
                        <div class="w-14 h-14 {{ $colors['icon_bg'] }} rounded-xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 {{ $colors['icon_text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $workshop->iconSvgPath() }}" />
                            </svg>
                        </div>
                        <span class="inline-block px-3 py-1 {{ $colors['badge_bg'] }} {{ $colors['badge_text'] }} text-xs font-medium rounded-full mb-4">{{ $workshop->dobna_skupina }}</span>
                        <h3 class="text-xl font-bold text-libra-warm-text mb-3">{{ $workshop->naziv }}</h3>
                        <p class="text-libra-warm-text-secondary leading-relaxed mb-6">
                            {{ $workshop->opis }}
                        </p>
                        <a href="#contact" class="inline-flex items-center text-sm font-semibold {{ $colors['link_text'] }} {{ $colors['link_hover'] }} group transition-colors">
                            Saznaj više
                            <svg class="w-4 h-4 ml-1 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                @empty
                    <div class="sm:col-span-2 lg:col-span-3 text-center py-16">
                        <p class="text-lg text-libra-warm-text-secondary">Radionice dolaze uskoro!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- KONTAKT SEKCIJA --}}
    {{-- ============================================================ --}}
    <section id="contact" class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Section header --}}
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <span class="inline-block px-4 py-1.5 bg-libra-amber-100 text-libra-amber-700 text-sm font-medium rounded-full mb-4">
                    Kontaktirajte nas
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold text-libra-warm-text mb-4">
                    Spremni za <span class="text-gradient-libra">početak</span>?
                </h2>
                <p class="text-libra-warm-text-secondary text-lg leading-relaxed">
                    Imate pitanja o našim programima? Rado ćemo vam pomoći. Javite nam se i pomozite svom djetetu da napreduje.
                </p>
            </div>

            <div class="grid lg:grid-cols-5 gap-12 max-w-5xl mx-auto">
                {{-- Contact Form --}}
                <div class="lg:col-span-3" data-aos="fade-right">
                    <form class="space-y-6">
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-libra-warm-text mb-2">Ime i prezime</label>
                                <input type="text" id="name" name="name"
                                    class="w-full px-4 py-3 rounded-xl border border-zinc-200 focus:ring-2 focus:ring-libra-amber-400 focus:border-transparent outline-none transition-all text-sm bg-white"
                                    placeholder="Vaše ime">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-libra-warm-text mb-2">E-pošta</label>
                                <input type="email" id="email" name="email"
                                    class="w-full px-4 py-3 rounded-xl border border-zinc-200 focus:ring-2 focus:ring-libra-amber-400 focus:border-transparent outline-none transition-all text-sm bg-white"
                                    placeholder="vasa@email.com">
                            </div>
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-libra-warm-text mb-2">Predmet</label>
                            <input type="text" id="subject" name="subject"
                                class="w-full px-4 py-3 rounded-xl border border-zinc-200 focus:ring-2 focus:ring-libra-amber-400 focus:border-transparent outline-none transition-all text-sm bg-white"
                                placeholder="Kako vam možemo pomoći?">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-libra-warm-text mb-2">Poruka</label>
                            <textarea id="message" name="message" rows="5"
                                class="w-full px-4 py-3 rounded-xl border border-zinc-200 focus:ring-2 focus:ring-libra-amber-400 focus:border-transparent outline-none transition-all text-sm resize-none bg-white"
                                placeholder="Recite nam nešto o svom djetetu i što tražite..."></textarea>
                        </div>
                        <button type="button" class="btn-libra-primary text-sm px-8 py-3 w-full sm:w-auto">
                            Pošalji poruku
                        </button>
                    </form>
                </div>

                {{-- Contact Info Sidebar --}}
                <div class="lg:col-span-2 space-y-8" data-aos="fade-left">
                    <div>
                        <h3 class="text-lg font-bold text-libra-warm-text mb-5">Kontakt informacije</h3>
                        <div class="space-y-5">
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 bg-libra-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-libra-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-libra-warm-text">E-pošta</p>
                                    <p class="text-sm text-libra-warm-text-secondary">hello@libra-edu.com</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 bg-libra-coral-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-libra-coral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-libra-warm-text">Telefon</p>
                                    <p class="text-sm text-libra-warm-text-secondary">+385 1 234 5678</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 bg-libra-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-libra-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-libra-warm-text">Lokacija</p>
                                    <p class="text-sm text-libra-warm-text-secondary">Zagreb, Hrvatska</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Social links --}}
                    <div>
                        <h3 class="text-lg font-bold text-libra-warm-text mb-4">Pratite nas</h3>
                        <div class="flex gap-3">
                            <a href="#" class="w-11 h-11 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-500 hover:bg-libra-amber-100 hover:text-libra-amber-600 transition-colors" aria-label="Facebook">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="#" class="w-11 h-11 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-500 hover:bg-libra-coral-100 hover:text-libra-coral-500 transition-colors" aria-label="Instagram">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            </a>
                            <a href="#" class="w-11 h-11 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-500 hover:bg-libra-purple-100 hover:text-libra-purple-500 transition-colors" aria-label="LinkedIn">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- PODNOŽJE --}}
    {{-- ============================================================ --}}
    <footer class="bg-gradient-libra-footer text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-12">
                {{-- Brand --}}
                <div>
                    <div class="flex items-center mb-4">
                        <img src="/images/libra-logo-vectors.svg" alt="Libra" class="h-10 w-auto brightness-0 invert" />
                    </div>
                    <p class="text-white/80 text-sm leading-relaxed max-w-xs">
                        Osnažujemo mlade umove kroz radosno, istraživanjem potkrijepljeno obrazovanje. Pripremamo djecu za školu i cjeloživotno učenje.
                    </p>
                </div>

                {{-- Brze poveznice --}}
                <div>
                    <h4 class="font-bold text-lg mb-4">Brze poveznice</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#home" class="text-white/80 text-sm hover:text-white transition-colors">Početna</a></li>
                        <li><a href="#about" class="text-white/80 text-sm hover:text-white transition-colors">O nama</a></li>
                        <li><a href="#workshops" class="text-white/80 text-sm hover:text-white transition-colors">Radionice</a></li>
                        <li><a href="#contact" class="text-white/80 text-sm hover:text-white transition-colors">Kontakt</a></li>
                        <li>
                            <a href="/images/Cjenik-Libra-2026.png" download
                               class="inline-flex items-center gap-2 text-white/80 text-sm hover:text-white transition-colors">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Cjenik 2026
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Kontakt --}}
                <div>
                    <h4 class="font-bold text-lg mb-4">Kontakt</h4>
                    <ul class="space-y-2.5 text-white/80 text-sm">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            hello@libra-edu.com
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                            +385 1 234 5678
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            Zagreb, Hrvatska
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/20 mt-12 pt-8 text-center text-white/60 text-sm">
                &copy; {{ date('Y') }} Libra. Sva prava pridržana.
                <br>
                <p>Design by Odin</p>
                <br>
                <a href="#" class="text-white/80 hover:text-white transition-colors">Privacy Policy</a> | <a href="#" class="text-white/80 hover:text-white transition-colors">Terms of Service</a>
            </div>
        </div>
    </footer>

</body>
</html>
