<?php
$pageTitle = 'Add Project';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('projects') ?>" class="btn btn-ghost btn-sm">← Back to Projects</a>
</div>

<div class="card bg-base-100 shadow max-w-3xl mx-auto">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Add New Project</h2>
        
        <form method="POST" action="<?= url('projects/create') ?>">
            <?= csrfField() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Client *</span></label>
                    <select name="client_id" class="select select-bordered" required>
                        <option value="">Select Client</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>" <?= selected(old('client_id'), $client['id']) ?>>
                                <?= e($client['name']) ?> <?= $client['company_name'] ? '(' . e($client['company_name']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Project Name *</span></label>
                    <input type="text" name="project_name" value="<?= e(old('project_name')) ?>" 
                           placeholder="e.g., Luxury Apartments Phase 1" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Project Type</span></label>
                    <select name="project_type" class="select select-bordered">
                        <option value="">Select Type</option>
                        <option value="Residential" <?= selected(old('project_type'), 'Residential') ?>>Residential</option>
                        <option value="Commercial" <?= selected(old('project_type'), 'Commercial') ?>>Commercial</option>
                        <option value="Plots" <?= selected(old('project_type'), 'Plots') ?>>Plots</option>
                        <option value="Villa" <?= selected(old('project_type'), 'Villa') ?>>Villa</option>
                        <option value="Independent Floor" <?= selected(old('project_type'), 'Independent Floor') ?>>Independent Floor</option>
                    </select>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Location</span></label>
                    <input type="text" name="location" value="<?= e(old('location')) ?>" 
                           placeholder="e.g., Sector 67, Gurgaon" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Price Range</span></label>
                    <input type="text" name="price_range" value="<?= e(old('price_range')) ?>" 
                           placeholder="e.g., 50L - 1Cr" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Brochure URL</span></label>
                    <input type="url" name="brochure_url" value="<?= e(old('brochure_url')) ?>" 
                           placeholder="https://..." class="input input-bordered">
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Description</span></label>
                    <textarea name="description" rows="3" class="textarea textarea-bordered" 
                              placeholder="Project description..."><?= e(old('description')) ?></textarea>
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Welcome Message (sent to new leads)</span></label>
                    <textarea name="welcome_message" rows="4" class="textarea textarea-bordered" 
                              placeholder="Hello {{name}}! Thank you for your interest in {{project_name}}..."><?= e(old('welcome_message')) ?></textarea>
                    <label class="label">
                        <span class="label-text-alt">Use merge tags: {{name}}, {{project_name}}, {{location}}, {{price_range}}</span>
                    </label>
                </div>
                
                <!-- WhatsApp Configuration -->
                <div class="divider md:col-span-2">WhatsApp Configuration</div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">WhatsApp Provider</span></label>
                    <select name="whatsapp_provider" id="whatsapp_provider" class="select select-bordered">
                        <option value="default" <?= selected(old('whatsapp_provider'), 'default') ?>>Use Global Default</option>
                        <option value="aisensy" <?= selected(old('whatsapp_provider'), 'aisensy') ?>>AiSensy</option>
                        <option value="whatsapp_api" <?= selected(old('whatsapp_provider'), 'whatsapp_api')?>>WhatsApp Cloud API (Meta)</option>
                        <option value="twilio" <?= selected(old('whatsapp_provider'), 'twilio') ?>>Twilio</option>
                    </select>
                    <label class="label">
                        <span class="label-text-alt">Choose which WhatsApp provider to use for this project's messages</span>
                    </label>
                </div>
                
                <div class="form-control md:col-span-2" id="aisensy_campaign_field" style="display:none;">
                    <label class="label"><span class="label-text">AiSensy Campaign Name *</span></label>
                    <input type="text" name="aisensy_campaign_name" value="<?= e(old('aisensy_campaign_name')) ?>" 
                           placeholder="e.g., api_campaign" class="input input-bordered">
                    <label class="label">
                        <span class="label-text-alt">Required when using AiSensy. Create this campaign in your AiSensy dashboard first.</span>
                    </label>
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" class="checkbox checkbox-primary" checked>
                        <span class="label-text">Active (project is accepting new leads)</span>
                    </label>
                </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
                <button type="submit" class="btn btn-primary">Create Project</button>
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

// Trigger on page load if old value was aisensy
document.addEventListener('DOMContentLoaded', function() {
    const provider = document.getElementById('whatsapp_provider');
    if (provider.value === 'aisensy') {
        document.getElementById('aisensy_campaign_field').style.display = 'block';
        document.querySelector('#aisensy_campaign_field input').required = true;
    }
});
</script>

<?php clearOldInput(); ?>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
