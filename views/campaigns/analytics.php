<?php
$pageTitle = 'Campaign Analytics - ' . $campaign['campaign_name'];
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('campaigns') ?>" class="btn btn-ghost btn-sm">← Back to Campaigns</a>
</div>

<div class="card bg-base-100 shadow mb-6">
    <div class="card-body">
        <h2 class="card-title text-2xl"><?= e($campaign['campaign_name']) ?></h2>
        <p class="text-gray-600"><?= e($campaign['project_name']) ?></p>
    </div>
</div>

<!-- Analytics Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="text-sm text-gray-500">Total Enrolled</h3>
            <p class="text-3xl font-bold text-primary"><?= $analytics['total_enrolled'] ?></p>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="text-sm text-gray-500">Active</h3>
            <p class="text-3xl font-bold text-success"><?= $analytics['active'] ?></p>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="text-sm text-gray-500">Completed</h3>
            <p class="text-3xl font-bold text-info"><?= $analytics['completed'] ?></p>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="text-sm text-gray-500">Unsubscribed</h3>
            <p class="text-3xl font-bold text-error"><?= $analytics['unsubscribed'] ?></p>
        </div>
    </div>
</div>

<!-- Message Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="text-sm text-gray-500">Messages Sent</h3>
            <p class="text-2xl font-bold"><?= $analytics['messages_sent'] ?></p>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="text-sm text-gray-500">Delivery Rate</h3>
            <p class="text-2xl font-bold text-success"><?= $analytics['delivery_rate'] ?>%</p>
            <p class="text-xs text-gray-500"><?= $analytics['messages_delivered'] ?> delivered</p>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="text-sm text-gray-500">Read Rate</h3>
            <p class="text-2xl font-bold text-info"><?= $analytics['read_rate'] ?>%</p>
            <p class="text-xs text-gray-500"><?= $analytics['messages_read'] ?> read</p>
        </div>
    </div>
</div>

<!-- Enrollments Table -->
<div class="card bg-base-100 shadow">
    <div class="card-body">
        <h3 class="card-title mb-4">Enrolled Leads</h3>
        
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>Lead Name</th>
                        <th>Phone</th>
                        <th>Current Day</th>
                        <th>Status</th>
                        <th>Started</th>
                        <th>Last Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($enrollments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">
                                No leads enrolled in this campaign yet
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td class="font-semibold"><?= e($enrollment['name']) ?></td>
                                <td><?= e($enrollment['phone']) ?></td>
                                <td>
                                    <span class="badge badge-primary">
                                        Day <?= $enrollment['current_day'] ?>/<?= $campaign['duration_days'] ?>
                                    </span>
                                </td>
                                <td><?= statusBadge($enrollment['status']) ?></td>
                                <td><?= timeAgo($enrollment['started_at']) ?></td>
                                <td><?= $enrollment['last_message_sent_at'] ? timeAgo($enrollment['last_message_sent_at']) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
