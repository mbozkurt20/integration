<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name', 'Restaurant Panel') }}</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        body {
            background-color: #f6f7f9;
        }
    </style>
</head>
<body class="min-h-screen">

{{-- NAVBAR --}}
<nav class="bg-gray-900 text-white px-6 py-3 flex justify-between items-center shadow">
    <h1 class="text-lg font-bold">Restaurant Panel</h1>
    <ul class="flex space-x-4">
        <li><a href="/" class="hover:text-yellow-400">Dashboard</a></li>
        <li><a href="/restaurants" class="hover:text-yellow-400">Restaurants</a></li>
        <li><a href="/providers" class="hover:text-yellow-400">Providers</a></li>
    </ul>
</nav>

{{-- CONTENT --}}
<main class="py-6 px-4">
    @yield('content')
</main>

@yield('scripts')

<script>
    // Tüm Axios isteklerinde CSRF token’i otomatik gönderelim
    axios.defaults.headers.common['X-CSRF-TOKEN'] =
        document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>

</body>
</html>
