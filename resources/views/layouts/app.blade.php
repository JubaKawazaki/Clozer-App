<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Laravel')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/@inertiajs/react@latest/dist/react.umd.js"></script>
        <script src="https://unpkg.com/@inertiajs/react-router@latest/dist/react-router.umd.js"></script>
        <script src="https://unpkg.com/@inertiajs/react-router@latest/dist/react-router.umd.js"></script>
        <script src="https://unpkg.com/@inertiajs/react@latest/dist/inertia.umd.js"></script>
        <script src="https://unpkg.com/@inertiajs/react@latest/dist/inertia.umd.js"></script>
        <script src="https://unpkg.com/@inertiajs/react@latest/dist/inertia.umd.js"></script>

        <!-- Tailwind -->
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                        },
                        colors: {
                            primary: '#4F46E5',
                            secondary: '#6366F1',
                            accent: '#818CF8',
                            danger: '#EF4444',
                            success: '#10B981',
                            warning: '#F59E0B',
                        },
                    },
                },
            }
        </script>

        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white">
        @yield('content')
    </body>
</html>
