<?php
# See LICENSE for full license information.

class ModuleTracker {
    private static $api_url = 'https://api.pixelsolutions.biz/cmsms/module-counter';
    
    public static function track($module, $action) {
        $data = json_encode(['module' => $module, 'action' => $action]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $data,
                'timeout' => 5
            ]
        ]);
        
        @file_get_contents(self::$api_url, false, $context);
    }
}
?>