<?php
$pageTitle = 'Import Leads';
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url('leads') ?>" class="btn btn-ghost btn-sm">
        ← Back to Leads
    </a>
</div>

<div class="card bg-base-100 shadow max-w-3xl mx-auto">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Import Leads from CSV</h2>
        
        <div class="alert alert-info mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <h3 class="font-bold">Instructions</h3>
                <ul class="list-disc list-inside text-sm">
                    <li>File must be in <strong>CSV format</strong>.</li>
                    <li>Required columns: <strong>Name</strong> and either <strong>Whatsapp Number</strong> or <strong>Phone Number</strong>.</li>
                    <li>Supported columns from Google Sheet: 
                        <br><span class="opacity-75 text-xs">Date, Name, Email Id, Phone Number, Whatsapp Number, Lead Status 1, Lead Status 2, Feedback 1...</span>
                    </li>
                    <li>Dates will be preserved if provided.</li>
                </ul>
                <div class="mt-2">
                    <button onclick="downloadSample()" class="btn btn-sm btn-ghost border-white text-white">
                        📥 Download Sample Sheet CSV
                    </button>
                </div>
            </div>
        </div>
        
        <form method="POST" action="<?= url('leads/process-import') ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            
            <div class="form-control mb-4">
                <label class="label"><span class="label-text">Select Project *</span></label>
                <select name="project_id" class="select select-bordered" required>
                    <option value="">-- Select Project --</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= $project['id'] ?>">
                            <?= e($project['project_name']) ?> - <?= e($project['client_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-control mb-6">
                <label class="label"><span class="label-text">Upload CSV File *</span></label>
                <input type="file" name="csv_file" accept=".csv" class="file-input file-input-bordered w-full" required />
            </div>
            
            <div class="card-actions justify-end">
                <button type="submit" class="btn btn-primary">
                    Upload & Import
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function downloadSample() {
    // Sample based on user's Google Sheet format
    const headers = ["Date", "Name", "Email Id", "Phone Number", "Whatsapp Number", "Lead Status 1", "Lead Status 2", "Feedback 1", "Remarks"];
    const row = ["10/26", "John Doe", "john@example.com", "9876543210", "9876543210", "Interested", "Call Back", "Will connect today", "Realty Application"];
    
    const csvContent = headers.join(",") + "\n" + row.join(",");
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'leads_template.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
