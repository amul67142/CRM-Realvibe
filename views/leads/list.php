<?php
$pageTitle = 'All Leads';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">All Leads</h2>
    <div class="flex gap-2">
        <a href="<?= url('leads/import') ?>" class="btn btn-outline btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Import CSV
        </a>
        <a href="<?= url('leads/create') ?>" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Lead
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card bg-base-100 shadow mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('leads') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="form-control">
                <label class="label"><span class="label-text">Search</span></label>
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" 
                       placeholder="Name, phone, email..." class="input input-bordered input-sm">
            </div>
            
            <div class="form-control">
                <label class="label"><span class="label-text">Project</span></label>
                <select name="project_id" class="select select-bordered select-sm">
                    <option value="">All Projects</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= $project['id'] ?>" <?= selected($filters['project_id'] ?? '', $project['id']) ?>>
                            <?= e($project['project_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-control">
                <label class="label"><span class="label-text">Source</span></label>
                <select name="source" class="select select-bordered select-sm">
                    <option value="">All Sources</option>
                    <option value="meta" <?= selected($filters['source'] ?? '', 'meta') ?>>Meta/Facebook</option>
                    <option value="wordpress" <?= selected($filters['source'] ?? '', 'wordpress') ?>>WordPress</option>
                    <option value="linkedin" <?= selected($filters['source'] ?? '', 'linkedin') ?>>LinkedIn</option>
                    <option value="manual" <?= selected($filters['source'] ?? '', 'manual') ?>>Manual</option>
                </select>
            </div>
            
            <div class="form-control">
                <label class="label"><span class="label-text">Status</span></label>
                <select name="status" class="select select-bordered select-sm">
                    <option value="">All Status</option>
                    <option value="new" <?= selected($filters['status'] ?? '', 'new') ?>>New</option>
                    <option value="contacted" <?= selected($filters['status'] ?? '', 'contacted') ?>>Contacted</option>
                    <option value="interested" <?= selected($filters['status'] ?? '', 'interested') ?>>Interested</option>
                    <option value="qualified" <?= selected($filters['status'] ?? '', 'qualified') ?>>Qualified</option>
                    <option value="converted" <?= selected($filters['status'] ?? '', 'converted') ?>>Converted</option>
                    <option value="lost" <?= selected($filters['status'] ?? '', 'lost') ?>>Lost</option>
                </select>
            </div>
            
            <div class="form-control md:col-span-4 flex-row gap-2 justify-end">
                <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                <a href="<?= url('leads') ?>" class="btn btn-ghost btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Leads Table -->
<div class="card bg-base-100 shadow">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Project</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📭</div>
                                    <p>No leads found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <a href="<?= url("leads/{$lead['id']}") ?>" class="link link-primary font-semibold">
                                        <?= e($lead['name']) ?>
                                    </a>
                                    <?php if ($lead['unread_replies'] > 0): ?>
                                        <span class="badge badge-error badge-sm ml-2"><?= $lead['unread_replies'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($lead['phone']) ?></td>
                                <td><?= e($lead['email'] ?? '-') ?></td>
                                <td><?= e($lead['project_name']) ?></td>
                                <td><?= sourceBadge($lead['source']) ?></td>
                                <td><?= statusBadge($lead['status']) ?></td>
                                <td>
                                    <div class="font-medium"><?= date('d M Y', strtotime($lead['created_at'])) ?></div>
                                    <div class="text-xs text-gray-500"><?= date('h:i A', strtotime($lead['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                            <li><a href="<?= url("leads/{$lead['id']}") ?>">View Details</a></li>
                                            <li><a href="<?= url("leads/{$lead['id']}/conversation") ?>">Conversation</a></li>
                                            <?php if (hasPermission('manager')): ?>
                                                <li><a href="<?= url("leads/delete?id={$lead['id']}") ?>" class="btn-delete text-error">Delete</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalLeads > 0): ?>
            <div class="flex flex-col sm:flex-row justify-between items-center mt-4">
                <div class="text-sm text-gray-500 mb-2 sm:mb-0">
                    <?php 
                    $start = ($currentPage - 1) * $perPage + 1;
                    $end = min($currentPage * $perPage, $totalLeads);
                    echo "Showing <strong>$start</strong> to <strong>$end</strong> of <strong>$totalLeads</strong> leads";
                    ?>
                </div>
                
                <?php if ($totalLeads > $perPage): ?>
                    <div class="join">
                        <?= pagination($totalLeads, $perPage, $currentPage, url('leads')) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
