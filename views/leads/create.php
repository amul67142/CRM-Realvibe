<?php
$pageTitle = 'Add Lead';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('leads') ?>" class="btn btn-ghost btn-sm">
        ← Back to Leads
    </a>
</div>

<div class="card bg-base-100 shadow max-w-3xl mx-auto">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Add New Lead</h2>
        
        <form method="POST" action="<?= url('leads/create') ?>">
            <?= csrfField() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control md:col-span-2">
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
                    <label class="label"><span class="label-text">Name *</span></label>
                    <input type="text" name="name" value="<?= e(old('name')) ?>" 
                           placeholder="Full Name" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Phone *</span></label>
                    <input type="tel" name="phone" value="<?= e(old('phone')) ?>" 
                           placeholder="9876543210" class="input input-bordered phone-input" required>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Email</span></label>
                    <input type="email" name="email" value="<?= e(old('email')) ?>" 
                           placeholder="email@example.com" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Budget</span></label>
                    <input type="text" name="budget" value="<?= e(old('budget')) ?>" 
                           placeholder="50L - 1Cr" class="input input-bordered">
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Status</span></label>
                    <select name="status" class="select select-bordered">
                        <option value="new" <?= selected(old('status', 'new'), 'new') ?>>New</option>
                        <option value="contacted" <?= selected(old('status'), 'contacted') ?>>Contacted</option>
                        <option value="interested" <?= selected(old('status'), 'interested') ?>>Interested</option>
                        <option value="qualified" <?= selected(old('status'), 'qualified') ?>>Qualified</option>
                    </select>
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Notes</span></label>
                    <textarea name="notes" rows="3" class="textarea textarea-bordered" 
                              placeholder="Additional notes..."><?= e(old('notes')) ?></textarea>
                </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
                <button type="submit" class="btn btn-primary">Create Lead</button>
            </div>
        </form>
    </div>
</div>

<?php clearOldInput(); ?>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
