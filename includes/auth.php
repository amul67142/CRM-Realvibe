<?php
/**
 * Authentication Middleware
 * Session and authentication management
 */

// Check session timeout on every page load
checkSessionTimeout();

// Auto-logout inactive users
if (isLoggedIn()) {
    $_SESSION['last_activity'] = time();
}
