<?php

return [
    'guard' => 'admin',
    
    'prefix' => 'admin',
    
    'middleware' => ['web', 'auth:admin'],
    
    'routes' => [
        'login' => 'admin.login',
        'dashboard' => 'admin.dashboard',
        'logout' => 'admin.logout',
    ],
    
    'auth' => [
        'model' => App\Models\Admin::class,
        'table' => 'admins',
    ],
];