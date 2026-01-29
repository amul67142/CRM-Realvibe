<?php
$pageTitle = 'Edit Client';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('clients') ?>" class="btn btn-ghost btn-sm">← Back to Clients</a>
</div>

<div class="card bg-base-100 shadow max-w-2xl mx-auto">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Edit Client</h2>
        
        <form method="POST" action="<?= url("clients/edit?id={$client['id']}") ?>">
            <?= csrfField() ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Name *</span></label>
                    <input type="text" name="name" value="<?= e($client['name']) ?>" 
                           class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Company Name</span></label>
                    <input type="text" name="company_name" value="<?= e($client['company_name']) ?>" 
                           class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Email</span></label>
                    <input type="email" name="email" value="<?= e($client['email']) ?>" 
                           class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label"><span class="label-text">Phone</span></label>
                    <input type="tel" name="phone" value="<?= e($client['phone']) ?>" 
                           class="input input-bordered">
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Address</span></label>
                    <textarea name="address" rows="2" class="textarea textarea-bordered"><?= e($client['address']) ?></textarea>
                </div>
                
                <div class="form-control md:col-span-2">
                    <label class="label"><span class="label-text">Notes</span></label>
                    <textarea name="notes" rows="3" class="textarea textarea-bordered"><?= e($client['notes']) ?></textarea>
                </div>
            </div>
            
            <div class="card-actions justify-end mt-6">
                <button type="submit" class="btn btn-primary">Update Client</button>
            </div>
        </form>
    </div>
</div>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
