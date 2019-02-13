<?php
namespace app\common;

use think\Model;

class Config extends Model{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CONFIG__';

    public static function getConfig(){
        $result = self::find();
        return $result;
    }
}
?>