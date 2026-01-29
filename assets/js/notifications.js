/**
 * Notification Bell - Real-time Notifications
 * Handles notification bell updates and interactions
 */

let notificationPollInterval = null;
let lastNotificationCount = 0;

// Initialize notification system
document.addEventListener('DOMContentLoaded', function () {
    // Fetch notifications immediately
    fetchNotifications();

    // Poll for new notifications every 30 seconds
    notificationPollInterval = setInterval(fetchNotifications, 30000);

    // Mark all as read button
    const markAllBtn = document.getElementById('markAllNotificationsRead');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', markAllAsRead);
    }
});

/**
 * Fetch unread notifications
 */
async function fetchNotifications() {
    try {
        const response = await fetch(baseUrl + '/notifications/unread');
        const data = await response.json();

        if (data.success) {
            updateBadge(data.count);
            renderNotifications(data.notifications);

            // Play sound if new notifications appeared
            if (data.count > lastNotificationCount && lastNotificationCount > 0) {
                playNotificationSound();
            }

            lastNotificationCount = data.count;
        }
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
}

/**
 * Update notification badge count
 */
function updateBadge(count) {
    const badge = document.getElementById('notificationCount');
    const bellButton = document.getElementById('notificationBell');

    if (badge) {
        badge.textContent = count;
        if (count > 0) {
            badge.style.display = 'inline-block';
            bellButton.classList.add('animate-pulse');
        } else {
            badge.style.display = 'none';
            bellButton.classList.remove('animate-pulse');
        }
    }
}

/**
 * Render notifications in dropdown
 */
function renderNotifications(notifications) {
    const list = document.getElementById('notificationList');

    if (!list) return;

    if (notifications.length === 0) {
        list.innerHTML = `
            <li class="p-4 text-center text-gray-500">
                <p>No new notifications</p>
            </li>
        `;
        return;
    }

    list.innerHTML = '';

    notifications.forEach(notification => {
        const isUnread = notification.is_read == 0;
        const item = document.createElement('li');
        item.className = isUnread ? 'bg-primary/10' : '';

        item.innerHTML = `
            <a href="${baseUrl}/${notification.link}" 
               class="flex items-start gap-3 p-3 hover:bg-base-200"
               onclick="markNotificationAsRead(${notification.id}); return true;">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                        ${getIconSVG(notification.icon)}
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm ${isUnread ? 'text-primary' : ''}">${notification.title}</p>
                    <p class="text-sm text-gray-600 truncate">${notification.message}</p>
                    <p class="text-xs text-gray-400 mt-1">${notification.time_ago}</p>
                </div>
                ${isUnread ? '<span class="w-2 h-2 bg-primary rounded-full"></span>' : ''}
            </a>
        `;

        list.appendChild(item);
    });
}

/**
 * Get icon SVG based on type
 */
function getIconSVG(icon) {
    const icons = {
        'user-plus': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/></svg>',
        'globe': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"/></svg>',
        'message': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3.293 3.293 3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>',
        'bell': '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>'
    };

    return icons[icon] || icons['bell'];
}

/**
 * Mark notification as read
 */
function markNotificationAsRead(id) {
    fetch(baseUrl + '/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}`
    }).then(() => {
        // Refresh notifications after a short delay
        setTimeout(fetchNotifications, 500);
    });
}

/**
 * Mark all notifications as read
 */
async function markAllAsRead() {
    try {
        const response = await fetch(baseUrl + '/notifications/mark-all-read', {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            fetchNotifications();
        }
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
}

/**
 * Play notification sound (optional)
 */
function playNotificationSound() {
    // Simple beep sound using Web Audio API
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    oscillator.frequency.value = 800;
    oscillator.type = 'sine';

    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.5);
}

// Clean up on page unload
window.addEventListener('beforeunload', function () {
    if (notificationPollInterval) {
        clearInterval(notificationPollInterval);
    }
});
