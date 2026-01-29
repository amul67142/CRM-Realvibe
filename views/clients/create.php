<?php
$pageTitle = 'Add Client';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('clients') ?>" class="btn btn-ghost btn-sm">← Back to Clients</a>
</div>

<div class="card bg-base-100 shadow max-w-2xl mx-auto">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Add New Client</h2>
        
        <form method="POST" action="<?= url('clients/create') ?>">
            <?= csrfField() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Name *</span></label>
                    <input type="text" name="name" value="<?= e(old('name')) ?>" 
                           placeholder="Client Name" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Company Name</span></label>
                    <input type="text" name="company_name" value="<?= e(old('company_name')) ?>" 
                           placeholder="Company Name" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Email</span></label>
                    <input type="email" name="email" value="<?= e(old('email')) ?>" 
                           placeholder="email@example.com" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Phone</span></label>
                    <input type="tel" name="phone" value="<?= e(old('phone')) ?>" 
                           placeholder="9876543210" class="input input-bordered">
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Address</span></label>
                    <textarea name="address" rows="2" class="textarea textarea-bordered"
                              placeholder="Full Address"><?= e(old('address')) ?></textarea>
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Notes</span></label>
                    <textarea name="notes" rows="3" class="textarea textarea-bordered"
                              placeholder="Additional notes..."><?= e(old('notes')) ?></textarea>
                </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
                <button type="submit" class="btn btn-primary">Create Client</button>
            </div>
        </form>
    </div>
</div>

<?php clearOldInput(); ?>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
