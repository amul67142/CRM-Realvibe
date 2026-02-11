<?php
/**
 * View Helper Functions
 * Functions for rendering views and HTML elements
 */

/**
 * Load view file with data
 */
function view($template, $data = []) {
    extract($data);
    
    $viewFile = BASE_PATH . 'views/' . $template . '.php';
    
    if (file_exists($viewFile)) {
        include $viewFile;
    } else {
        die("View not found: $template");
    }
}

/**
 * Generate asset URL
 */
function asset($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Generate application URL
 */
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Escape HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Output JSON response
 */
function json($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get old form input value (after validation error)
 */
function old($field, $default = '') {
    return $_SESSION['old_input'][$field] ?? $default;
}

/**
 * Clear old input
 */
function clearOldInput() {
    unset($_SESSION['old_input']);
}

/**
 * Store old input
 */
function storeOldInput() {
    $_SESSION['old_input'] = $_POST;
}

/**
 * Output 'selected' attribute for select options
 */
function selected($value, $compare) {
    return $value == $compare ? 'selected' : '';
}

/**
 * Output 'checked' attribute for checkboxes/radios
 */
function checked($value, $compare) {
    return $value == $compare ? 'checked' : '';
}

/**
 * Generate status badge HTML
 */
function statusBadge($status) {
    $badges = [
        'new' => '<span class="badge badge-info">New</span>',
        'contacted' => '<span class="badge badge-primary">Contacted</span>',
        'interested' => '<span class="badge badge-warning">Interested</span>',
        'qualified' => '<span class="badge badge-success">Qualified</span>',
        'converted' => '<span class="badge badge-success">Converted</span>',
        'lost' => '<span class="badge badge-error">Lost</span>',
        'active' => '<span class="badge badge-success">Active</span>',
        'paused' => '<span class="badge badge-warning">Paused</span>',
        'completed' => '<span class="badge badge-info">Completed</span>',
        'cancelled' => '<span class="badge badge-error">Cancelled</span>',
        'unsubscribed' => '<span class="badge badge-error">Unsubscribed</span>',
    ];
    
    return $badges[$status] ?? '<span class="badge badge-ghost">' . ucfirst($status) . '</span>';
}

/**
 * Generate source badge HTML
 */
function sourceBadge($source) {
    if (empty($source)) {
        return '<span class="badge badge-ghost">Manual</span>';
    }
    
    $badges = [
        'meta' => '<span class="badge badge-primary">Meta/Facebook</span>',
        'wordpress' => '<span class="badge badge-info">WordPress</span>',
        'linkedin' => '<span class="badge badge-accent">LinkedIn</span>',
        'manual' => '<span class="badge badge-ghost">Manual</span>',
        'other' => '<span class="badge badge-ghost">Other</span>',
    ];
    
    // If source has a predefined badge, use it
    if (isset($badges[$source])) {
        return $badges[$source];
    }
    
    // For custom sources (like pyramid_alban), display with default styling
    // Replace underscores with spaces and capitalize words
    $displayName = ucwords(str_replace('_', ' ', $source));
    return '<span class="badge badge-outline">' . htmlspecialchars($displayName) . '</span>';
}

/**
 * Generate message status indicators
 */
function messageStatusIcon($status) {
    $icons = [
        'queued' => '<span class="text-gray-400" title="Queued">⏱</span>',
        'sent' => '<span class="text-gray-500" title="Sent">✓</span>',
        'delivered' => '<span class="text-blue-500" title="Delivered">✓✓</span>',
        'read' => '<span class="text-green-500" title="Read">✓✓</span>',
        'failed' => '<span class="text-red-500" title="Failed">✗</span>',
    ];
    
    return $icons[$status] ?? '';
}

/**
 * Generate pagination HTML
 */
function pagination($total, $perPage, $currentPage, $baseUrl) {
    $totalPages = ceil($total / $perPage);
    
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="join">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="join-item btn btn-sm">«</a>';
    } else {
        $html .= '<button class="join-item btn btn-sm btn-disabled">«</button>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<a href="' . $baseUrl . '?page=1" class="join-item btn btn-sm">1</a>';
        if ($start > 2) {
            $html .= '<button class="join-item btn btn-sm btn-disabled">...</button>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $currentPage) ? 'btn-active' : '';
        $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="join-item btn btn-sm ' . $active . '">' . $i . '</a>';
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<button class="join-item btn btn-sm btn-disabled">...</button>';
        }
        $html .= '<a href="' . $baseUrl . '?page=' . $totalPages . '" class="join-item btn btn-sm">' . $totalPages . '</a>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="join-item btn btn-sm">»</a>';
    } else {
        $html .= '<button class="join-item btn btn-sm btn-disabled">»</button>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Render layout with content
 */
function renderLayout($content, $title = '', $data = []) {
    $pageTitle = $title ? $title . ' - ' . APP_NAME : APP_NAME;
    include BASE_PATH . 'views/layouts/header.php';
    echo $content;
    include BASE_PATH . 'views/layouts/footer.php';
}

/**
 * Get CSRF token input field
 */
function csrfField() {
    $token = generateToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Get merge tag selector HTML
 */
function mergeTagSelector($insertInto = 'message_content') {
    $tags = [
        '{{name}}' => 'Lead Name',
        '{{first_name}}' => 'First Name',
        '{{phone}}' => 'Phone Number',
        '{{email}}' => 'Email Address',
        '{{project_name}}' => 'Project Name',
        '{{project_location}}' => 'Project Location',
        '{{project_type}}' => 'Project Type',
        '{{price_range}}' => 'Price Range',
        '{{client_name}}' => 'Client Name',
        '{{current_date}}' => 'Current Date',
        '{{agent_name}}' => 'Agent Name',
        '{{brochure_link}}' => 'Brochure Link',
    ];
    
    $html = '<div class="dropdown dropdown-end">';
    $html .= '<button type="button" class="btn btn-sm btn-outline" tabindex="0">Insert Merge Tag</button>';
    $html .= '<ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52 max-h-64 overflow-y-auto z-10">';
    
    foreach ($tags as $tag => $label) {
        $html .= '<li><a href="javascript:void(0)" onclick="insertMergeTag(\'' . $insertInto . '\', \'' . $tag . '\')">' . $label . '</a></li>';
    }
    
    $html .= '</ul>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < 3) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}
