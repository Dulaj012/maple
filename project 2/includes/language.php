<?php
// Language helper functions
function initLanguage() {
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'en'; // Default language
    }
    
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si'])) {
        $_SESSION['language'] = $_GET['lang'];
    }
    
    $langFile = __DIR__ . '/languages/' . $_SESSION['language'] . '.php';
    if (file_exists($langFile)) {
        return require $langFile;
    }
    
    return require __DIR__ . '/languages/en.php'; // Fallback to English
}

function __($key) {
    static $translations = null;
    
    if ($translations === null) {
        $translations = initLanguage();
    }
    
    return $translations[$key] ?? $key;
}

// Language switcher HTML
function getLanguageSwitcher() {
    $currentLang = $_SESSION['language'] ?? 'en';
    $currentUrl = $_SERVER['REQUEST_URI'];
    $currentUrl = preg_replace('/[?&]lang=[^&]+/', '', $currentUrl);
    $separator = strpos($currentUrl, '?') === false ? '?' : '&';
    
    $html = '<div class="language-switcher">';
    $html .= '<a href="' . $currentUrl . $separator . 'lang=en" class="' . ($currentLang === 'en' ? 'active' : '') . '">English</a>';
    $html .= '<span class="separator">|</span>';
    $html .= '<a href="' . $currentUrl . $separator . 'lang=si" class="' . ($currentLang === 'si' ? 'active' : '') . '">සිංහල</a>';
    $html .= '</div>';
    
    return $html;
}