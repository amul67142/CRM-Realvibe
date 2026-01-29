<?php
$pageTitle = 'Clients';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Clients</h2>
    <a href="<?= url('clients/create') ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Add Client
    </a>
</div>

<div class="card bg-base-100 shadow">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Projects</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <div class="empty-state">
                                    <div class="empty-state-icon">🏢</div>
                                    <p>No clients found</p>
                                    <a href="<?= url('clients/create') ?>" class="btn btn-primary btn-sm mt-4">Add Your First Client</a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td class="font-semibold"><?= e($client['name']) ?></td>
                                <td><?= e($client['company_name'] ?? '-') ?></td>
                                <td><?= e($client['email'] ?? '-') ?></td>
                                <td><?= e($client['phone'] ?? '-') ?></td>
                                <td>
                                    <span class="badge badge-primary"><?= $client['projects_count'] ?> projects</span>
                                </td>
                                <td><?= formatDate($client['created_at'], 'M d, Y') ?></td>
                                <td>
                                    <div class="join">
                                        <a href="<?= url("clients/edit?id={$client['id']}") ?>" class="btn btn-xs join-item">Edit</a>
                                        <?php if (hasPermission('admin')): ?>
                                            <a href="<?= url("clients/delete?id={$client['id']}") ?>" 
                                               class="btn btn-xs join-item btn-error btn-delete">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalClients > $perPage): ?>
            <div class="flex justify-center mt-4">
                <?= pagination($totalClients, $perPage, $currentPage, url('clients')) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
