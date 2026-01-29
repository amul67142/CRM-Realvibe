<?php
$pageTitle = 'Dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title text-sm text-gray-500">Total Leads</h3>
            <p class="text-3xl font-bold text-primary"><?= number_format($stats['total_leads']) ?></p>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title text-sm text-gray-500">Today's Leads</h3>
            <p class="text-3xl font-bold text-success"><?= number_format($stats['leads_today']) ?></p>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title text-sm text-gray-500">Active Campaigns</h3>
            <p class="text-3xl font-bold text-info"><?= number_format($stats['active_campaigns']) ?></p>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title text-sm text-gray-500">Unread Replies</h3>
            <p class="text-3xl font-bold text-warning"><?= number_format($stats['unread_replies']) ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Leads Trend Chart -->
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title">Lead Trend (Last 30 Days)</h2>
                <canvas id="leadsTrendChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Leads by Source -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Leads by Source</h2>
            <div class="space-y-2">
                <?php foreach ($leadsBySource as $source): ?>
                    <div class="flex justify-between items-center">
                        <span><?= sourceBadge($source['source']) ?></span>
                        <span class="font-bold"><?= $source['count'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Leads & Unread Replies -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <!-- Recent Leads -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Recent Leads</h2>
            
            <div class="overflow-x-auto">
                <table class="table table-compact w-full">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Project</th>
                            <th>Source</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLeads as $lead): ?>
                            <tr>
                                <td>
                                    <a href="<?= url("leads/{$lead['id']}") ?>" class="link link-primary">
                                        <?= e($lead['name']) ?>
                                    </a>
                                </td>
                                <td><?= e($lead['project_name']) ?></td>
                                <td><?= sourceBadge($lead['source']) ?></td>
                                <td><?= timeAgo($lead['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="card-actions justify-end mt-4">
                <a href="<?= url('leads') ?>" class="btn btn-sm btn-primary">View All Leads</a>
            </div>
        </div>
    </div>
    
    <!-- Unread Replies -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Recent Replies</h2>
            
            <div class="space-y-3">
                <?php if (empty($unreadReplies)): ?>
                    <p class="text-gray-500">No unread replies</p>
                <?php else: ?>
                    <?php foreach ($unreadReplies as $reply): ?>
                        <div class="p-3 bg-base-200 rounded-lg">
                            <div class="flex justify-between items-start mb-2">
                                <strong><?= e($reply['name']) ?></strong>
                                <span class="text-xs text-gray-500"><?= timeAgo($reply['received_at']) ?></span>
                            </div>
                            <p class="text-sm"><?= e(truncate($reply['reply_content'], 100)) ?></p>
                            <a href="<?= url("leads/{$reply['lead_id']}/conversation") ?>" class="btn btn-xs btn-primary mt-2">
                                View Conversation
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Leads Trend Chart
const ctx = document.getElementById('leadsTrendChart');
const leadsTrend = <?= json_encode($leadsTrend) ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: leadsTrend.map(d => d.date),
        datasets: [{
            label: 'Leads',
            data: leadsTrend.map(d => d.count),
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
