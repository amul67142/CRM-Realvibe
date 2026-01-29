<?php requireLogin(); $user = getCurrentUser(); ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?></title>
    
    <!-- Tailwind CSS + DaisyUI -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@latest/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Chart.js for dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">
</head>
<body class="bg-base-200">
    
    <div class="drawer lg:drawer-open">
        <input id="main-drawer" type="checkbox" class="drawer-toggle" />
        
        <!-- Main content -->
        <div class="drawer-content flex flex-col">
            <!-- Navbar -->
            <div class="navbar bg-base-100 shadow-md">
                <div class="flex-none lg:hidden">
                    <label for="main-drawer" class="btn btn-square btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </label>
                </div>
                
                <div class="flex-1">
                    <h1 class="text-xl font-bold ml-2"><?= APP_NAME ?></h1>
                </div>
                
                <div class="flex-none gap-2">
                    <!-- Notifications -->
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" id="notificationBell" class="btn btn-ghost btn-circle">
                            <div class="indicator">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span id="notificationCount" class="badge badge-xs badge-primary indicator-item" style="display:none;">0</span>
                            </div>
                        </label>
                        <div tabindex="0" class="dropdown-content mt-3 w-80 shadow-2xl bg-base-100 rounded-box z-50">
                            <div class="p-3 border-b border-base-300 flex justify-between items-center">
                                <h3 class="font-bold text-sm">Notifications</h3>
                                <button id="markAllNotificationsRead" class="btn btn-ghost btn-xs">Mark all read</button>
                            </div>
                            <ul id="notificationList" class="menu menu-compact max-h-96 overflow-y-auto">
                                <li class="p-4 text-center text-gray-500">
                                    <p>Loading...</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- User menu -->
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle avatar placeholder">
                            <div class="bg-neutral text-neutral-content rounded-full w-10">
                                <span><?= strtoupper(substr($user['full_name'], 0, 2)) ?></span>
                            </div>
                        </label>
                        <ul tabindex="0" class="mt-3 p-2 shadow menu menu-compact dropdown-content bg-base-100 rounded-box w-52">
                            <li class="menu-title">
                                <span><?= e($user['full_name']) ?></span>
                                <span class="text-xs"><?= ucfirst($user['role']) ?></span>
                            </li>
                            <li><a href="<?= url('logout') ?>">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Page content -->
            <div class="p-4 lg:p-6">
                <!-- Flash Message -->
                <?php $flash = getFlashMessage(); if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : $flash['type'] ?> mb-4 flash-message">
                        <span><?= e($flash['message']) ?></span>
                    </div>
                <?php endif; ?>
