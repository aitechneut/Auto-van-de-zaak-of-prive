<?php
// Algemene helper functies

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatGeld($bedrag) {
    return '€ ' . number_format($bedrag, 2, ',', '.');
}

function berekenBelastingSchijf($inkomen) {
    // 2024 belastingschijven (simplified)
    if ($inkomen <= 73031) {
        return 0.3693;
    } else {
        return 0.495;
    }
}
