<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Realvibe CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css?v=fix" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F3F4F6; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="glass-card max-w-lg w-full rounded-2xl p-8 md:p-12 text-center animate-bounce-in">
        <div class="mb-6 inline-flex items-center justify-center w-20 h-20 rounded-full bg-indigo-50 text-indigo-600 mb-6">
            <span class="material-symbols-outlined text-4xl">travel_explore</span>
        </div>
        
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Page Not Found</h1>
        <p class="text-gray-500 mb-8 text-lg">Sorry, we couldn't find the page you're looking for. It might have been moved or deleted.</p>
        
        <div class="flex flex-col gap-3">
            <a href="<?= BASE_URL ?>" class="btn btn-primary btn-block gap-2 normal-case text-lg h-12">
                <span class="material-symbols-outlined">dashboard</span>
                Back to Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-ghost btn-block normal-case">
                Go Back
            </a>
        </div>
    </div>
</body>
</html>
