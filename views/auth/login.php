<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    
    <!-- Tailwind CSS + DaisyUI -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css?v=fix" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <div class="card w-full max-w-md bg-base-100 shadow-2xl">
        <div class="card-body">
            <!-- Logo/Header -->
            <div class="text-center mb-6">
                <h1 class="text-4xl font-bold text-primary"><?= APP_NAME ?></h1>
                <p class="text-sm text-gray-500 mt-2">Real Estate Lead Management System</p>
            </div>
            
            <!-- Flash Message -->
            <?php $flash = getFlashMessage(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : $flash['type'] ?> mb-4">
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="<?= url('login') ?>" class="space-y-4">
                <?= csrfField() ?>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Username or Email</span>
                    </label>
                    <input type="text" name="username" placeholder="Enter your username" 
                           class="input input-bordered" required autofocus>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <input type="password" name="password" placeholder="Enter your password" 
                           class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" name="remember" class="checkbox checkbox-sm" />
                        <span class="label-text">Remember me</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">
                    Login
                </button>
            </form>
            
            <!-- Footer -->
            <div class="text-center mt-6 text-sm text-gray-500">
                <p>Default credentials: admin / admin123</p>
                <p class="mt-2">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
    
</body>
</html>
