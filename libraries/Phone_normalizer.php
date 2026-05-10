<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Phone_normalizer
{
    public function normalize_bd_phone($phone)
    {
        $raw = trim((string) $phone);
        if ($raw === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if (function_exists('get_option') && get_option('unified_phone_normalization_enabled') === '0') {
            return substr((string) $digits, 0, 20);
        }
        if ($digits === '') {
            return '';
        }

        while (strpos($digits, '00') === 0) {
            $digits = substr($digits, 2);
        }

        if (strpos($digits, '880') === 0) {
            return substr($digits, 0, 13);
        }

        if (strpos($digits, '0') === 0 && strlen($digits) >= 11) {
            return '88' . substr($digits, 0, 11);
        }

        if (strlen($digits) >= 10 && strpos($digits, '1') === 0) {
            return '880' . substr($digits, 0, 10);
        }

        return substr($digits, 0, 20);
    }

    public function display_bd_phone($phone)
    {
        $normalized = $this->normalize_bd_phone($phone);
        if (strlen($normalized) === 13 && strpos($normalized, '8801') === 0) {
            return '+880 ' . substr($normalized, 3, 4) . '-' . substr($normalized, 7);
        }
        return $phone;
    }

    public function search_keys($phone)
    {
        $normalized = $this->normalize_bd_phone($phone);
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (function_exists('get_option') && get_option('unified_phone_like_search_enabled') === '0') {
            return array_values(array_filter(array_unique([trim((string) $phone), $digits, $normalized]), static function ($v) {
                return $v !== '';
            }));
        }
        $keys = [$phone, $digits, $normalized];
        foreach ([$normalized, $digits] as $value) {
            if ($value !== '') {
                if (strlen($value) >= 10) {
                    $keys[] = substr($value, -10);
                }
                if (strlen($value) >= 11) {
                    $keys[] = substr($value, -11);
                }
                if (strlen($value) >= 13 && strpos($value, '880') === 0) {
                    $keys[] = '0' . substr($value, -10);
                }
            }
        }
        $keys = array_map('trim', $keys);
        $keys = array_filter(array_unique($keys), static function ($v) {
            return $v !== '' && strlen($v) >= 4;
        });
        return array_values($keys);
    }
}
