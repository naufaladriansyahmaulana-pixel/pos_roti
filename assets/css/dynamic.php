<?php
// Dynamic CSS Generator based on Settings
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();
$pengaturan = new Pengaturan($db);

// Get color settings with defaults - Bakery Theme
$warna_primary = $pengaturan->get('warna_primary') ?? '#8B6F47';
$warna_secondary = $pengaturan->get('warna_secondary') ?? '#6B5435';
$warna_sidebar = $pengaturan->get('warna_sidebar') ?? '#6B5435';
$warna_sidebar_header = $pengaturan->get('warna_sidebar_header') ?? '#5a4530';
$warna_success = $pengaturan->get('warna_success') ?? '#8B6F47';
$warna_danger = $pengaturan->get('warna_danger') ?? '#e74c3c';
$warna_warning = $pengaturan->get('warna_warning') ?? '#f4a460';
$warna_info = $pengaturan->get('warna_info') ?? '#d4a574';

// Function to darken color
function darkenColor($hex, $percent = 20) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r - ($r * $percent / 100)));
    $g = max(0, min(255, $g - ($g * $percent / 100)));
    $b = max(0, min(255, $b - ($b * $percent / 100)));
    
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

// Function to lighten color
function lightenColor($hex, $percent = 20) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = min(255, $r + (255 - $r) * $percent / 100);
    $g = min(255, $g + (255 - $g) * $percent / 100);
    $b = min(255, $b + (255 - $b) * $percent / 100);
    
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

// Calculate variations
$warna_primary_dark = darkenColor($warna_primary, 15);
$warna_primary_light = lightenColor($warna_primary, 10);
$warna_sidebar_dark = darkenColor($warna_sidebar, 10);

// Set content type
header('Content-Type: text/css');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
?>
/* Dynamic CSS - Generated from Settings */
/* Primary Colors */
:root {
    --color-primary: <?php echo $warna_primary; ?>;
    --color-primary-dark: <?php echo $warna_primary_dark; ?>;
    --color-primary-light: <?php echo $warna_primary_light; ?>;
    --color-secondary: <?php echo $warna_secondary; ?>;
    --color-sidebar: <?php echo $warna_sidebar; ?>;
    --color-sidebar-header: <?php echo $warna_sidebar_header; ?>;
    --color-sidebar-dark: <?php echo $warna_sidebar_dark; ?>;
    --color-success: <?php echo $warna_success; ?>;
    --color-danger: <?php echo $warna_danger; ?>;
    --color-warning: <?php echo $warna_warning; ?>;
    --color-info: <?php echo $warna_info; ?>;
}

/* Login Page */
.login-page {
    background: linear-gradient(135deg, <?php echo $warna_primary; ?> 0%, <?php echo $warna_secondary; ?> 100%);
}

.login-form input:focus {
    border-color: <?php echo $warna_primary; ?>;
}

/* Buttons */
.btn-primary {
    background: linear-gradient(135deg, <?php echo $warna_primary; ?> 0%, <?php echo $warna_secondary; ?> 100%);
    color: white;
}

.btn-primary:hover {
    box-shadow: 0 5px 15px <?php echo $warna_primary; ?>66;
}

/* Sidebar */
.sidebar {
    background: <?php echo $warna_sidebar; ?>;
}

.sidebar-header {
    background: <?php echo $warna_sidebar_header; ?>;
    border-bottom: 1px solid <?php echo $warna_sidebar_dark; ?>;
}

.nav-link:hover {
    background: <?php echo $warna_sidebar_dark; ?>;
}

.nav-link.active {
    background: <?php echo $warna_primary; ?>;
    border-left-color: <?php echo $warna_primary; ?>;
}

/* Top Navigation */
.top-nav {
    background: white;
    border-bottom: 1px solid #ecf0f1;
}

/* Dashboard Cards */
.card-icon.primary { 
    background: <?php echo $warna_info; ?>; 
}

.card-icon.success { 
    background: <?php echo $warna_success; ?>; 
}

.card-icon.warning { 
    background: <?php echo $warna_warning; ?>; 
}

.card-icon.danger { 
    background: <?php echo $warna_danger; ?>; 
}

/* Links */
a {
    color: <?php echo $warna_primary; ?>;
}

a:hover {
    color: <?php echo $warna_primary_dark; ?>;
}

/* Form Focus */
input:focus,
select:focus,
textarea:focus {
    border-color: <?php echo $warna_primary; ?>;
}

/* Badge */
.badge-primary {
    background: <?php echo $warna_primary; ?>;
    color: white;
}

.badge-success {
    background: <?php echo $warna_success; ?>;
    color: white;
}

.badge-danger {
    background: <?php echo $warna_danger; ?>;
    color: white;
}

.badge-warning {
    background: <?php echo $warna_warning; ?>;
    color: white;
}

.badge-info {
    background: <?php echo $warna_info; ?>;
    color: white;
}

/* POS Specific */
.btn-process {
    background: <?php echo $warna_success; ?>;
}

.btn-process:hover {
    background: <?php echo darkenColor($warna_success, 10); ?>;
}

.product-card:hover {
    border-color: <?php echo $warna_primary; ?>;
    box-shadow: 0 2px 8px <?php echo $warna_primary; ?>33;
}

.product-card.selected {
    border-color: <?php echo $warna_success; ?>;
    background: <?php echo lightenColor($warna_success, 40); ?>;
}

