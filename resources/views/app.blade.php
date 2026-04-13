<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-from-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SPA Laravel React</title>
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    </head>
    <body class="antialiased">
        <div id="root"></div>
    </body>
</html>
