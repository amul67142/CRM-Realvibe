<?php
$pageTitle = 'All Leads';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-bold tracking-tight text-gray-900">Leads</h2>
        <p class="text-sm text-gray-500 mt-1">Manage and track your potential clients</p>
    </div>
    <div class="flex gap-3">
        <a href="<?= url('leads/export') . (empty($filters) ? '' : '?' . http_build_query($filters)) ?>" class="btn btn-outline btn-success btn-sm font-medium normal-case">
            <span class="material-symbols-outlined text-lg mr-1">download</span>
            Export Excel
        </a>
        <a href="<?= url('leads/import') ?>" class="btn btn-outline btn-sm font-medium normal-case">
            <span class="material-symbols-outlined text-lg mr-1">upload_file</span>
            Import CSV
        </a>
        <a href="<?= url('leads/create') ?>" class="btn btn-primary btn-sm font-medium normal-case shadow-lg shadow-primary/30">
            <span class="material-symbols-outlined text-lg mr-1">add</span>
            Add Lead
        </a>
    </div>
</div>

<!-- Bulk Actions Toolbar (Hidden by default) -->
<div id="bulkActions" class="hidden flex items-center justify-between bg-base-100 p-4 rounded-lg shadow-sm border border-base-200 mb-6 sticky top-0 z-20">
    <div class="flex items-center gap-3">
        <span class="text-sm font-medium text-gray-600">Selected: <span id="selectedCount" class="font-bold text-primary">0</span></span>
    </div>
    <div class="flex gap-2">
        <button type="button" onclick="submitBulkDelete()" class="btn btn-error btn-sm text-white">
            <span class="material-symbols-outlined text-lg mr-1">delete</span>
            Delete Selected
        </button>
        <button type="button" onclick="toggleSelectionMode()" class="btn btn-ghost btn-sm">
            Cancel
        </button>
    </div>
</div>

<!-- Selection Mode Toggle -->
<div class="flex justify-end mb-2">
    <button type="button" onclick="toggleSelectionMode()" class="btn btn-ghost btn-xs text-gray-500 hover:text-primary">
        <span class="material-symbols-outlined text-base mr-1">check_box</span>
        Select Multiple
    </button>
</div>

<!-- Filters -->
<div class="card bg-base-100 border border-base-200 shadow-sm mb-6">
    <div class="card-body p-4">
        <form method="GET" action="<?= url('leads') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="form-control w-full">
                <label class="label py-1"><span class="label-text text-xs font-semibold text-gray-500 uppercase">Search</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <span class="material-symbols-outlined text-lg">search</span>
                    </span>
                    <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" 
                           placeholder="Name, phone, email..." class="input input-bordered input-sm w-full pl-9 bg-gray-50 focus:bg-white transition-colors">
                </div>
            </div>
            
            <div class="form-control w-full">
                <label class="label py-1"><span class="label-text text-xs font-semibold text-gray-500 uppercase">Project</span></label>
                <select name="project_id" class="select select-bordered select-sm w-full bg-gray-50 focus:bg-white transition-colors">
                    <option value="">All Projects</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= $project['id'] ?>" <?= selected($filters['project_id'] ?? '', $project['id']) ?>>
                            <?= e($project['project_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-control w-full">
                <label class="label py-1"><span class="label-text text-xs font-semibold text-gray-500 uppercase">Status</span></label>
                <select name="status" class="select select-bordered select-sm w-full bg-gray-50 focus:bg-white transition-colors">
                    <option value="">All Status</option>
                    <option value="new" <?= selected($filters['status'] ?? '', 'new') ?>>New</option>
                    <option value="contacted" <?= selected($filters['status'] ?? '', 'contacted') ?>>Contacted</option>
                    <option value="interested" <?= selected($filters['status'] ?? '', 'interested') ?>>Interested</option>
                    <option value="qualified" <?= selected($filters['status'] ?? '', 'qualified') ?>>Qualified</option>
                    <option value="converted" <?= selected($filters['status'] ?? '', 'converted') ?>>Converted</option>
                    <option value="lost" <?= selected($filters['status'] ?? '', 'lost') ?>>Lost</option>
                </select>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-1 font-medium normal-case">Filter</button>
                <a href="<?= url('leads') ?>" class="btn btn-ghost btn-sm px-2" title="Clear Filters">
                    <span class="material-symbols-outlined text-lg">close</span>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Leads Table -->
<div class="card bg-base-100 border border-base-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <form id="bulkDeleteForm" method="POST" action="<?= url('leads/bulk-delete') ?>">
            <table class="table w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <!-- Checkbox Column (Hidden by default) -->
                        <th class="w-10 py-4 pl-6 hidden selection-col">
                            <label class="cursor-pointer">
                                <input type="checkbox" id="selectAll" class="checkbox checkbox-sm checkbox-primary rounded-sm" />
                            </label>
                        </th>
                        <th class="text-xs font-bold text-gray-500 uppercase tracking-wider py-4 pl-6 standard-col">Lead Name</th>
                        <th class="text-xs font-bold text-gray-500 uppercase tracking-wider py-4">Contact</th>
                        <th class="text-xs font-bold text-gray-500 uppercase tracking-wider py-4">Project</th>
                        <th class="text-xs font-bold text-gray-500 uppercase tracking-wider py-4">Status</th>
                        <th class="text-xs font-bold text-gray-500 uppercase tracking-wider py-4">Source</th>
                        <th class="text-xs font-bold text-gray-500 uppercase tracking-wider py-4">Received</th>
                        <th class="text-xs font-bold text-gray-500 uppercase tracking-wider py-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-12">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="bg-gray-100 p-4 rounded-full mb-3">
                                        <span class="material-symbols-outlined text-3xl">inbox</span>
                                    </div>
                                    <p class="text-lg font-medium text-gray-900">No leads found</p>
                                    <p class="text-sm">Try adjusting your filters or add a new lead.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <!-- Checkbox Cell (Hidden by default) -->
                                <td class="pl-6 py-4 whitespace-nowrap hidden selection-col">
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="lead_ids[]" value="<?= $lead['id'] ?>" class="leader-checkbox checkbox checkbox-sm checkbox-primary rounded-sm" />
                                    </label>
                                </td>
                                <td class="pl-6 py-4 whitespace-nowrap standard-col">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral-focus text-neutral-content rounded-full w-10 h-10 shadow-sm">
                                                <span class="text-xs font-semibold"><?= strtoupper(substr($lead['name'], 0, 2)) ?></span>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="<?= url("leads/{$lead['id']}") ?>" class="font-semibold text-gray-900 hover:text-primary transition-colors block">
                                                <?= e($lead['name']) ?>
                                            </a>
                                            <?php if ($lead['unread_replies'] > 0): ?>
                                                <span class="badge badge-error badge-xs mt-1">New Reply</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="flex items-center text-sm font-medium text-gray-700">
                                            <span class="material-symbols-outlined text-base mr-1 text-gray-400">call</span>
                                            <?= e($lead['phone']) ?>
                                        </div>
                                        <?php if (!empty($lead['email'])): ?>
                                            <div class="flex items-center text-xs text-gray-500 mt-0.5">
                                                <span class="material-symbols-outlined text-base mr-1 text-gray-400">mail</span>
                                                <?= e($lead['email']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-4 whitespace-nowrap">
                                    <span class="badge badge-ghost badge-sm font-medium">
                                        <?= e($lead['project_name']) ?>
                                    </span>
                                </td>
                                <td class="py-4 whitespace-nowrap">
                                    <?= statusBadge($lead['status']) ?>
                                </td>
                                <td class="py-4 whitespace-nowrap">
                                    <?= sourceBadge($lead['source']) ?>
                                </td>
                                <td class="py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900"><?= date('M d, Y', strtotime($lead['created_at'])) ?></span>
                                        <span class="text-xs text-gray-500"><?= date('h:i A', strtotime($lead['created_at'])) ?></span>
                                    </div>
                                </td>
                                <td class="pr-6 py-4 whitespace-nowrap text-right">
                                    <div class="join shadow-sm">
                                        <a href="<?= url("leads/{$lead['id']}") ?>" class="btn btn-xs btn-ghost join-item" title="View">
                                            <span class="material-symbols-outlined text-lg">visibility</span>
                                        </a>
                                        <a href="<?= url("leads/{$lead['id']}/conversation") ?>" class="btn btn-xs btn-ghost join-item text-success" title="WhatsApp">
                                            <span class="material-symbols-outlined text-lg">chat</span>
                                        </a>
                                        <?php if (hasPermission('manager')): ?>
                                            <a href="<?= url("leads/delete?id={$lead['id']}") ?>" class="btn btn-xs btn-ghost join-item text-error" title="Delete" onclick="return confirm('Are you sure?')">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalLeads > 0): ?>
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center">
            <div class="text-xs text-gray-500 font-medium mb-2 sm:mb-0">
                <?php 
                $start = ($currentPage - 1) * $perPage + 1;
                $end = min($currentPage * $perPage, $totalLeads);
                echo "Showing <span class='font-bold text-gray-900'>$start</span> to <span class='font-bold text-gray-900'>$end</span> of <span class='font-bold text-gray-900'>$totalLeads</span> results";
                ?>
            </div>
            
            <?php if ($totalLeads > $perPage): ?>
                <div class="join shadow-sm">
                    <?= pagination($totalLeads, $perPage, $currentPage, url('leads')) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>

<script>
function toggleSelectionMode() {
    const cols = document.querySelectorAll('.selection-col');
    const stdCols = document.querySelectorAll('.standard-col'); // Adjusting padding if needed
    const toolbar = document.getElementById('bulkActions');
    
    cols.forEach(el => el.classList.toggle('hidden'));
    toolbar.classList.toggle('hidden');
    
    // Clear selections when closing
    if (toolbar.classList.contains('hidden')) {
        document.querySelectorAll('.leader-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateSelectedCount();
    }
}

// Select All Logic
document.getElementById('selectAll')?.addEventListener('change', function(e) {
    document.querySelectorAll('.leader-checkbox').forEach(cb => {
        cb.checked = e.target.checked;
    });
    updateSelectedCount();
});

// Individual Checkbox Logic
document.querySelectorAll('.leader-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

function updateSelectedCount() {
    const count = document.querySelectorAll('.leader-checkbox:checked').length;
    document.getElementById('selectedCount').innerText = count;
}

function submitBulkDelete() {
    const count = document.querySelectorAll('.leader-checkbox:checked').length;
    if (count === 0) return;
    
    if (confirm(`Are you sure you want to delete ${count} leads? This cannot be undone.`)) {
        document.getElementById('bulkDeleteForm').submit();
    }
}
</script>
