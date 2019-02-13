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

Route::any('login','index/index/login');
Route::any('logout','index/index/logout');
Route::any('userinfo','index/index/userinfo');
Route::any('uploadexl','index/index/uploadexl');
Route::any('mobilelist','index/index/mobilelist');
Route::any('userlist','index/index/userlist');
Route::any('downchange','index/index/downchange');
Route::any('showchange','index/index/showchange');
Route::any('exltomysql','index/index/exltomysql');
Route::any('delnumpc','index/index/delnumpc');
Route::any('ywuserlist','index/index/ywuserlist');
Route::any('hfuserlist','index/index/hfuserlist');
Route::any('creckpc','index/index/creckpc');
Route::any('lefthf','index/index/lefthf');
Route::any('mobilefp','index/index/mobilefp');
Route::any('chargefp','index/index/chargefp');
Route::any('countdatalist','index/index/countdatalist');
Route::any('exlcountdata','index/Api/exlcountdata');
Route::any('extoexl','index/Api/extoexl');
Route::any('deleteRecord','index/Api/deleteRecord');
Route::any('deleteTest','index/Api/deleteTest');

return [

];
