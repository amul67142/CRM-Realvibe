<?php
$pageTitle = 'Admin Profile';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('settings/integrations') ?>" class="btn btn-ghost btn-sm">← Back to Settings</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Profile Information -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-4">Admin Profile</h2>
            
            <form id="profileForm">
                <?= csrfField() ?>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Full Name *</span></label>
                    <input type="text" name="admin_name" value="<?= e($settings['admin_name']) ?>" 
                           class="input input-bordered" required>
                </div>
                
                <div class="form-control mt-4">
                    <label class="label"><span class="label-text">Email Address</span></label>
                    <input type="email" name="admin_email" value="<?= e($settings['admin_email']) ?>" 
                           class="input input-bordered" placeholder="admin@example.com">
                </div>
                
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Phone Number (for WhatsApp notifications)</span>
                    </label>
                    <input type="tel" name="admin_phone" value="<?= e($settings['admin_phone']) ?>" 
                           class="input input-bordered" placeholder="9876543210">
                    <label class="label">
                        <span class="label-text-alt">10-digit mobile number. You'll receive lead alerts on this number.</span>
                    </label>
                </div>
                
                <div class="form-control mt-4">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="admin_notification_enabled" class="checkbox checkbox-primary" 
                               <?= checked($settings['admin_notification_enabled'], true) ?>>
                        <span class="label-text">Receive lead notification alerts</span>
                    </label>
                    <label class="label">
                        <span class="label-text-alt">Enable/disable WhatsApp notifications when new leads are captured</span>
                    </label>
                </div>
                
                <div class="card-actions justify-end mt-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-4">Change Password</h2>
            
            <form id="passwordForm">
                <?= csrfField() ?>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Current Password *</span></label>
                    <input type="password" name="current_password" class="input input-bordered" required autocomplete="current-password">
                </div>
                
                <div class="form-control mt-4">
                    <label class="label"><span class="label-text">New Password *</span></label>
                    <input type="password" name="new_password" class="input input-bordered" required 
                           minlength="6" autocomplete="new-password">
                    <label class="label">
                        <span class="label-text-alt">Minimum 6 characters</span>
                    </label>
                </div>
                
                <div class="form-control mt-4">
                    <label class="label"><span class="label-text">Confirm New Password *</span></label>
                    <input type="password" name="confirm_password" class="input input-bordered" required 
                           minlength="6" autocomplete="new-password">
                </div>
                
                <div class="card-actions justify-end mt-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-lock"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle profile form submission
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<span class="loading loading-spinner"></span> Saving...';
    
    try {
        const response = await fetch('<?= url('settings/updateProfile') ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Profile updated successfully!');
        } else {
            alert('❌ Error: ' + (result.error ||'Failed to update profile'));
        }
    } catch (error) {
        alert('❌ Error: ' + error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = originalText;
    }
});

// Handle password form submission
document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const button = this.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    // Validate passwords match
    const newPass = formData.get('new_password');
    const confirmPass = formData.get('confirm_password');
    
    if (newPass !== confirmPass) {
        alert('❌ New passwords do not match!');
        return;
    }
    
    button.disabled = true;
    button.innerHTML = '<span class="loading loading-spinner"></span> Updating...';
    
    try {
        const response = await fetch('<?= url('settings/updatePassword') ?>', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Password changed successfully!');
            this.reset();
        } else {
            alert('❌ Error: ' + (result.error || 'Failed to update password'));
        }
    } catch (error) {
        alert('❌ Error: ' + error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = originalText;
    }
});
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
