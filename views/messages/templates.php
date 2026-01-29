<?php
$pageTitle = 'Message Templates';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Message Templates</h2>
    <?php if (hasPermission('manager')): ?>
        <a href="<?= url('messages/template/create') ?>" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create Template
        </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card bg-base-100 shadow mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('messages/templates') ?>" class="flex gap-4 items-end">
            <div class="form-control flex-1">
                <label class="label"><span class="label-text">Filter by Type</span></label>
                <select name="type" class="select select-bordered select-sm">
                    <option value="">All Types</option>
                    <option value="welcome" <?= selected($current_type, 'welcome') ?>>Welcome</option>
                    <option value="followup" <?= selected($current_type, 'followup') ?>>Follow-up</option>
                    <option value="nurture" <?= selected($current_type, 'nurture') ?>>Nurture</option>
                    <option value="reminder" <?= selected($current_type, 'reminder') ?>>Reminder</option>
                    <option value="other" <?= selected($current_type, 'other') ?>>Other</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                <a href="<?= url('messages/templates') ?>" class="btn btn-ghost btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Templates Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php if (empty($templates)): ?>
        <div class="col-span-full">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state-icon">📝</div>
                        <p>No templates found</p>
                        <?php if (hasPermission('manager')): ?>
                            <a href="<?= url('messages/template/create') ?>" class="btn btn-primary btn-sm mt-4">Create Your First Template</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($templates as $template): ?>
            <div class="card bg-base-100 shadow hover:shadow-xl transition-shadow">
                <div class="card-body">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="card-title text-lg"><?= e($template['template_name']) ?></h3>
                        <span class="badge badge-primary badge-sm"><?= ucfirst(e($template['template_type'])) ?></span>
                    </div>
                    
                    <div class="text-sm space-y-2">
                        <div class="bg-base-200 p-3 rounded-lg">
                            <p class="text-gray-600 whitespace-pre-wrap"><?= e(truncate($template['template_content'], 150)) ?></p>
                        </div>
                        
                        <?php if ($template['variables']): ?>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs text-gray-500">Variables:</span>
                                <?php 
                                $vars = json_decode($template['variables'], true);
                                if ($vars && is_array($vars)):
                                    foreach ($vars as $var): ?>
                                        <span class="badge badge-sm badge-outline">{{<?= e($var) ?>}}</span>
                                    <?php endforeach;
                                endif;
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-xs text-gray-500">
                            Created by <?= e($template['created_by_name'] ?? 'Unknown') ?>
                            on <?= date('M d, Y', strtotime($template['created_at'])) ?>
                        </div>
                    </div>
                    
                    <?php if (hasPermission('manager')): ?>
                        <div class="card-actions justify-end mt-4 pt-4 border-t">
                            <a href="<?= url("messages/template/edit?id={$template['id']}") ?>" class="btn btn-outline btn-sm">
                                Edit
                            </a>
                            <?php if (hasPermission('admin')): ?>
                                <a href="<?= url("messages/template/delete?id={$template['id']}") ?>" 
                                   class="btn btn-error btn-outline btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this template?')">
                                    Delete
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
