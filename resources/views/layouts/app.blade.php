<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My App Dashboard</title>

    <meta name="shopify-api-key" content="{{ env('SHOPIFY_API_KEY') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

</head>

<body>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://unpkg.com/@shopify/app-bridge"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const host = new URLSearchParams(window.location.search).get("host");

            if (!host) {
                console.error("Missing host parameter");
                return;
            }

            const AppBridge = window['app-bridge'];

            const app = AppBridge.createApp({
                apiKey: "{{ env('SHOPIFY_API_KEY') }}",
                host: host,
                forceRedirect: true
            });

        });
    </script>

</body>
</html>