<?php
$pageTitle = $lead['name'] . ' - Lead Details';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('leads') ?>" class="btn btn-ghost btn-sm">← Back to Leads</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Lead Details Card -->
    <div class="lg:col-span-2 space-y-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h2 class="card-title text-2xl"><?= e($lead['name']) ?></h2>
                        <p class="text-gray-600"><?= e($lead['project_name']) ?></p>
                    </div>
                    <div class="flex gap-2">
                        <?= sourceBadge($lead['source']) ?>
                        <?= statusBadge($lead['status']) ?>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-semibold mb-2">Contact Information</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span><?= e($lead['phone']) ?></span>
                            </div>
                            <?php if ($lead['email']): ?>
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span><?= e($lead['email']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold mb-2">Project Details</h3>
                        <div class="space-y-2 text-sm">
                            <div><span class="badge badge-outline"><?= e($lead['project_type'] ?? 'N/A') ?></span></div>
                            <?php if ($lead['project_location']): ?>
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <span><?= e($lead['project_location']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($lead['price_range']): ?>
                            <div class="font-semibold text-primary">₹ <?= e($lead['price_range']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($lead['budget']): ?>
                <div class="mt-4">
                    <h3 class="font-semibold mb-2">Budget</h3>
                    <p class="text-sm"><?= e($lead['budget']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($lead['notes']): ?>
                <div class="mt-4">
                    <h3 class="font-semibold mb-2">Notes</h3>
                    <p class="text-sm text-gray-600"><?= e($lead['notes']) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mt-4 pt-4 border-t text-sm text-gray-500">
                    Created <?= timeAgo($lead['created_at']) ?>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title mb-4">Quick Actions</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= url("leads/{$lead['id']}/conversation") ?>" class="btn btn-primary">
                        View Conversation
                    </a>
                    <button class="btn btn-outline" onclick="updateStatus()">Change Status</button>
                    <?php if ($lead['is_subscribed']): ?>
                        <button class="btn btn-ghost text-error" onclick="unsubscribeLead()">Unsubscribe</button>
                    <?php else: ?>
                        <button class="btn btn-ghost text-success" onclick="resubscribeLead()">Resubscribe</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Subscription Status -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title text-sm mb-2">Subscription</h3>
                <?php if ($lead['is_subscribed']): ?>
                    <div class="badge badge-success gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Subscribed
                    </div>
                <?php else: ?>
                    <div class="badge badge-error gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Unsubscribed
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Status Options -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title text-sm mb-4">Update Status</h3>
                <select id="status-select" class="select select-bordered select-sm w-full" onchange="quickUpdateStatus(this.value)">
                    <option value="">Change status...</option>
                    <option value="new" <?= selected($lead['status'], 'new') ?>>New</option>
                    <option value="contacted" <?= selected($lead['status'], 'contacted') ?>>Contacted</option>
                    <option value="interested" <?= selected($lead['status'], 'interested') ?>>Interested</option>
                    <option value="qualified" <?= selected($lead['status'], 'qualified') ?>>Qualified</option>
                    <option value="converted" <?= selected($lead['status'], 'converted') ?>>Converted</option>
                    <option value="lost" <?= selected($lead['status'], 'lost') ?>>Lost</option>
                </select>
            </div>
        </div>
    </div>
</div>

<script>
function quickUpdateStatus(status) {
    if (!status) return;
    
    if (!confirm('Update lead status to ' + status + '?')) {
        document.getElementById('status-select').value = '';
        return;
    }
    
    $.ajax({
        url: '<?= url('leads/update-status') ?>',
        method: 'POST',
        data: {
            lead_id: <?= $lead['id'] ?>,
            status: status
        },
        success: function(response) {
            if (response.success) {
                showNotification('Status updated successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Failed to update status', 'error');
            }
        },
        error: function() {
            showNotification('Error updating status', 'error');
        }
    });
}
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
