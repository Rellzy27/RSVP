<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        
        <!-- Tambahkan script untuk Alpine.js (dibutuhkan untuk menu mobile)
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> -->
    </head>
    <body class="min-h-screen bg-zinc-100 dark:bg-zinc-900 antialiased">
        <div x-data="{ open: false }">
            <!-- Header/Navigasi -->
            <header class="bg-white dark:bg-zinc-800 shadow-sm sticky top-0 z-50">
                <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex-shrink-0 flex items-center">
                                <!-- <a href="{{ route('home') }}" wire:navigate>
                                    <x-app-logo-icon class="h-9 w-9 fill-current text-black dark:text-white" />
                                </a> -->
                                <a href="{{ route('home') }}" wire:navigate class="font-bold ml-2 text-lg text-gray-900 dark:text-white">
                                    Amazing Journey 6
                                </a>
                            </div>
                        </div>

                        <!-- Menu Desktop -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('home') }}" wire:navigate 
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('home') ? 'border-primary-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-zinc-700 hover:text-gray-700 dark:hover:text-gray-300' }} text-sm font-medium">
                                Daftar
                            </a>
                            <a href="{{ route('konfirmasi') }}" wire:navigate 
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('konfirmasi') ? 'border-primary-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-zinc-700 hover:text-gray-700 dark:hover:text-gray-300' }} text-sm font-medium">
                                Konfirmasi Bayar
                            </a>
                            <a href="{{ route('tiket') }}" wire:navigate 
                               class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('tiket') ? 'border-primary-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-zinc-700 hover:text-gray-700 dark:hover:text-gray-300' }} text-sm font-medium">
                                Cek Tiket
                            </a>
                            
                            @auth
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('admin.verifikasi') }}" wire:navigate 
                                       class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.verifikasi') ? 'border-primary-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-zinc-700 hover:text-gray-700 dark:hover:text-gray-300' }} text-sm font-medium">
                                        Admin Verifikasi
                                    </a>
                                @endif
                                <a href="{{ route('dashboard') }}" wire:navigate 
                                   class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-primary-500 text-gray-900 dark:text-white' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-zinc-700 hover:text-gray-700 dark:hover:text-gray-300' }} text-sm font-medium">
                                    Dashboard
                                </a>
                            @endauth
                        </div>

                        <!-- Tombol Hamburger Menu Mobile -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-zinc-800 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </nav>

                <!-- Menu Dropdown Mobile -->
                <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <a href="{{ route('home') }}" wire:navigate 
                           class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('home') ? 'border-primary-500 text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/10' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600 hover:text-gray-800 dark:hover:text-gray-300' }} text-base font-medium">
                           Daftar
                        </a>
                        <a href="{{ route('konfirmasi') }}" wire:navigate 
                           class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('konfirmasi') ? 'border-primary-500 text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/10' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600 hover:text-gray-800 dark:hover:text-gray-300' }} text-base font-medium">
                           Konfirmasi Bayar
                        </a>
                        <a href="{{ route('tiket') }}" wire:navigate 
                           class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('tiket') ? 'border-primary-500 text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/10' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600 hover:text-gray-800 dark:hover:text-gray-300' }} text-base font-medium">
                           Cek Tiket
                        </a>
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.verifikasi') }}" wire:navigate 
                                   class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('admin.verifikasi') ? 'border-primary-500 text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/10' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600 hover:text-gray-800 dark:hover:text-gray-300' }} text-base font-medium">
                                    Admin Verifikasi
                                </a>
                            @endif
                            <a href="{{ route('dashboard') }}" wire:navigate 
                               class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('dashboard') ? 'border-primary-500 text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/10' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600 hover:text-gray-800 dark:hover:text-gray-300' }} text-base font-medium">
                                Dashboard
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Konten Halaman -->
            <main class="py-10">
                <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        @fluxScripts
    </body>
</html>