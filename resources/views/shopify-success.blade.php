<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Installed</title>
    <script src="https://unpkg.com/@shopify/app-bridge"></script>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f4f6f8; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        h1 { color: #008060; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🎉 Success!</h1>
        <p>The app <strong>Devnest Connector</strong> is now connected to:</p>
        <code>{{ $shop }}</code>
        <p>You can now start building your awesome dashboard here.</p>
    </div>
</body>
</html>