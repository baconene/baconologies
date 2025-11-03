<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Baconologies') }} - Engineering Smart Solutions for a Digital Future</title>
        
        <!-- Meta tags for SEO -->
        <meta name="description" content="Baconologies.com - Enterprise IT consulting specializing in Learning Management Systems, Utility Systems, Billing Systems, and Geomapping Services. Blending warmth and creativity with innovation and precision.">
        <meta name="keywords" content="IT consulting, LMS, Learning Management Systems, Utility Management, Billing Systems, Geomapping, Oracle OUAF, Oracle CCB, Laravel, Vue.js">
        <meta name="author" content="Baconologies.com">
        
        <!-- Open Graph tags -->
        <meta property="og:title" content="Baconologies - Engineering Smart Solutions for a Digital Future">
        <meta property="og:description" content="Enterprise IT consulting specializing in custom eLearning platforms, utility management, billing systems, and geospatial mapping tools.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url('/') }}">
        
        <!-- Favicon -->
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&family=inter:400,500,600,700,800" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
