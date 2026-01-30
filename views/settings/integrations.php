<?php
$pageTitle = 'Integrations & Settings';
include BASE_PATH . 'views/layouts/header.php';
?>

<style>
.integration-card {
    transition: all 0.3s ease;
}
.integration-card:hover {
    transform: translateY(-2px);
}
.integration-status {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}
.integration-status.active {
    background-color: #10b981;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
}
.integration-status.inactive {
    background-color: #6b7280;
}
</style>

<div class="mb-6">
    <h2 class="text-3xl font-bold">Integrations</h2>
    <p class="text-gray-600 mt-1">Connect your favorite tools and services to supercharge your CRM</p>
</div>

<!-- Integration Categories Tabs -->
<div class="tabs tabs-boxed mb-6 bg-base-200">
    <a class="tab tab-active" data-category="messaging">💬 Messaging</a>
    <a class="tab" data-category="leads">📊 Lead Sources</a>
    <a class="tab" data-category="email">📧 Email & SMS</a>
</div>

<form method="POST" action="<?= url('settings/integrations') ?>">
    <?= csrfField() ?>
    
    <!-- Messaging Category -->
    <div class="category-content" data-category="messaging">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- AiSensy Integration Card -->
            <div class="card bg-base-100 shadow-lg integration-card border-2 <?= ($settings['aisensy_enabled'] ?? 1) ? 'border-success' : 'border-base-300' ?>">
                <div class="card-body">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                                <div class="w-12 rounded-lg bg-white p-2">
                                    <img src="https://www.aisensy.com/hubfs/aisensy_logo.svg" 
                                         alt="AiSensy" 
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-gradient-to-br from-purple-500 to-pink-500 text-white rounded-lg w-full h-full flex items-center justify-center\'><span class=\'text-2xl\'>🚀</span></div>';">
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg flex items-center gap-2">
                                    AiSensy
                                    <span class="integration-status <?= ($settings['aisensy_enabled'] ?? 1) ? 'active' : 'inactive' ?>"></span>
                                </h3>
                                <p class="text-sm text-gray-500">WhatsApp Business Automation</p>
                            </div>
                        </div>
                        <div class="form-control">
                            <input type="checkbox" name="aisensy_enabled" class="toggle toggle-success" 
                                   <?= checked($settings['aisensy_enabled'] ?? 1, 1) ?>>
                        </div>
                    </div>
                    
                    <div class="divider my-2"></div>
                    
                    <div class="space-y-3">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">API Key</span>
                            </label>
                            <input type="text" name="aisensy_api_key" 
                                   value="<?= e($settings['aisensy_api_key'] ?? AISENSY_API_KEY) ?>" 
                                   placeholder="Enter your AiSensy API Key" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Base URL</span>
                            </label>
                            <input type="text" name="aisensy_base_url" 
                                   value="<?= e($settings['aisensy_base_url'] ?? AISENSY_API_BASE_URL) ?>" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="alert <?= !empty($settings['aisensy_api_key']) ? 'alert-success' : 'alert-warning' ?> py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5">
                                <?php if (!empty($settings['aisensy_api_key'])): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                <?php endif; ?>
                            </svg>
                            <span class="text-xs">
                                <?= !empty($settings['aisensy_api_key']) ? 'API key configured - Integration active' : 'API key required' ?>
                            </span>
                        </div>
                        
                        <button type="button" class="btn btn-outline btn-sm w-full mt-2" 
                                onclick="showAiSensyTestModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Test AiSensy
                        </button>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Business API Card -->
            <div class="card bg-base-100 shadow-lg integration-card border-2 <?= ($settings['whatsapp_api_enabled'] ?? 0) ? 'border-success' : 'border-base-300' ?>">
                <div class="card-body">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                                <div class="w-12 rounded-lg bg-white p-2">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" 
                                         alt="WhatsApp" 
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-gradient-to-br from-green-500 to-teal-500 text-white rounded-lg w-full h-full flex items-center justify-center\'><span class=\'text-2xl\'>📱</span></div>';">
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg flex items-center gap-2">
                                    WhatsApp API
                                    <span class="integration-status <?= ($settings['whatsapp_api_enabled'] ?? 0) ? 'active' : 'inactive' ?>"></span>
                                </h3>
                                <p class="text-sm text-gray-500">Meta Business Platform</p>
                            </div>
                        </div>
                        <div class="form-control">
                            <input type="checkbox" name="whatsapp_api_enabled" class="toggle toggle-success" 
                                   <?= checked($settings['whatsapp_api_enabled'] ?? 0, 1) ?>>
                        </div>
                    </div>
                    
                    <div class="divider my-2"></div>
                    
                    <div class="space-y-3">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Access Token</span>
                            </label>
                            <input type="text" name="whatsapp_api_key" 
                                   value="<?= e($settings['whatsapp_api_key'] ?? '') ?>" 
                                   placeholder="Your WhatsApp API Key" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold text-xs">Phone Number ID</span>
                                </label>
                                <input type="text" name="whatsapp_phone_number_id" 
                                       value="<?= e($settings['whatsapp_phone_number_id'] ?? '') ?>" 
                                       placeholder="Phone ID" 
                                       class="input input-bordered input-sm">
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold text-xs">Business ID</span>
                                </label>
                                <input type="text" name="whatsapp_business_account_id" 
                                       value="<?= e($settings['whatsapp_business_account_id'] ?? '') ?>" 
                                       placeholder="Business ID" 
                                       class="input input-bordered input-sm">
                            </div>
                        </div>
                        
                         
                        <div class="alert <?= !empty($settings['whatsapp_api_key']) ? 'alert-success' : 'alert-warning' ?> py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5">
                                <?php if (!empty($settings['whatsapp_api_key'])): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                <?php endif; ?>
                            </svg>
                            <span class="text-xs">
                                <?= !empty($settings['whatsapp_api_key']) ? 'API configured - Integration ready' : 'API key required' ?>
                            </span>
                        </div>

                        <button type="button" class="btn btn-outline btn-sm w-full mt-2" 
                                onclick="testWhatsAppAPI()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Test WhatsApp API
                        </button>
                    </div>
                </div>
            </div>

            <!-- Twilio WhatsApp Card -->
            <div class="card bg-base-100 shadow-lg integration-card border-2 <?= ($settings['twilio_enabled'] ?? 0) ? 'border-success' : 'border-base-300' ?>">
                <div class="card-body">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                                <div class="w-12 rounded-lg bg-white p-2">
                                    <img src="https://www.vectorlogo.zone/logos/twilio/twilio-icon.svg" 
                                         alt="Twilio" 
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-gradient-to-br from-red-500 to-orange-500 text-white rounded-lg w-full h-full flex items-center justify-center\'><span class=\'text-2xl\'>💬</span></div>';">
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg flex items-center gap-2">
                                    Twilio
                                    <span class="integration-status <?= ($settings['twilio_enabled'] ?? 0) ? 'active' : 'inactive' ?>"></span>
                                </h3>
                                <p class="text-sm text-gray-500">WhatsApp & SMS Gateway</p>
                            </div>
                        </div>
                        <div class="form-control">
                            <input type="checkbox" name="twilio_enabled" class="toggle toggle-success" 
                                   <?= checked($settings['twilio_enabled'] ?? 0, 1) ?>>
                        </div>
                    </div>
                    
                    <div class="divider my-2"></div>
                    
                    <div class="space-y-3">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Account SID</span>
                            </label>
                            <input type="text" name="twilio_account_sid" 
                                   value="<?= e($settings['twilio_account_sid'] ?? '') ?>" 
                                   placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Auth Token</span>
                            </label>
                            <input type="password" name="twilio_auth_token" 
                                   value="<?= e($settings['twilio_auth_token'] ?? '') ?>" 
                                   placeholder="Your Auth Token" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">WhatsApp Number</span>
                            </label>
                            <input type="text" name="twilio_whatsapp_number" 
                                   value="<?= e($settings['twilio_whatsapp_number'] ?? '') ?>" 
                                   placeholder="whatsapp:+14155238886" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="alert <?= !empty($settings['twilio_account_sid']) && !empty($settings['twilio_auth_token']) ? 'alert-success' : 'alert-warning' ?> py-2 mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5">
                                <?php if (!empty($settings['twilio_account_sid']) && !empty($settings['twilio_auth_token'])): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                <?php endif; ?>
                            </svg>
                            <span class="text-xs">
                                <?= (!empty($settings['twilio_account_sid']) && !empty($settings['twilio_auth_token'])) ? 'Credentials configured' : 'Credentials required' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Provider Selection Card -->
            <div class="card bg-gradient-to-br from-blue-50 to-indigo-50 shadow-lg">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-4">⚙️ Primary WhatsApp Provider</h3>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Active Provider</span>
                        </label>
                        <select name="whatsapp_provider" class="select select-bordered select-primary">
                            <option value="aisensy" <?= selected($settings['whatsapp_provider'] ?? 'aisensy', 'aisensy') ?>>
                                🚀 AiSensy (Recommended)
                            </option>
                            <option value="whatsapp_api" <?= selected($settings['whatsapp_provider'] ?? '', 'whatsapp_api') ?>>
                                📱 WhatsApp Business API
                            </option>
                            <option value="twilio" <?= selected($settings['whatsapp_provider'] ?? '', 'twilio') ?>>
                                💬 Twilio WhatsApp
                            </option>
                        </select>
                    </div>

                    <div class="alert alert-info mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm">This will be used for all automated and manual WhatsApp messaging</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Sources Category -->
    <div class="category-content hidden" data-category="leads">
        <div class="grid grid-cols-1 gap-6 mb-6">
            <!-- Meta Lead Ads Card -->
            <div class="card bg-base-100 shadow-lg border-2 border-primary">
                <div class="card-body">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                                <div class="w-14 rounded-lg bg-white p-2">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/0/05/Facebook_Logo_%282019%29.png" 
                                         alt="Meta Facebook" 
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-gradient-to-br from-blue-600 to-blue-800 text-white rounded-lg w-full h-full flex items-center justify-center\'><span class=\'text-3xl\'>📘</span></div>';">
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-xl">Meta Lead Ads</h3>
                                <p class="text-sm text-gray-500">Facebook & Instagram Lead Generation</p>
                                <div class="badge badge-info badge-sm mt-1">Auto-import leads</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-bold">Webhook URL for Meta Developer Console:</h4>
                            <div class="bg-base-100 p-2 rounded mt-2 font-mono text-sm break-all">
                                <?= rtrim(BASE_URL, '/') ?>/api/webhooks/meta-leads
                            </div>
                            <p class="text-xs mt-2">Configure this URL in your Meta App's Webhook settings. Subscribe to <strong>leadgen</strong> events.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">App ID</span>
                            </label>
                            <input type="text" name="meta_app_id" 
                                   value="<?= e($settings['meta_app_id'] ?? '') ?>" 
                                   placeholder="Your Meta App ID" 
                                   class="input input-bordered">
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">App Secret</span>
                            </label>
                            <input type="password" name="meta_app_secret" 
                                   value="<?= e($settings['meta_app_secret'] ?? '') ?>" 
                                   placeholder="Your Meta App Secret" 
                                   class="input input-bordered">
                        </div>
                        
                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text font-semibold">Access Token</span>
                                <span class="label-text-alt text-xs">Required: leads_retrieval permission</span>
                            </label>
                            <textarea name="meta_access_token" rows="2"
                                      placeholder="Your Meta Access Token" 
                                      class="textarea textarea-bordered"><?= e($settings['meta_access_token'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text font-semibold">Verify Token</span>
                                <span class="label-text-alt text-xs">Custom token for webhook verification</span>
                            </label>
                            <input type="text" name="meta_verify_token" 
                                   value="<?= e($settings['meta_verify_token'] ?? '') ?>" 
                                   placeholder="Any random string (e.g., mySecretToken123)" 
                                   class="input input-bordered">
                        </div>
                    </div>
                    
                    <div class="flex gap-2 mt-4">
                        <button type="button" class="btn btn-success btn-sm flex-1" 
                                onclick="testIntegration('meta_leads')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Test Connection
                        </button>
                        <a href="https://developers.facebook.com/apps/" target="_blank" 
                           class="btn btn-outline btn-sm flex-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            Meta Console
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email & SMS Category -->
    <div class="category-content hidden" data-category="email">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- SMTP Email Card -->
            <div class="card bg-base-100 shadow-lg integration-card border-2 <?= ($settings['smtp_enabled'] ?? 0) ? 'border-success' : 'border-base-300' ?>">
                <div class="card-body">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-gradient-to-br from-yellow-500 to-orange-500 text-white rounded-lg w-12">
                                    <span class="text-2xl">📧</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg flex items-center gap-2">
                                    SMTP Email
                                    <span class="integration-status <?= ($settings['smtp_enabled'] ?? 0) ? 'active' : 'inactive' ?>"></span>
                                </h3>
                                <p class="text-sm text-gray-500">Custom Email Server</p>
                            </div>
                        </div>
                        <div class="form-control">
                            <input type="checkbox" name="smtp_enabled" class="toggle toggle-success" 
                                   <?= checked($settings['smtp_enabled'] ?? 0, 1) ?>>
                        </div>
                    </div>
                    
                    <div class="divider my-2"></div>
                    
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold text-xs">SMTP Host</span>
                                </label>
                                <input type="text" name="smtp_host" 
                                       value="<?= e($settings['smtp_host'] ?? '') ?>" 
                                       placeholder="smtp.gmail.com" 
                                       class="input input-bordered input-sm">
                            </div>
                            
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold text-xs">Port</span>
                                </label>
                                <input type="number" name="smtp_port" 
                                       value="<?= e($settings['smtp_port'] ?? '587') ?>" 
                                       placeholder="587" 
                                       class="input input-bordered input-sm">
                            </div>
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold text-xs">Username</span>
                            </label>
                            <input type="text" name="smtp_username" 
                                   value="<?= e($settings['smtp_username'] ?? '') ?>" 
                                   placeholder="your-email@gmail.com" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold text-xs">Password</span>
                            </label>
                            <input type="password" name="smtp_password" 
                                   value="<?= e($settings['smtp_password'] ?? '') ?>" 
                                   placeholder="Password or App Password" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold text-xs">From Email</span>
                            </label>
                            <input type="email" name="smtp_from_email" 
                                   value="<?= e($settings['smtp_from_email'] ?? '') ?>" 
                                   placeholder="noreply@yourdomain.com" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="alert <?= !empty($settings['smtp_host']) && !empty($settings['smtp_username']) ? 'alert-success' : 'alert-warning' ?> py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5">
                                <?php if (!empty($settings['smtp_host']) && !empty($settings['smtp_username'])): ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <?php else: ?>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                <?php endif; ?>
                            </svg>
                            <span class="text-xs">
                                <?= (!empty($settings['smtp_host']) && !empty($settings['smtp_username'])) ? 'SMTP configured' : 'Configuration incomplete' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SMS Gateway Card -->
            <div class="card bg-base-100 shadow-lg integration-card border-2 <?= ($settings['sms_enabled'] ?? 0) ? 'border-success' : 'border-base-300' ?>">
                <div class="card-body">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="avatar placeholder">
                                <div class="bg-gradient-to-br from-cyan-500 to-blue-500 text-white rounded-lg w-12">
                                    <span class="text-2xl">📱</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg flex items-center gap-2">
                                    SMS Gateway
                                    <span class="integration-status <?= ($settings['sms_enabled'] ?? 0) ? 'active' : 'inactive' ?>"></span>
                                </h3>
                                <p class="text-sm text-gray-500">Text Message Alerts</p>
                            </div>
                        </div>
                        <div class="form-control">
                            <input type="checkbox" name="sms_enabled" class="toggle toggle-success" 
                                   <?= checked($settings['sms_enabled'] ?? 0, 1) ?>>
                        </div>
                    </div>
                    
                    <div class="divider my-2"></div>
                    
                    <div class="space-y-3">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">SMS Provider</span>
                            </label>
                            <select name="sms_provider" class="select select-bordered select-sm">
                                <option value="none" <?= selected($settings['sms_provider'] ?? 'none', 'none') ?>>None</option>
                                <option value="twilio" <?= selected($settings['sms_provider'] ?? '', 'twilio') ?>>Twilio</option>
                                <option value="msg91" <?= selected($settings['sms_provider'] ?? '', 'msg91') ?>>MSG91</option>
                                <option value="textlocal" <?= selected($settings['sms_provider'] ?? '', 'textlocal') ?>>TextLocal</option>
                            </select>
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold text-xs">API Key</span>
                            </label>
                            <input type="text" name="sms_api_key" 
                                   value="<?= e($settings['sms_api_key'] ?? '') ?>" 
                                   placeholder="Your SMS API Key" 
                                   class="input input-bordered input-sm">
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold text-xs">Sender ID</span>
                            </label>
                            <input type="text" name="sms_sender_id" 
                                   value="<?= e($settings['sms_sender_id'] ?? '') ?>" 
                                   placeholder="RLVIBE" 
                                   class="input input-bordered input-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button (Sticky) -->
    <div class="sticky bottom-4 z-10">
        <button type="submit" class="btn btn-primary btn-lg w-full shadow-xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Save All Integrations
        </button>
    </div>
</form>

<script>
// Tab switching
document.querySelectorAll('.tabs .tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        const category = this.dataset.category;
        
        // Update active tab
        document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('tab-active'));
        this.classList.add('tab-active');
        
        // Show/hide content
        document.querySelectorAll('.category-content').forEach(content => {
            if (content.dataset.category === category) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
        });
    });
});

// AiSensy test with campaign and template
function showAiSensyTestModal() {
    const phone = prompt('Enter test phone number (with country code):');
    if (!phone) return;
    
    const campaign = prompt('Enter campaign name:', 'test_campaign');
    if (!campaign) return;
    
    const template = prompt('Enter template ID/name:', 'test_template');
    if (!template) return;
    
    showNotification('Testing AiSensy connection...', 'info');
    
    $.ajax({
        url: '<?= url('settings/test-integration') ?>',
        method: 'POST',
        data: {
            provider: 'aisensy',
            phone: phone,
            campaign: campaign,
            template: template
        },
        success: function(response) {
            if (response.success) {
                showNotification('✓ AiSensy test successful! Message sent.', 'success');
            } else {
                showNotification('✗ Test failed: ' + response.error, 'error');
            }
        },
        error: function() {
            showNotification('✗ Test failed. Check your configuration.', 'error');
        }
    });
}

// WhatsApp API test - simple test message
function testWhatsAppAPI() {
    const phone = prompt('Enter test phone number (with country code, e.g., 919876543210):');
    if (!phone) return;
    
    showNotification('Testing WhatsApp API connection...', 'info');
    
    $.ajax({
        url: '<?= url('settings/test-integration') ?>',
        method: 'POST',
        data: {
            provider: 'whatsapp_api',
            phone: phone
        },
        success: function(response) {
            if (response.success) {
                showNotification('✓ WhatsApp API test successful! Welcome message sent.', 'success');
            } else {
                showNotification('✗ Test failed: ' + response.error, 'error');
            }
        },
        error: function() {
            showNotification('✗ Test failed. Check your configuration.', 'error');
        }
    });
}

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
                showNotification('✓ Test successful! Message sent.', 'success');
            } else {
                showNotification('✗ Test failed: ' + response.error, 'error');
            }
        },
        error: function() {
            showNotification('✗ Test failed. Please check your configuration.', 'error');
        }
    });
}
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
