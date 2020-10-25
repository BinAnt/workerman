<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// Route::get('think', function () {
//     return 'hello,ThinkPHP6!';
// });

// Route::post('test/:a/:b', 'StoreTask/test');

Route::post('info/:store_id/:money', 'StoreTask/info');
// Route::post('store', 'StoreTask/store');
