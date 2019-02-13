<?php
namespace app\common;

use app\common\Config AS ConfigModel;

use View;
use think\Db;
use Config;
use Cookie;
use Session;
use think\Controller;

class Base extends Controller{
    public function __construct()
    {
        parent::__construct();
        $this->cross();

    }

    public function cross(){
        header('Access-Control-Allow-Origin *');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:x-requested-with,content-type,X-Token');
    }
}
?>