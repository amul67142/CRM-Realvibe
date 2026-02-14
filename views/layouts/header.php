<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?></title>
    
    <!-- Tailwind CSS + DaisyUI -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css?v=fix" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5', /* Indigo 600 */
                        secondary: '#7C3AED', /* Violet 600 */
                        accent: '#F59E0B', /* Amber 500 */
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/modern-nav.css') ?>">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col font-sans text-gray-900">

    <?php 
    requireLogin(); 
    $user = getCurrentUser();
    ?>

    <!-- Navbar -->
    <div class="glass-nav">
        <div class="navbar max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Navbar Start (Logo) -->
            <div class="navbar-start">
                <div class="dropdown lg:hidden">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                    </div>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li><a href="<?= url('dashboard') ?>" class="<?= isActive('dashboard') ?>">Dashboard</a></li>
                        <li><a href="<?= url('leads') ?>" class="<?= isActive('leads') ?>">Leads</a></li>
                        <li><a href="<?= url('clients') ?>" class="<?= isActive('clients') ?>">Clients</a></li>
                        <li><a href="<?= url('projects') ?>" class="<?= isActive('projects') ?>">Projects</a></li>
                        <li><a href="<?= url('campaigns') ?>" class="<?= isActive('campaigns') ?>">Campaigns</a></li>
                        <li><a href="<?= url('messages/templates') ?>" class="<?= isActive('messages') ?>">Messages</a></li>
                    </ul>
                </div>
                <a href="<?= url('dashboard') ?>" class="nav-brand text-xl">
                    <span class="material-symbols-outlined text-3xl">rocket_launch</span>
                    <?= APP_NAME ?>
                </a>
            </div>

            <!-- Navbar Center (Desktop Menu) -->
            <div class="navbar-center hidden lg:flex">
                <ul class="flex items-center gap-1">
                    <li><a href="<?= url('dashboard') ?>" class="nav-link <?= isActive('dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= url('leads') ?>" class="nav-link <?= isActive('leads') ?>">Leads</a></li>
                    <li><a href="<?= url('clients') ?>" class="nav-link <?= isActive('clients') ?>">Clients</a></li>
                    <li><a href="<?= url('projects') ?>" class="nav-link <?= isActive('projects') ?>">Projects</a></li>
                    <li><a href="<?= url('campaigns') ?>" class="nav-link <?= isActive('campaigns') ?>">Campaigns</a></li>
                    <li><a href="<?= url('messages/templates') ?>" class="nav-link <?= isActive('messages') ?>">Messages</a></li>
                </ul>
            </div>

            <!-- Navbar End (User & Notifications) -->
            <div class="navbar-end gap-2">
                <!-- Notifications -->
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                        <div class="indicator">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="badge badge-xs badge-primary indicator-item" id="notification-badge" style="display:none"></span>
                        </div>
                    </div>
                    <div tabindex="0" class="dropdown-content z-[1] card card-compact w-80 p-2 shadow bg-base-100 mt-3">
                        <div class="card-body">
                            <h3 class="card-title text-base">Notifications</h3>
                            <div id="notification-list" class="max-h-60 overflow-y-auto space-y-2">
                                <p class="text-sm text-gray-500 text-center py-4">No new notifications</p>
                            </div>
                            <div class="card-actions justify-center mt-2">
                                <a href="<?= url('notifications') ?>" class="btn btn-xs btn-ghost text-primary">View All</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="nav-profile-btn flex items-center gap-2 cursor-pointer p-1 pr-3">
                        <div class="avatar placeholder">
                            <div class="bg-indigo-600 text-white rounded-full w-8 h-8">
                                <span class="text-xs font-bold"><?= strtoupper(substr($user['full_name'], 0, 2)) ?></span>
                            </div>
                        </div>
                        <div class="hidden md:block text-left text-sm">
                            <div class="font-semibold leading-none"><?= e($user['full_name']) ?></div>
                            <div class="text-xs text-gray-500 leading-none mt-1 capitalize"><?= e($user['role']) ?></div>
                        </div>
                        <span class="material-symbols-outlined text-gray-400 text-sm hidden md:block">expand_more</span>
                    </div>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li>
                            <a href="<?= url('settings/profile') ?>" class="justify-between">
                                Profile
                                <span class="badge badge-ghost">New</span>
                            </a>
                        </li>
                        <li><a href="<?= url('settings/integrations') ?>">Integrations</a></li>
                        <div class="divider my-1"></div>
                        <li><a href="<?= url('logout') ?>" class="text-red-600 hover:bg-red-50">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Wrapper (Replacing .drawer-content) -->
    <main class="flex-grow main-container w-full">
        <!-- Flash Messages -->
        <?php $flash = getFlashMessage(); if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : $flash['type'] ?> mb-6 shadow-sm border border-<?= $flash['type'] === 'error' ? 'red' : 'green' ?>-200">
                <span class="material-symbols-outlined">
                    <?= $flash['type'] === 'error' ? 'error' : 'check_circle' ?>
                </span>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>
