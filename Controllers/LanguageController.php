<?php
require_once __DIR__ . '/../Core/I18n.php';

class LanguageController {
    public function Set() {
        I18n::boot();
        $lang = isset($_GET['lang']) ? strtolower((string)$_GET['lang']) : 'es';
        I18n::setLocale($lang);
        $prev = isset($_GET['prev']) ? (string)$_GET['prev'] : '';
        // Saneamos 'prev' para evitar open redirect: solo rutas internas
        if ($prev && strpos($prev, '/ProyectoPandora/') === 0) {
            header('Location: ' . $prev);
        } else {
            header('Location: /ProyectoPandora/Public/index.php?route=Default/Index');
        }
        exit;
    }
}
