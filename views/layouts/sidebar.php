<div class="drawer-side">
    <label for="main-drawer" class="drawer-overlay"></label>
    
    <aside class="bg-white w-64 h-full border-r border-gray-200 flex flex-col transition-all duration-300" id="app-sidebar">
        <!-- Logo Section -->
        <div class="h-16 flex items-center justify-center border-b border-gray-100 bg-gradient-to-br from-indigo-600 to-violet-600 text-white overflow-hidden">
            <div class="flex items-center gap-3 w-full px-6" id="sidebar-logo">
                <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-xl">rocket_launch</span>
                </div>
                <div class="whitespace-nowrap transition-opacity duration-300 menu-text">
                    <h2 class="text-lg font-bold tracking-tight"><?= APP_NAME ?></h2>
                </div>
            </div>
        </div>
        
        <!-- Menu -->
        <ul class="menu p-4 overflow-y-auto flex-1 gap-1 text-base-content/70">
            <li>
                <a href="<?= url('dashboard') ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active bg-indigo-50 text-indigo-600 font-semibold' : 'hover:bg-gray-50' ?> flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px]">dashboard</span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            
            <li class="menu-title mt-4 text-xs font-bold uppercase tracking-wider text-gray-400 pl-3 menu-text">Leads & Sales</li>
            
            <li>
                <a href="<?= url('leads') ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/leads') !== false && strpos($_SERVER['REQUEST_URI'], '/create') === false ? 'active bg-indigo-50 text-indigo-600 font-semibold' : 'hover:bg-gray-50' ?> flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px]">groups</span>
                    <span class="menu-text">All Leads</span>
                </a>
            </li>
            
            <li>
                <a href="<?= url('leads/create') ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/leads/create') !== false ? 'active bg-indigo-50 text-indigo-600 font-semibold' : 'hover:bg-gray-50' ?> flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px]">person_add</span>
                    <span class="menu-text">Add New Lead</span>
                </a>
            </li>
            
            <li class="menu-title mt-4 text-xs font-bold uppercase tracking-wider text-gray-400 pl-3 menu-text">Marketing</li>
            
            <li>
                <a href="<?= url('campaigns') ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/campaigns') !== false ? 'active bg-indigo-50 text-indigo-600 font-semibold' : 'hover:bg-gray-50' ?> flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px]">campaign</span>
                    <span class="menu-text">Campaigns</span>
                </a>
            </li>
            
            <li>
                <a href="<?= url('messages/templates') ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/messages/templates') !== false ? 'active bg-indigo-50 text-indigo-600 font-semibold' : 'hover:bg-gray-50' ?> flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px]">library_books</span>
                    <span class="menu-text">Templates</span>
                </a>
            </li>

            <?php if (hasPermission('manager')): ?>
            <li class="menu-title mt-4 text-xs font-bold uppercase tracking-wider text-gray-400 pl-3 menu-text">Management</li>
            
            <li>
                <a href="<?= url('projects') ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/projects') !== false ? 'active bg-indigo-50 text-indigo-600 font-semibold' : 'hover:bg-gray-50' ?> flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px]">apartment</span>
                    <span class="menu-text">Projects</span>
                </a>
            </li>
            
            <li>
                <a href="<?= url('clients') ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/clients') !== false ? 'active bg-indigo-50 text-indigo-600 font-semibold' : 'hover:bg-gray-50' ?> flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px]">domain</span>
                    <span class="menu-text">Clients</span>
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('admin')): ?>
            <li class="menu-title mt-4 text-xs font-bold uppercase tracking-wider text-gray-400 pl-3 menu-text">System</li>
            
            <li>
                <a href="<?= url('settings/integrations') ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/settings') !== false ? 'active bg-indigo-50 text-indigo-600 font-semibold' : 'hover:bg-gray-50' ?> flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px]">settings_input_component</span>
                    <span class="menu-text">Integrations</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <!-- User Profile (Bottom) -->
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            <a href="<?= url('settings/profile') ?>" class="flex items-center gap-3 p-2 rounded-lg hover:bg-white transition-colors group">
                <?php $currentUser = getCurrentUser(); ?>
                <div class="avatar placeholder flex-shrink-0">
                    <div class="bg-indigo-600 text-white rounded-full w-9 h-9 shadow-sm group-hover:shadow-md transition-shadow">
                        <span class="text-xs font-bold"><?= strtoupper(substr($currentUser['full_name'], 0, 2)) ?></span>
                    </div>
                </div>
                <div class="flex-1 min-w-0 menu-text transition-opacity duration-300">
                    <p class="text-sm font-semibold text-gray-900 truncate"><?= e($currentUser['full_name']) ?></p>
                    <p class="text-xs text-gray-500 truncate capitalize"><?= e($currentUser['role']) ?></p>
                </div>
            </a>
        </div>
    </aside>
</div>
