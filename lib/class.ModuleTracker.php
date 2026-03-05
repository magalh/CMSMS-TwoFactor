<?php
# See LICENSE for full license information.

namespace TwoFactor;

class ModuleTracker {
    public static function track($module, $event_type, $CMS_VERSION, $module_version) {
        try {
            $url = 'https://api.cmsmadesimple.org/v1/modules/' . urlencode($module) . '/events';
            $data = json_encode([
                'eventType' => $event_type,
                'cmsVersion' => $CMS_VERSION,
                'moduleVersion' => $module_version
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 60);
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            // Silently fail
        }
    }
}
?>