<?php
// 404 Error Page
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@latest/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-base-200">
    <div class="text-center">
        <h1 class="text-9xl font-bold text-primary">404</h1>
        <p class="text-2xl font-semibold mt-4">Page Not Found</p>
        <p class="text-gray-600 mt-2 mb-8">The page you're looking for doesn't exist.</p>
        <a href="<?= BASE_URL ?>" class="btn btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>
