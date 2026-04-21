<!DOCTYPE html>
<html>
<head>
    <title>My App Dashboard</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f6f6f7; padding: 20px; }
        .card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 24px; max-width: 600px; margin: 0 auto; }
        h1 { color: #202223; font-size: 20px; }
        .status-badge { background: #e4f1eb; color: #008060; padding: 4px 8px; border-radius: 10px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Welcome to Devnest Connector <span class="status-badge">Connected</span></h1>
        <p>You are currently viewing the dashboard for: <strong>{{ $shop }}</strong></p>
        <hr>
        <p>This is your dummy page. From here, you can start building your App UI using Shopify Polaris or standard HTML/CSS.</p>
    </div>
</body>
</html>