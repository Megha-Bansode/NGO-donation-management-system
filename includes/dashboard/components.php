<?php
// includes/dashboard/components.php

/**
 * Renders a Premium KPI Card
 */
function render_kpi_card($title, $value, $icon, $trendClass, $trendText, $url = '#') {
    // Escape output for safety
    $title = htmlspecialchars($title);
    $value = htmlspecialchars($value);
    $icon = htmlspecialchars($icon);
    $url = htmlspecialchars($url);
    
    echo "
    <a href='{$url}' class='kpi-card' style='text-decoration: none; color: inherit; display: flex; flex-direction: column;'>
        <div class='kpi-header'>
            <span class='kpi-title'>{$title}</span>
            <div class='kpi-icon'><i class='{$icon}'></i></div>
        </div>
        <div class='kpi-value counter-animate' data-target='{$value}'>0</div>
        <div class='kpi-trend {$trendClass}'>
            <i class='fas fa-arrow-" . (strpos($trendClass, 'up') !== false ? 'up' : (strpos($trendClass, 'down') !== false ? 'down' : 'minus')) . "'></i>
            <span>{$trendText}</span>
        </div>
    </a>
    ";
}

/**
 * Renders an Empty State Placeholder
 */
function render_empty_state($title, $desc, $icon, $buttonText = null, $buttonLink = '#') {
    echo "
    <div class='glass-card empty-state'>
        <div class='empty-icon'><i class='{$icon}'></i></div>
        <h3>{$title}</h3>
        <p>{$desc}</p>
    ";
    
    if ($buttonText) {
        echo "<a href='{$buttonLink}' class='btn-primary'>{$buttonText} <i class='fas fa-arrow-right'></i></a>";
    }
    
    echo "</div>";
}

/**
 * Renders a skeleton loader block
 */
function render_skeleton($height = '150px', $width = '100%', $borderRadius = '20px') {
    echo "<div class='skeleton' style='height: {$height}; width: {$width}; border-radius: {$borderRadius}; margin-bottom: 1.5rem;'></div>";
}

/**
 * Formats amount into Indian Numbering System (e.g. 1,25,000.00)
 */
function formatIndianCurrency($amount, $includeSymbol = true) {
    if (!is_numeric($amount)) return $amount;
    
    // Check if intl extension is loaded and fallback if not
    if (class_exists('NumberFormatter')) {
        $formatter = new NumberFormatter('en_IN', NumberFormatter::CURRENCY);
        $symbol = defined('APP_CURRENCY_SYMBOL') ? APP_CURRENCY_SYMBOL : '₹';
        $formatted = $formatter->formatCurrency($amount, 'INR');
        // Replace default symbol if needed, though en_IN INR usually returns ₹
        if (!$includeSymbol) {
            return trim(str_replace(['₹', 'Rs', 'INR'], '', $formatted));
        }
        return str_replace(['₹', 'Rs', 'INR'], $symbol, $formatted);
    }
    
    // Fallback manual Indian formatting
    $amount = round((float)$amount, 2);
    $parts = explode('.', (string)$amount);
    $integerPart = $parts[0];
    $decimalPart = isset($parts[1]) ? '.' . str_pad($parts[1], 2, '0', STR_PAD_RIGHT) : '.00';
    
    // Handle negative
    $isNegative = false;
    if (strpos($integerPart, '-') === 0) {
        $isNegative = true;
        $integerPart = substr($integerPart, 1);
    }

    $lastThree = substr($integerPart, -3);
    $rest = substr($integerPart, 0, -3);
    
    if ($rest !== '') {
        $lastThree = ',' . $lastThree;
        // Split rest by 2 digits
        $rest = strrev(implode(',', str_split(strrev($rest), 2)));
    }
    
    $formattedValue = $rest . $lastThree . $decimalPart;
    if ($isNegative) {
        $formattedValue = '-' . $formattedValue;
    }
    
    if ($includeSymbol) {
        $symbol = defined('APP_CURRENCY_SYMBOL') ? APP_CURRENCY_SYMBOL : '₹';
        return $symbol . $formattedValue;
    }
    
    return $formattedValue;
}
?>
