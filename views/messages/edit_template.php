<?php
$pageTitle = 'Edit Template';
include BASE_PATH . 'views/layouts/header.php';

// Convert JSON variables to comma-separated string for display
$variablesDisplay = '';
if ($template['variables']) {
    $vars = json_decode($template['variables'], true);
    if ($vars && is_array($vars)) {
        $variablesDisplay = implode(', ', $vars);
    }
}
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">Edit Template</h2>
        <div class="text-sm breadcrumbs">
            <ul>
                <li><a href="<?= url('messages/templates') ?>">Templates</a></li>
                <li><?= e($template['template_name']) ?></li>
            </ul>
        </div>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form method="POST" action="<?= url('messages/template/edit?id=' . $template['id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= generateToken() ?>">
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Template Name <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="template_name" class="input input-bordered" 
                           value="<?= e($template['template_name']) ?>" required>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Template Type <span class="text-error">*</span></span>
                    </label>
                    <select name="template_type" class="select select-bordered" required>
                        <option value="welcome" <?= selected($template['template_type'], 'welcome') ?>>Welcome</option>
                        <option value="followup" <?= selected($template['template_type'], 'followup') ?>>Follow-up</option>
                        <option value="nurture" <?= selected($template['template_type'], 'nurture') ?>>Nurture</option>
                        <option value="reminder" <?= selected($template['template_type'], 'reminder') ?>>Reminder</option>
                        <option value="other" <?= selected($template['template_type'], 'other') ?>>Other</option>
                    </select>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Template Content <span class="text-error">*</span></span>
                        <span class="label-text-alt">Use {{variable_name}} for dynamic content</span>
                    </label>
                    <textarea name="template_content" class="textarea textarea-bordered h-32" 
                              required><?= e($template['template_content']) ?></textarea>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Variables (Optional)</span>
                        <span class="label-text-alt">Comma-separated, e.g., name,phone,project</span>
                    </label>
                    <input type="text" name="variables_input" id="variables_input" 
                           class="input input-bordered" 
                           placeholder="name,phone,project"
                           value="<?= e($variablesDisplay) ?>">
                    <small class="text-gray-500 mt-1">Common variables: name, phone, email, project, company</small>
                </div>

                <div class="alert alert-info mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><strong>Example:</strong> Hi {{name}}, Thank you for your interest in {{project}}. Our team will contact you at {{phone}}.</span>
                </div>

                <div class="form-control">
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Template</button>
                        <a href="<?= url('messages/templates') ?>" class="btn btn-ghost">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Convert comma-separated input to array for submission
document.querySelector('form').addEventListener('submit', function(e) {
    const variablesInput = document.getElementById('variables_input').value;
    if (variablesInput.trim()) {
        const vars = variablesInput.split(',').map(v => v.trim()).filter(v => v);
        vars.forEach((v, i) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `variables[${i}]`;
            input.value = v;
            this.appendChild(input);
        });
    }
});
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
