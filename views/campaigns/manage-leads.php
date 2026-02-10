<?php
$pageTitle = 'Manage Leads - ' . e($campaign['campaign_name']);
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold"><?= e($campaign['campaign_name']) ?></h2>
            <p class="text-gray-600 mt-1">Manage leads enrolled in this campaign</p>
        </div>
        <a href="<?= url('campaigns') ?>" class="btn btn-outline btn-sm">
            ← Back to Campaigns
        </a>
    </div>
</div>

<!-- Campaign Stats -->
<div class="stats stats-horizontal shadow mb-6 w-full">
    <div class="stat">
        <div class="stat-title">Total Enrolled</div>
        <div class="stat-value text-primary"><?= count($campaignLeads) ?></div>
        <div class="stat-desc">Leads in this campaign</div>
    </div>
    <div class="stat">
        <div class="stat-title">Active</div>
        <div class="stat-value text-success">
            <?= count(array_filter($campaignLeads, fn($l) => $l['status'] === 'active')) ?>
        </div>
        <div class="stat-desc">Currently receiving messages</div>
    </div>
    <div class="stat">
        <div class="stat-title">Paused</div>
        <div class="stat-value text-warning">
            <?= count(array_filter($campaignLeads, fn($l) => $l['status'] === 'paused')) ?>
        </div>
        <div class="stat-desc">Temporarily stopped</div>
    </div>
    <div class="stat">
        <div class="stat-title">Completed</div>
        <div class="stat-value text-info">
            <?= count(array_filter($campaignLeads, fn($l) => $l['status'] === 'completed')) ?>
        </div>
        <div class="stat-desc">Finished the sequence</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Current Campaign Leads -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Campaign Leads (<?= count($campaignLeads) ?>)</h3>
            
            <?php if (empty($campaignLeads)): ?>
                <div class="empty-state py-8">
                    <div class="empty-state-icon">👥</div>
                    <p>No leads enrolled yet</p>
                    <p class="text-sm text-gray-500 mt-2">Add leads from the right panel</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaignLeads as $lead): ?>
                                <tr>
                                    <td>
                                        <div class="font-medium"><?= e($lead['name']) ?></div>
                                        <div class="text-xs text-gray-500"><?= e($lead['email'] ?? 'N/A') ?></div>
                                    </td>
                                    <td><?= e($lead['phone']) ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'badge-neutral',
                                            'active' => 'badge-success',
                                            'paused' => 'badge-warning',
                                            'completed' => 'badge-info',
                                            'opted_out' => 'badge-error'
                                        ];
                                        $statusColor = $statusColors[$lead['status']] ?? 'badge-neutral';
                                        ?>
                                        <span class="badge <?= $statusColor ?> badge-sm">
                                            <?= ucfirst($lead['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-xs">
                                            Message <?= $lead['current_message_index'] ?> 
                                            <?php if ($lead['last_message_sent_at']): ?>
                                                <div class="text-gray-500">
                                                    <?= date('M d, h:i A', strtotime($lead['last_message_sent_at'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex gap-1">
                                            <?php if ($lead['status'] === 'active'): ?>
                                                <button onclick="pauseLead(<?= $lead['campaign_lead_id'] ?>)" 
                                                        class="btn btn-warning btn-xs" 
                                                        title="Pause">
                                                    ⏸️
                                                </button>
                                            <?php elseif ($lead['status'] === 'paused'): ?>
                                                <button onclick="resumeLead(<?= $lead['campaign_lead_id'] ?>)" 
                                                        class="btn btn-success btn-xs" 
                                                        title="Resume">
                                                    ▶️
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button onclick="removeLead(<?= $lead['campaign_lead_id'] ?>)" 
                                                    class="btn btn-error btn-xs" 
                                                    title="Remove from campaign">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add New Leads -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title">Add Leads to Campaign</h3>
            
            <?php if (empty($allLeads)): ?>
                <div class="empty-state py-8">
                    <div class="empty-state-icon">📋</div>
                    <p>No leads available</p>
                    <p class="text-sm text-gray-500 mt-2">Create leads for this project first</p>
                    <a href="<?= url('leads/create') ?>" class="btn btn-primary btn-sm mt-4">
                        Create Lead
                    </a>
                </div>
            <?php else: ?>
                <!-- Filter already enrolled leads -->
                <?php
                $enrolledLeadIds = array_column($campaignLeads, 'lead_id');
                $availableLeads = array_filter($allLeads, function($lead) use ($enrolledLeadIds) {
                    return !in_array($lead['id'], $enrolledLeadIds);
                });
                ?>
                
                <?php if (empty($availableLeads)): ?>
                    <div class="alert alert-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>All project leads are already enrolled in this campaign!</span>
                    </div>
                <?php else: ?>
                    <div class="form-control mb-4">
                        <input type="text" 
                               id="searchLeads" 
                               placeholder="Search by name or phone..." 
                               class="input input-bordered input-sm" 
                               onkeyup="filterLeads()">
                    </div>
                    
                    <div class="overflow-y-auto max-h-96" id="leadsList">
                        <div class="space-y-2">
                            <?php foreach ($availableLeads as $lead): ?>
                                <div class="lead-item p-3 border rounded hover:bg-base-200 cursor-pointer transition-colors"
                                     data-name="<?= e(strtolower($lead['name'])) ?>"
                                     data-phone="<?= e($lead['phone']) ?>">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1">
                                            <div class="font-medium"><?= e($lead['name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= e($lead['phone']) ?></div>
                                            <?php if ($lead['email']): ?>
                                                <div class="text-xs text-gray-400"><?= e($lead['email']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <button onclick="addLead(<?= $lead['id'] ?>)" 
                                                class="btn btn-primary btn-sm">
                                            + Add
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const campaignId = <?= $campaign['id'] ?>;

function filterLeads() {
    const search = document.getElementById('searchLeads').value.toLowerCase();
    const items = document.querySelectorAll('.lead-item');
    
    items.forEach(item => {
        const name = item.getAttribute('data-name');
        const phone = item.getAttribute('data-phone');
        
        if (name.includes(search) || phone.includes(search)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function addLead(leadId) {
    if (!confirm('Add this lead to the campaign?')) return;
    
    $.ajax({
        url: '<?= url('campaigns/add-lead') ?>',
        method: 'POST',
        data: { 
            campaign_id: campaignId,
            lead_id: leadId 
        },
        success: function(response) {
            if (response.success) {
                showNotification('Lead added to campaign', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(response.message || 'Failed to add lead', 'error');
            }
        },
        error: function() {
            showNotification('Error adding lead', 'error');
        }
    });
}

function removeLead(campaignLeadId) {
    if (!confirm('Remove this lead from the campaign?')) return;
    
    $.ajax({
        url: '<?= url('campaigns/remove-lead') ?>',
        method: 'POST',
        data: { campaign_lead_id: campaignLeadId },
        success: function(response) {
            if (response.success) {
                showNotification('Lead removed from campaign', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(response.message || 'Failed to remove lead', 'error');
            }
        },
        error: function() {
            showNotification('Error removing lead', 'error');
        }
    });
}

function pauseLead(campaignLeadId) {
    $.ajax({
        url: '<?= url('campaigns/pause-lead') ?>',
        method: 'POST',
        data: { campaign_lead_id: campaignLeadId },
        success: function(response) {
            if (response.success) {
                showNotification('Lead paused', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(response.message || 'Failed to pause lead', 'error');
            }
        },
        error: function() {
            showNotification('Error pausing lead', 'error');
        }
    });
}

function resumeLead(campaignLeadId) {
    $.ajax({
        url: '<?= url('campaigns/resume-lead') ?>',
        method: 'POST',
        data: { campaign_lead_id: campaignLeadId },
        success: function(response) {
            if (response.success) {
                showNotification('Lead resumed', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(response.message || 'Failed to resume lead', 'error');
            }
        },
        error: function() {
            showNotification('Error resuming lead', 'error');
        }
    });
}
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
