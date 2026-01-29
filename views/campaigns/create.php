<?php
$pageTitle = 'Create Campaign';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('campaigns') ?>" class="btn btn-ghost btn-sm">← Back to Campaigns</a>
</div>

<div class="card bg-base-100 shadow max-w-2xl mx-auto">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Create New Campaign</h2>
        
        <form method="POST" action="<?= url('campaigns/create') ?>">
            <?= csrfField() ?>
            
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Project *</span></label>
                    <select name="project_id" class="select select-bordered" required>
                        <option value="">Select Project</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id'] ?>" <?= selected(old('project_id'), $project['id']) ?>>
                                <?= e($project['project_name']) ?> - <?= e($project['client_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Campaign Name *</span></label>
                    <input type="text" name="campaign_name" value="<?= e(old('campaign_name')) ?>" 
                           placeholder="e.g., 5-Day Nurture Campaign" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Description</span></label>
                    <textarea name="description" rows="3" class="textarea textarea-bordered" 
                              placeholder="Campaign description..."><?= e(old('description')) ?></textarea>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Campaign Duration (Days) *</span></label>
                    <select name="duration_days" class="select select-bordered" required>
                        <option value="3" <?= selected(old('duration_days'), '3') ?>>3 Days</option>
                        <option value="5" <?= selected(old('duration_days', '5'), '5') ?>>5 Days</option>
                        <option value="7" <?= selected(old('duration_days'), '7') ?>>7 Days</option>
                        <option value="10" <?= selected(old('duration_days'), '10') ?>>10 Days</option>
                        <option value="14" <?= selected(old('duration_days'), '14') ?>>14 Days</option>
                        <option value="30" <?= selected(old('duration_days'), '30') ?>>30 Days</option>
                    </select>
                    <label class="label">
                        <span class="label-text-alt">Number of days to send messages to leads</span>
                    </label>
                </div>
                
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="auto_enroll" class="checkbox checkbox-primary" 
                               <?= checked(old('auto_enroll'), 1) ?>>
                        <div>
                            <span class="label-text font-semibold">Auto-enroll new leads</span>
                            <p class="text-sm text-gray-500">Automatically enroll all new leads from this project into this campaign</p>
                        </div>
                    </label>
                </div>
                
                <div class="alert alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <p class="font-semibold">Next Step</p>
                        <p class="text-sm">After creating the campaign, you'll configure the messages for each day.</p>
                    </div>
                </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
                <button type="submit" class="btn btn-primary">Create Campaign & Add Messages</button>
            </div>
        </form>
    </div>
</div>

<?php clearOldInput(); ?>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
