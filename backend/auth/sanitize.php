<?php
function cleanString(string $val): string {
    return trim(strip_tags($val));
}

function cleanInt(mixed $val): int {
    return (int) filter_var($val, FILTER_SANITIZE_NUMBER_INT);
}

function cleanEmail(string $val): string {
    return filter_var(trim($val), FILTER_SANITIZE_EMAIL);
}

function cleanPhone(string $val): string {
    return preg_replace('/[^0-9\s\-().+]/', '', $val);
}