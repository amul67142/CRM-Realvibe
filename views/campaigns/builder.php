<?php
$pageTitle = 'Campaign Builder - ' . $campaign['campaign_name'];
include BASE_PATH . 'views/layouts/header.php';

// Convert messages array to indexed by day
$messagesByDay = [];
foreach ($messages as $msg) {
    $messagesByDay[$msg['day_number']] = $msg;
}
?>

<div class="mb-6">
    <a href="<?= url('campaigns') ?>" class="btn btn-ghost btn-sm">← Back to Campaigns</a>
</div>

<div class="card bg-base-100 shadow mb-6">
    <div class="card-body">
        <h2 class="card-title text-2xl"><?= e($campaign['campaign_name']) ?></h2>
        <p class="text-gray-600"><?= e($campaign['project_name']) ?> • <?= $campaign['duration_days'] ?> days</p>
    </div>
</div>

<form method="POST" action="<?= url("campaigns/builder?id={$campaign['id']}") ?>">
    <?= csrfField() ?>
    
    <div class="space-y-6">
        <?php for ($day = 1; $day <= $campaign['duration_days']; $day++): ?>
            <?php $existingMsg = $messagesByDay[$day] ?? null; ?>
            
            <div class="card bg-base-100 shadow campaign-day-card">
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="campaign-day-number flex-shrink-0">
                            <?= $day ?>
                        </div>
                        
                        <div class="flex-1 space-y-4">
                            <h3 class="font-bold text-lg">Day <?= $day ?> Message</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Message Type</span></label>
                                    <select name="message_type_<?= $day ?>" class="select select-bordered select-sm">
                                        <option value="text" <?= selected($existingMsg['message_type'] ?? 'text', 'text') ?>>Text</option>
                                        <option value="image" <?= selected($existingMsg['message_type'] ?? '', 'image') ?>>Image</option>
                                        <option value="video" <?= selected($existingMsg['message_type'] ?? '', 'video') ?>>Video</option>
                                        <option value="document" <?= selected($existingMsg['message_type'] ?? '', 'document') ?>>Document</option>
                                    </select>
                                </div>
                                
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Send Time</span></label>
                                    <input type="time" name="send_time_<?= $day ?>" 
                                           value="<?= $existingMsg['send_time'] ?? '10:00:00' ?>" 
                                           class="input input-bordered input-sm">
                                </div>
                            </div>
                            
                            <div class="form-control">
                                <label class="label"><span class="label-text">Message Content *</span></label>
                                <textarea name="message_content_<?= $day ?>" rows="4" 
                                          class="textarea textarea-bordered" 
                                          placeholder="Your message here... Use {{name}}, {{project_name}}, etc." 
                                          required><?= e($existingMsg['message_content'] ?? '') ?></textarea>
                                <label class="label">
                                    <span class="label-text-alt">
                                        Available tags: {{name}}, {{first_name}}, {{project_name}}, {{location}}, {{price_range}}
                                    </span>
                                </label>
                            </div>
                            
                            <div class="form-control">
                                <label class="label"><span class="label-text">Media URL (for image/video/document)</span></label>
                                <input type="url" name="media_url_<?= $day ?>" 
                                       value="<?= e($existingMsg['media_url'] ?? '') ?>" 
                                       placeholder="https://example.com/image.jpg" 
                                       class="input input-bordered input-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    
    <div class="card bg-base-100 shadow mt-6">
        <div class="card-body">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <p>📌 Messages will be sent automatically based on the schedule</p>
                    <p>📌 Remember to activate the campaign after saving messages</p>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">Save All Messages</button>
            </div>
        </div>
    </div>
</form>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
