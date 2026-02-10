<?php
$pageTitle = 'Campaigns';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Campaigns</h2>
    <?php if (hasPermission('manager')): ?>
        <a href="<?= url('campaigns/create') ?>" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create Campaign
        </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<?php if (!empty($projects)): ?>
<div class="card bg-base-100 shadow mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('campaigns') ?>" class="flex gap-4 items-end">
            <div class="form-control flex-1">
                <label class="label"><span class="label-text">Filter by Project</span></label>
                <select name="project_id" class="select select-bordered select-sm">
                    <option value="">All Projects</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= $project['id'] ?>" <?= selected($filters['project_id'] ?? '', $project['id']) ?>>
                            <?= e($project['project_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                <a href="<?= url('campaigns') ?>" class="btn btn-ghost btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Campaigns Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <?php if (empty($campaigns)): ?>
        <div class="col-span-full">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state-icon">📧</div>
                        <p>No campaigns found</p>
                        <?php if (hasPermission('manager')): ?>
                            <a href="<?= url('campaigns/create') ?>" class="btn btn-primary btn-sm mt-4">Create Your First Campaign</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($campaigns as $campaign): ?>
            <div class="card bg-base-100 shadow hover:shadow-xl transition-shadow">
                <div class="card-body">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="card-title text-lg"><?= e($campaign['campaign_name']) ?></h3>
                        <div class="flex gap-2 items-center">
                            <?php if ($campaign['auto_enroll']): ?>
                                <span class="badge badge-success badge-sm">Auto-enroll</span>
                            <?php endif; ?>
                            <label class="swap">
                                <input type="checkbox" class="toggle toggle-success" 
                                       onchange="toggleCampaignStatus(<?= $campaign['id'] ?>)"
                                       <?= $campaign['is_active'] ? 'checked' : '' ?>>
                            </label>
                        </div>
                    </div>
                    
                    <div class="text-sm space-y-2">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span><?= e($campaign['project_name']) ?></span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span><?= $campaign['duration_days'] ?> days</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span><?= $campaign['messages_count'] ?> messages configured</span>
                        </div>
                        
                        <?php if ($campaign['description']): ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="stats stats-vertical lg:stats-horizontal shadow-sm mt-4 text-xs">
                        <div class="stat">
                            <div class="stat-title text-xs">Enrolled</div>
                            <div class="stat-value text-lg"><?= $campaign['enrolled_count'] ?></div>
                        </div>
                        <div class="stat">
                            <div class="stat-title text-xs">Active</div>
                            <div class="stat-value text-lg text-success"><?= $campaign['active_count'] ?></div>
                        </div>
                    </div>
                    
                    <!-- Status Badge -->
                    <div class="mt-4">
                        <?php 
                        $status = $campaign['status'] ?? 'draft';
                        $statusColors = [
                            'draft' => 'badge-neutral',
                            'active' => 'badge-success',
                            'paused' => 'badge-warning',
                            'completed' => 'badge-info'
                        ];
                        $statusColor = $statusColors[$status] ?? 'badge-neutral';
                        ?>
                        <span class="badge <?= $statusColor ?>"><?= ucfirst($status) ?></span>
                        <?php if (($campaign['whatsapp_method'] ?? '') === 'aisensy'): ?>
                            <span class="badge badge-outline badge-sm ml-2">
                                ☁️ AiSensy
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="card-actions justify-end mt-4 pt-4 border-t flex-wrap gap-2">
                        <!-- Analytics (always visible) -->
                        <a href="<?= url("campaigns/analytics?id={$campaign['id']}") ?>" class="btn btn-outline btn-sm">
                            📊 Analytics
                        </a>
                        
                        <?php if (hasPermission('manager')): ?>
                            <!-- Edit Messages -->
                            <a href="<?= url("campaigns/builder?id={$campaign['id']}") ?>" class="btn btn-outline btn-sm">
                                ✏️ Edit Messages
                            </a>
                            
                            <!-- Manage Leads -->
                            <a href="<?= url("campaigns/manage-leads?id={$campaign['id']}") ?>" class="btn btn-outline btn-sm">
                                👥 Manage Leads
                            </a>
                            
                            <!-- Start Button (only for draft campaigns) -->
                            <?php if ($status === 'draft'): ?>
                                <a href="<?= url("campaigns/start?id={$campaign['id']}") ?>" 
                                   class="btn btn-success btn-sm"
                                   onclick="return confirm('Start this campaign and send welcome messages to all leads?')">
                                    ▶️ Start Campaign
                                </a>
                            <?php endif; ?>
                            
                            <!-- Pause Button (only for active campaigns) -->
                            <?php if ($status === 'active'): ?>
                                <a href="<?= url("campaigns/pause?id={$campaign['id']}") ?>" 
                                   class="btn btn-warning btn-sm"
                                   onclick="return confirm('Pause this campaign? No new messages will be sent.')">
                                    ⏸️ Pause
                                </a>
                            <?php endif; ?>
                            
                            <!-- Resume Button (only for paused campaigns) -->
                            <?php if ($status === 'paused'): ?>
                                <a href="<?= url("campaigns/resume?id={$campaign['id']}") ?>" 
                                   class="btn btn-success btn-sm"
                                   onclick="return confirm('Resume this campaign? Messages will start sending again.')">
                                    ▶️ Resume
                                </a>
                            <?php endif; ?>
                            
                            <!-- Delete Button (admin only) -->
                            <?php if (hasPermission('admin')): ?>
                                <a href="<?= url("campaigns/delete?id={$campaign['id']}") ?>" 
                                   class="btn btn-error btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this campaign? This cannot be undone!')">
                                    🗑️ Delete
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleCampaignStatus(campaignId) {
    $.ajax({
        url: '<?= url('campaigns/toggle-status') ?>',
        method: 'POST',
        data: { campaign_id: campaignId },
        success: function(response) {
            if (response.success) {
                showNotification('Campaign status updated', 'success');
            } else {
                showNotification('Failed to update status', 'error');
                location.reload();
            }
        },
        error: function() {
            showNotification('Error updating status', 'error');
            location.reload();
        }
    });
}
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
