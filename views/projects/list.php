<?php
$pageTitle = 'Projects';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Projects</h2>
    <a href="<?= url('projects/create') ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Add Project
    </a>
</div>

<!-- Filters -->
<?php if (!empty($clients)): ?>
<div class="card bg-base-100 shadow mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('projects') ?>" class="flex gap-4 items-end">
            <div class="form-control flex-1">
                <label class="label"><span class="label-text">Filter by Client</span></label>
                <select name="client_id" class="select select-bordered select-sm">
                    <option value="">All Clients</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>" <?= selected($filters['client_id'] ?? '', $client['id']) ?>>
                            <?= e($client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                <a href="<?= url('projects') ?>" class="btn btn-ghost btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Projects Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($projects)): ?>
        <div class="col-span-full">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state-icon">🏗️</div>
                        <p>No projects found</p>
                        <a href="<?= url('projects/create') ?>" class="btn btn-primary btn-sm mt-4">Add Your First Project</a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($projects as $project): ?>
            <div class="card bg-base-100 shadow hover:shadow-xl transition-shadow">
                <div class="card-body">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="text-xs font-mono text-gray-400 mb-1">ID: <?= $project['id'] ?></div>
                            <h3 class="card-title text-lg"><?= e($project['project_name']) ?></h3>
                        </div>
                        <?php if ($project['is_active']): ?>
                            <span class="badge badge-success badge-sm">Active</span>
                        <?php else: ?>
                            <span class="badge badge-ghost badge-sm">Inactive</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="text-gray-600"><?= e($project['client_name']) ?></span>
                        </div>
                        
                        <?php if ($project['location']): ?>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-gray-600"><?= e($project['location']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($project['project_type']): ?>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-outline badge-sm"><?= e($project['project_type']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($project['price_range']): ?>
                        <div class="font-semibold text-primary">
                            ₹ <?= e($project['price_range']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex items-center justify-between mt-4 pt-4 border-t">
                        <div class="text-sm text-gray-500">
                            <span class="badge badge-primary badge-sm"><?= $project['leads_count'] ?></span> leads
                        </div>
                        
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-sm">⋮</label>
                            <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                <li><a href="<?= url("projects/edit?id={$project['id']}") ?>">Edit</a></li>
                                <li><a href="<?= url("leads?project_id={$project['id']}") ?>">View Leads</a></li>
                                <?php if (hasPermission('admin')): ?>
                                    <li><a href="<?= url("projects/delete?id={$project['id']}") ?>" class="btn-delete text-error">Delete</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($totalProjects > $perPage): ?>
    <div class="flex justify-center mt-6">
        <?= pagination($totalProjects, $perPage, $currentPage, url('projects')) ?>
    </div>
<?php endif; ?>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
