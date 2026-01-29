<?php
$pageTitle = 'Edit Project';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('projects') ?>" class="btn btn-ghost btn-sm">← Back to Projects</a>
</div>

<div class="card bg-base-100 shadow max-w-3xl mx-auto">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Edit Project</h2>
        
        <form method="POST" action="<?= url("projects/edit?id={$project['id']}") ?>">
            <?= csrfField() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Client *</span></label>
                    <select name="client_id" class="select select-bordered" required>
                        <option value="">Select Client</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>" <?= selected($project['client_id'], $client['id']) ?>>
                                <?= e($client['name']) ?> <?= $client['company_name'] ? '(' . e($client['company_name']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Project Name *</span></label>
                    <input type="text" name="project_name" value="<?= e($project['project_name']) ?>" 
                           class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Project Type</span></label>
                    <select name="project_type" class="select select-bordered">
                        <option value="">Select Type</option>
                        <option value="Residential" <?= selected($project['project_type'], 'Residential') ?>>Residential</option>
                        <option value="Commercial" <?= selected($project['project_type'], 'Commercial') ?>>Commercial</option>
                        <option value="Plots" <?= selected($project['project_type'], 'Plots') ?>>Plots</option>
                        <option value="Villa" <?= selected($project['project_type'], 'Villa') ?>>Villa</option>
                        <option value="Independent Floor" <?= selected($project['project_type'], 'Independent Floor') ?>>Independent Floor</option>
                    </select>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Location</span></label>
                    <input type="text" name="location" value="<?= e($project['location']) ?>" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Price Range</span></label>
                    <input type="text" name="price_range" value="<?= e($project['price_range']) ?>" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Brochure URL</span></label>
                    <input type="url" name="brochure_url" value="<?= e($project['brochure_url']) ?>" class="input input-bordered">
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Description</span></label>
                    <textarea name="description" rows="3" class="textarea textarea-bordered"><?= e($project['description']) ?></textarea>
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Welcome Message</span></label>
                    <textarea name="welcome_message" rows="4" class="textarea textarea-bordered"><?= e($project['welcome_message']) ?></textarea>
                </div>
                
                <!-- WhatsApp Configuration -->
                <div class="divider md:col-span-2">WhatsApp Configuration</div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">WhatsApp Provider</span></label>
                    <select name="whatsapp_provider" id="whatsapp_provider" class="select select-bordered">
                        <option value="default" <?= selected($project['whatsapp_provider'] ?? 'default', 'default') ?>>Use Global Default</option>
                        <option value="aisensy" <?= selected($project['whatsapp_provider'] ?? 'default', 'aisensy') ?>>AiSensy</option>
                        <option value="whatsapp_api" <?= selected($project['whatsapp_provider'] ?? 'default', 'whatsapp_api') ?>>WhatsApp Cloud API (Meta)</option>
                        <option value="twilio" <?= selected($project['whatsapp_provider'] ?? 'default', 'twilio') ?>>Twilio</option>
                    </select>
                    <label class="label">
                        <span class="label-text-alt">Choose which WhatsApp provider to use for this project's messages</span>
                    </label>
                </div>
                
                <div class="form-control md:col-span-2" id="aisensy_campaign_field" style="display:<?= ($project['whatsapp_provider'] ?? '') === 'aisensy' ? 'block' : 'none' ?>;">
                    <label class="label"><span class="label-text">AiSensy Campaign Name *</span></label>
                    <input type="text" name="aisensy_campaign_name" value="<?= e($project['aisensy_campaign_name'] ?? '') ?>" 
                           placeholder="e.g., api_campaign" class="input input-bordered">
                    <label class="label">
                        <span class="label-text-alt">Required when using AiSensy. Create this campaign in your AiSensy dashboard first.</span>
                    </label>
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" class="checkbox checkbox-primary" 
                               <?= checked($project['is_active'], 1) ?>>
                        <span class="label-text">Active (project is accepting new leads)</span>
                    </label>
                </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
                <button type="submit" class="btn btn-primary">Update Project</button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle AiSensy campaign field visibility
document.getElementById('whatsapp_provider').addEventListener('change', function() {
    const campaignField = document.getElementById('aisensy_campaign_field');
    const campaignInput = campaignField.querySelector('input');
    
    if (this.value === 'aisensy') {
        campaignField.style.display = 'block';
        campaignInput.required = true;
    } else {
        campaignField.style.display = 'none';
        campaignInput.required = false;
    }
});
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
