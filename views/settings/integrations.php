<?php
$pageTitle = 'Integrations & Settings';
include BASE_PATH . 'views/layouts/header.php';
?>

<h2 class="text-2xl font-bold mb-6">Integrations & Settings</h2>

<div class="card bg-base-100 shadow">
    <div class="card-body">
        <form method="POST" action="<?= url('settings/integrations') ?>">
            <?= csrfField() ?>
            
            <!-- WhatsApp Provider Selection -->
            <div class="form-section">
                <h3 class="form-section-title">Primary WhatsApp Provider</h3>
                <div class="form-control">
                    <label class="label"><span class="label-text">Select WhatsApp Provider</span></label>
                    <select name="whatsapp_provider" class="select select-bordered">
                        <option value="aisensy" <?= selected($settings['whatsapp_provider'] ?? 'aisensy', 'aisensy') ?>>AiSensy</option>
                        <option value="whatsapp_api" <?= selected($settings['whatsapp_provider'] ?? '', 'whatsapp_api') ?>>WhatsApp Business API</option>
                        <option value="twilio" <?= selected($settings['whatsapp_provider'] ?? '', 'twilio') ?>>Twilio WhatsApp</option>
                    </select>
                </div>
            </div>
            
            <!-- AiSensy Settings -->
            <div class="form-section">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">AiSensy Configuration</h3>
                    <div class="form-control">
                        <label class="label cursor-pointer gap-2">
                            <span class="label-text">Enable</span>
                            <input type="checkbox" name="aisensy_enabled" class="toggle toggle-primary" 
                                   <?= checked($settings['aisensy_enabled'] ?? 1, 1) ?>>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">API Key</span></label>
                        <input type="text" name="aisensy_api_key" 
                               value="<?= e($settings['aisensy_api_key'] ?? AISENSY_API_KEY) ?>" 
                               placeholder="Your AiSensy API Key" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Base URL</span></label>
                        <input type="text" name="aisensy_base_url" 
                               value="<?= e($settings['aisensy_base_url'] ?? AISENSY_API_BASE_URL) ?>" 
                               class="input input-bordered">
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline btn-sm mt-2" onclick="testIntegration('aisensy')">
                    Test AiSensy Connection
                </button>
            </div>
            
            <!-- Meta Lead Ads Settings -->
            <div class="form-section">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Meta Lead Ads Integration</h3>
                    <span class="badge badge-info">Facebook & Instagram</span>
                </div>
                
                <div class="alert alert-info mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <h4 class="font-bold">Webhook URL for Meta Developer Console:</h4>
                        <code class="text-sm bg-base-200 px-2 py-1 rounded block mt-2">
                            <?= rtrim(BASE_URL, '/') ?>/api/webhooks/meta-leads
                        </code>
                        <p class="text-xs mt-2">Configure this URL in your Meta App's Webhook settings. Subscribe to <strong>leadgen</strong> events.</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">App ID</span></label>
                        <input type="text" name="meta_app_id" 
                               value="<?= e($settings['meta_app_id'] ?? '') ?>" 
                               placeholder="Your Meta App ID" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">App Secret</span></label>
                        <input type="password" name="meta_app_secret" 
                               value="<?= e($settings['meta_app_secret'] ?? '') ?>" 
                               placeholder="Your Meta App Secret" class="input input-bordered">
                    </div>
                    
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text">Access Token</span></label>
                        <textarea name="meta_access_token" rows="2"
                                  placeholder="Your Meta Access Token (with leads_retrieval permission)" 
                                  class="textarea textarea-bordered"><?= e($settings['meta_access_token'] ?? '') ?></textarea>
                        <label class="label">
                            <span class="label-text-alt">Required permission: <code>leads_retrieval</code></span>
                        </label>
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Verify Token</span></label>
                        <input type="text" name="meta_verify_token" 
                               value="<?= e($settings['meta_verify_token'] ?? '') ?>" 
                               placeholder="Custom verify token (any random string)" class="input input-bordered">
                        <label class="label">
                            <span class="label-text-alt">Use this token when setting up the webhook in Meta</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex gap-2 mt-4">
                    <button type="button" class="btn btn-outline btn-sm" 
                            onclick="testIntegration('meta_leads')">
                        Test Meta API Connection
                    </button>
                    <a href="https://developers.facebook.com/apps/" target="_blank" 
                       class="btn btn-ghost btn-sm">
                        Open Meta Developer Console
                    </a>
                </div>
            </div>
            
            <!-- WhatsApp Business API Settings -->
            <div class="form-section">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">WhatsApp Business API</h3>
                    <div class="form-control">
                        <label class="label cursor-pointer gap-2">
                            <span class="label-text">Enable</span>
                            <input type="checkbox" name="whatsapp_api_enabled" class="toggle toggle-primary" 
                                   <?= checked($settings['whatsapp_api_enabled'] ?? 0, 1) ?>>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">API Key / Access Token</span></label>
                        <input type="text" name="whatsapp_api_key" 
                               value="<?= e($settings['whatsapp_api_key'] ?? '') ?>" 
                               placeholder="Your WhatsApp API Key" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">API URL</span></label>
                        <input type="text" name="whatsapp_api_url" 
                               value="<?= e($settings['whatsapp_api_url'] ?? 'https://graph.facebook.com/v17.0') ?>" 
                               class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Phone Number ID</span></label>
                        <input type="text" name="whatsapp_phone_number_id" 
                               value="<?= e($settings['whatsapp_phone_number_id'] ?? '') ?>" 
                               placeholder="Phone Number ID" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Business Account ID</span></label>
                        <input type="text" name="whatsapp_business_account_id" 
                               value="<?= e($settings['whatsapp_business_account_id'] ?? '') ?>" 
                               placeholder="Business Account ID" class="input input-bordered">
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline btn-sm mt-2" onclick="testIntegration('whatsapp_api')">
                    Test WhatsApp Business API Connection
                </button>
            </div>
            
            <!-- Twilio WhatsApp Settings -->
            <div class="form-section">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Twilio WhatsApp</h3>
                    <div class="form-control">
                        <label class="label cursor-pointer gap-2">
                            <span class="label-text">Enable</span>
                            <input type="checkbox" name="twilio_enabled" class="toggle toggle-primary" 
                                   <?= checked($settings['twilio_enabled'] ?? 0, 1) ?>>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Account SID</span></label>
                        <input type="text" name="twilio_account_sid" 
                               value="<?= e($settings['twilio_account_sid'] ?? '') ?>" 
                               placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Auth Token</span></label>
                        <input type="password" name="twilio_auth_token" 
                               value="<?= e($settings['twilio_auth_token'] ?? '') ?>" 
                               placeholder="Your Auth Token" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">WhatsApp Number</span></label>
                        <input type="text" name="twilio_whatsapp_number" 
                               value="<?= e($settings['twilio_whatsapp_number'] ?? '') ?>" 
                               placeholder="whatsapp:+14155238886" class="input input-bordered">
                    </div>
                </div>
            </div>
            
            <!-- SMTP Email Settings -->
            <div class="form-section">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">SMTP Email Configuration</h3>
                    <div class="form-control">
                        <label class="label cursor-pointer gap-2">
                            <span class="label-text">Enable</span>
                            <input type="checkbox" name="smtp_enabled" class="toggle toggle-primary" 
                                   <?= checked($settings['smtp_enabled'] ?? 0, 1) ?>>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">SMTP Host</span></label>
                        <input type="text" name="smtp_host" 
                               value="<?= e($settings['smtp_host'] ?? '') ?>" 
                               placeholder="smtp.gmail.com" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">SMTP Port</span></label>
                        <input type="number" name="smtp_port" 
                               value="<?= e($settings['smtp_port'] ?? '587') ?>" 
                               placeholder="587" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Username</span></label>
                        <input type="text" name="smtp_username" 
                               value="<?= e($settings['smtp_username'] ?? '') ?>" 
                               placeholder="your-email@gmail.com" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Password</span></label>
                        <input type="password" name="smtp_password" 
                               value="<?= e($settings['smtp_password'] ?? '') ?>" 
                               placeholder="Your password or app password" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Encryption</span></label>
                        <select name="smtp_encryption" class="select select-bordered">
                            <option value="tls" <?= selected($settings['smtp_encryption'] ?? 'tls', 'tls') ?>>TLS</option>
                            <option value="ssl" <?= selected($settings['smtp_encryption'] ?? '', 'ssl') ?>>SSL</option>
                            <option value="none" <?= selected($settings['smtp_encryption'] ?? '', 'none') ?>>None</option>
                        </select>
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">From Email</span></label>
                        <input type="email" name="smtp_from_email" 
                               value="<?= e($settings['smtp_from_email'] ?? '') ?>" 
                               placeholder="noreply@yourdomain.com" class="input input-bordered">
                    </div>
                    
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text">From Name</span></label>
                        <input type="text" name="smtp_from_name" 
                               value="<?= e($settings['smtp_from_name'] ?? 'RealVibe CRM') ?>" 
                               class="input input-bordered">
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline btn-sm mt-2" onclick="testIntegration('smtp')">
                    Test SMTP Connection
                </button>
            </div>
            
            <!-- SMS Gateway Settings -->
            <div class="form-section">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">SMS Gateway</h3>
                    <div class="form-control">
                        <label class="label cursor-pointer gap-2">
                            <span class="label-text">Enable</span>
                            <input type="checkbox" name="sms_enabled" class="toggle toggle-primary" 
                                   <?= checked($settings['sms_enabled'] ?? 0, 1) ?>>
                        </label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">SMS Provider</span></label>
                        <select name="sms_provider" class="select select-bordered">
                            <option value="none" <?= selected($settings['sms_provider'] ?? 'none', 'none') ?>>None</option>
                            <option value="twilio" <?= selected($settings['sms_provider'] ?? '', 'twilio') ?>>Twilio</option>
                            <option value="msg91" <?= selected($settings['sms_provider'] ?? '', 'msg91') ?>>MSG91</option>
                            <option value="textlocal" <?= selected($settings['sms_provider'] ?? '', 'textlocal') ?>>TextLocal</option>
                        </select>
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">API Key</span></label>
                        <input type="text" name="sms_api_key" 
                               value="<?= e($settings['sms_api_key'] ?? '') ?>" 
                               placeholder="Your SMS API Key" class="input input-bordered">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Sender ID</span></label>
                        <input type="text" name="sms_sender_id" 
                               value="<?= e($settings['sms_sender_id'] ?? '') ?>" 
                               placeholder="RLVIBE" class="input input-bordered">
                    </div>
                </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
                <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
            </div>
        </form>
    </div>
</div>

<script>
function testIntegration(provider) {
    // Meta API test doesn't need phone number, just tests connection
    if (provider === 'meta_leads') {
        showNotification('Testing Meta API connection...', 'info');
        
        $.ajax({
            url: '<?= url('controllers/MetaLeadFormsController.php?action=test_connection') ?>',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    showNotification('✓ Meta API connection successful!', 'success');
                } else {
                    showNotification('Meta API test failed: ' + response.error, 'error');
                }
            },
            error: function() {
                showNotification('Meta API test failed. Please check your configuration.', 'error');
            }
        });
        return;
    }
    
    const phone = prompt(`Enter test phone number for ${provider}:`);
    if (!phone) return;
    
    const email = provider === 'smtp' ? prompt('Enter test email:') : '';
    
    showNotification('Testing connection...', 'info');
    
    $.ajax({
        url: '<?= url('settings/test-integration') ?>',
        method: 'POST',
        data: {
            provider: provider,
            phone: phone,
            email: email
        },
        success: function(response) {
            if (response.success) {
                showNotification('Test successful! Message sent.', 'success');
            } else {
                showNotification('Test failed: ' + response.error, 'error');
            }
        },
        error: function() {
            showNotification('Test failed. Please check your configuration.', 'error');
        }
    });
}
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
