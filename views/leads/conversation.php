<?php
$pageTitle = 'Conversation - ' . $lead['name'];
include BASE_PATH . 'views/layouts/header.php';
?>

<div class="mb-6">
    <a href="<?= url("leads/{$lead['id']}") ?>" class="btn btn-ghost btn-sm">← Back to Lead Details</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Conversation -->
    <div class="lg:col-span-3">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="card-title"><?= e($lead['name']) ?></h2>
                    <div class="text-sm text-gray-600"><?= e($lead['phone']) ?></div>
                </div>
                
                <!-- Messages -->
                <div class="conversation-container" id="conversation">
                    <?php if (empty($conversation)): ?>
                        <div class="text-center text-gray-500 py-8">
                            <p>No messages yet</p>
                            <p class="text-sm">Start the conversation by sending a message below</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversation as $msg): ?>
                            <?php if ($msg['type'] === 'sent'): ?>
                                <!-- Sent Message -->
                                <div class="flex justify-end mb-4">
                                    <div class="chat-bubble-sent">
                                        <?= nl2br(e($msg['content'])) ?>
                                        <?php if ($msg['media_url']): ?>
                                            <div class="mt-2">
                                                <a href="<?= e($msg['media_url']) ?>" target="_blank" class="text-xs underline">
                                                    📎 Media
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="message-status">
                                            <?= messageStatusIcon($msg['status']) ?>
                                            <span class="text-xs"><?= formatDate($msg['timestamp'], 'M d, h:i A') ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Received Message -->
                                <div class="flex justify-start mb-4">
                                    <div class="chat-bubble-received">
                                        <?= nl2br(e($msg['content'])) ?>
                                        <?php if ($msg['media_url']): ?>
                                            <div class="mt-2">
                                                <a href="<?= e($msg['media_url']) ?>" target="_blank" class="text-xs underline">
                                                    📎 Media
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text-right mt-1">
                                            <span class="text-xs text-gray-500"><?= formatDate($msg['timestamp'], 'M d, h:i A') ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Send Message Form -->
                <?php if ($lead['is_subscribed']): ?>
                <div class="mt-4 pt-4 border-t">
                    <form id="send-message-form" class="flex gap-2">
                        <textarea id="message-input" rows="2" 
                                  class="textarea textarea-bordered flex-1" 
                                  placeholder="Type your message..."></textarea>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Send
                        </button>
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-warning mt-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>This lead has unsubscribed. You cannot send messages.</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title text-sm">Lead Info</h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-gray-500">Project:</span>
                        <p class="font-semibold"><?= e($lead['project_name']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Status:</span>
                        <p><?= statusBadge($lead['status']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Source:</span>
                        <p><?= sourceBadge($lead['source']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title text-sm mb-2">Quick Send</h3>
                <div class="space-y-2">
                    <?= mergeTagSelector('message-input') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Send message
$('#send-message-form').on('submit', function(e) {
    e.preventDefault();
    
    const message = $('#message-input').val().trim();
    if (!message) {
        showNotification('Please enter a message', 'warning');
        return;
    }
    
    // Disable form
    $('#message-input').prop('disabled', true);
    $('button[type="submit"]').prop('disabled', true);
    
    $.ajax({
        url: '<?= url('leads/send-message') ?>',
        method: 'POST',
        data: {
            lead_id: <?= $lead['id'] ?>,
            message: message
        },
        success: function(response) {
            if (response.success) {
                showNotification('Message sent successfully', 'success');
                $('#message-input').val('');
                
                // Add message to conversation
                const timestamp = new Date().toLocaleString();
                const messageHtml = `
                    <div class="flex justify-end mb-4">
                        <div class="chat-bubble-sent">
                            ${message.replace(/\n/g, '<br>')}
                            <div class="message-status">
                                <span class="text-gray-500">✓</span>
                                <span class="text-xs">${timestamp}</span>
                            </div>
                        </div>
                    </div>
                `;
                $('#conversation').append(messageHtml);
                
                // Scroll to bottom
                $('#conversation').scrollTop($('#conversation')[0].scrollHeight);
            } else {
                showNotification(response.error || 'Failed to send message', 'error');
            }
        },
        error: function() {
            showNotification('Error sending message', 'error');
        },
        complete: function() {
            $('#message-input').prop('disabled', false);
            $('button[type="submit"]').prop('disabled', false);
            $('#message-input').focus();
        }
    });
});

// Scroll to bottom on load
$(document).ready(function() {
    $('#conversation').scrollTop($('#conversation')[0].scrollHeight);
});
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>
