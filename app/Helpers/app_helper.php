<?php

if (!function_exists('app_version')) {
    /**
     * Get the current application version from VERSION.md
     *
     * @return string
     */
    function app_version(): string
    {
        $versionFile = base_path('VERSION.md');
        
        if (file_exists($versionFile)) {
            $content = file_get_contents($versionFile);
            if (preg_match('/## Current Version:\s*(v[\d.]+)/', $content, $matches)) {
                return $matches[1];
            }
        }
        
        return 'v1.0.0';
    }
}