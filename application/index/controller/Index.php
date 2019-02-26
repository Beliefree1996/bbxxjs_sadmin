<?php
namespace app\index\controller;

use think\helper\Time;
use think\View;
use think\Db;
use Cookie;
use Config;
use Cache;
use app\common\Base;
use app\index\model\Suser;
use app\index\model\User;
use app\index\model\UserInfo;
use app\index\model\UserBuy;
use app\index\model\Product;
use app\index\model\Number;
use app\index\model\Numberpool;
use app\index\model\CrmMobile;
use app\index\model\CrmMobilePc;
use app\index\model\RechargeRemarks;
use app\index\model\Seat;
use app\index\model\SeatWx;
use app\index\model\Task;
use Env;
use tools\Aes;
use tools\NetWork;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class Index extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    public function login()
    {
        $username = input("post.username");
        $password = input("post.password");
        $uuid1 = Uuid::uuid1();

        $rs = Suser::where("username",$username)->find();
        $token = $uuid1->toString();

        //Cookie::set($token, $rs["id"]);
        Cache::store('redis')->set($token,$rs["id"]);

        if (empty($rs)){
            Ajson('用户名密码错误!', '0001');
        }
        else{
            if (getmd5($password) != $rs["password"]){
                Ajson('用户名密码错误!', '0001');
            }
            else {
                Ajson('登陆成功!', '0000',$token);
            }
        }



    }

    public function logout(){
        cookie::set("Admin-Token","");
        Ajson('退出成功!', '0000');
    }

    public function userinfo(){
        $token = input("post.token");
        $id = Cache::get($token);
        $rs = Suser::where("id",$id)->find();

        $res = array(
            "roles"=>array('admin'),
            "token"=>$token,
            "id"=>$id,
            "userinfo"=>array(
                "introduction"=> '我是超级管理员',
                "avatar"=>'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif',
                "name"=>$rs["username"]
            )
        );

        Ajson('登陆成功!', '0000',$res);
    }

    // 导入号码表
    public function uploadexl()
    {
        $file = request()->file('file');
        $token = input("post.token");
        $sid = Cache::get($token);;

        if ($file) {
            $info = $file->move('../uploads/exl/');

            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 jpg
                $filename = $info->getSaveName();

            } else {
                // 上传失败获取错误信息
                $filename = $file->getError();

            }
        }

        //上传文件的地址
        $filename = Env::get('root_path') . 'uploads' . DIRECTORY_SEPARATOR . 'exl' . DIRECTORY_SEPARATOR . $filename;

        $this->doRequest('sai.bbxxjs.com', '/exltomysql', array(
                'filename' => $filename,
                'name' => $_FILES["file"]["name"],
                'sid' => $sid,
            )
        );
        //echo $fp;
        Ajson('导入成功!', '0000');
    }


    public function doRequest($host, $path, $param = array())
    {
        $query = isset($param) ? http_build_query($param) : '';

        $port = 80;
        $errno = 0;
        $errstr = '';
        $timeout = 10;

        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);

        $out = "POST " . $path . " HTTP/1.1\r\n";
        $out .= "host:" . $host . "\r\n";
        $out .= "content-length:" . strlen($query) . "\r\n";
        $out .= "content-type:application/x-www-form-urlencoded\r\n";
        $out .= "connection:close\r\n\r\n";
        $out .= $query;

        fputs($fp, $out);

        fclose($fp);

    }

    // 文件写入数据库
    public function exltomysql()
    {
        ignore_user_abort(true);
        set_time_limit(0);
        $filename = trim(input('post.filename'));
        $name = trim(input('post.name'));
        $sid = trim(input('post.sid'));
        $t = explode("/", $filename);

        file_put_contents(Env::get('runtime_path') . "log/test.txt", "exltomysql@" . $filename, FILE_APPEND);

        //判断截取文件
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        //file_put_contents(Env::get('runtime_path')."log/test.txt", "exltomysql@".$extension, FILE_APPEND);

        //区分上传文件格式
        if ($extension == 'xlsx') {
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        } else if ($extension == 'xls') {
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }

        $excel_array = $objPHPExcel->getsheet(0)->toArray();   //转换为数组格式
        array_shift($excel_array);  //删除第一个数组(标题);
        //file_put_contents(Env::get('runtime_path')."log/test.txt", "exltomysql@".json_encode($excel_array), FILE_APPEND);
        $data["dateline"] = time();
        $data['name'] = $name;
        $data['filename'] = $filename;
        $data['sid'] = $sid;
        $pid = CrmMobilePc::insertGetId($data);

        $city = [];
        $i = 0;
        //file_put_contents(Env::get('runtime_path')."log/test.txt", json_encode($excel_array), FILE_APPEND);
        foreach ($excel_array as $k => $v) {
            $num = trim($v[0]);
            if (!empty($num)){
                $city['mobile'] = $num;
                $city['pid'] = $pid;
                $city['sid'] = $sid;
                CrmMobile::insert($city);
            }
        }
    }

    // 文件列表显示
    public function mobilelist(){
//        $s = input('post.s');
//        $p = input('post.p');
        $name  = trim(input('post.name'));
        $token = input('post.token');
        $sid = Cache::get($token);

//        if ($s == 1){
//            $s = 0;
//        }else{
//            $s = ($s-1) * $p;
//        }


        if (!isset($name) || empty($name)){
            $rs = CrmMobilePc::where("sid",$sid)->where("isdel",0)->order("id","desc")->select();;
//            $re['total'] = CrmMobilePc::where("sid",$sid)->where("isdel",0)->count();
        }else{
            $rs = CrmMobilePc::where("name","like", "%" . $name . "%")->where("sid",$sid)->where("isdel",0)->order("id","desc")->select();
//            $rs = CrmMobilePc::where("name","like", "%" . $name . "%")->where("sid",$sid)->where("isdel",0)->order("id","desc")->limit($s,$p)->select();
//            $re['total'] = CrmMobilePc::where("name",$name)->where("sid",$sid)->where("isdel",0)->count();
        }

        $ad = Suser::where("id",$sid)->find();

        if (count($rs->toArray())>0){
            foreach ($rs as $v=>$a){
                $rs[$v]["user"] = $ad["username"];
                $rs[$v]["znum"] = CrmMobile::where("pid",$a["id"])->where("sid",$sid)->where("isdel",0)->count();
                $rs[$v]["snum"] = CrmMobile::where("pid",$a["id"])->where("sid",$sid)->where("aid",0)->where("zid",0)->where("isdel",0)->count();
                $rs[$v]["dateline"] = friendlyDate($a["dateline"], 'mohu');
            }
            $re['rows']=$rs;
        }else{
            $re['rows'] = [];
        }

        Ajson('查询成功!','0000',$re);
    }

    // 用户列表显示
    public function userlist(){
//        $s = input('post.s');
//        $p = input('post.p');
        $name = input('post.name');
        $token = input('post.token');
        $sid = Cache::get($token);

//        if ($s == 1){
//            $s = 0;
//        }else{
//            $s = ($s-1) * $p;
//        }


        if (empty($name)){
            $rs = User::where("sid",$sid)->select();
//            $rs = User::where("sid",$sid)->limit($s,$p)->select();
//            $re['total'] = User::where("sid",$sid)->count();
        }else{
            $rs = User::where('username', 'like', '%' . $name . '%')->where("sid",$sid)->select();
//            $rs = User::where('username', 'like', '%' . $name . '%')->where("sid",$sid)->limit($s,$p)->select();
//            $re['total'] = User::where('username', 'like', '%' . $name . '%')->where("sid",$sid)->count();
        }

//        $ad = Suser::where("id",$sid)->find();

        if (count($rs->toArray())>0){
            foreach ($rs as $v=>$a){
//                $rs[$v]["nme"] = $ad["username"];
//                $rs[$v]["name"] = $rs["username"];
//                $rs[$v]["mobile"] = $rs["mobile"];
//                $rs[$v]["isdown"] = $rs["isdown"];
            }
            $re['rows']=$rs;
        }else{
            $re['rows'] = [];
        }

        Ajson('查询成功!','0000',$re);
    }

    // 下载权限更改
    public function downchange()
    {
        $id = input("post.id");
        $tag = input("post.tag");

        if ($tag == 1) {
            User::where('id',$id)->setField('isdown',$tag);
            Ajson('执行成功!', '0000');
        }
         else if ($tag == 0) {
            User::where('id',$id)->setField('isdown',$tag);
            Ajson('执行成功!', '0000');
        }
        else {
            Ajson('执行失败!', '0001');
        }
    }

    // 号码全显权限更改
    public function showchange()
    {
        $id = input("post.id");
        $flag = input("post.flag");

        if ($flag == 1) {
            User::where('id',$id)->setField('notshow',$flag);
            Ajson('执行成功!', '0000');
        }
        else if ($flag == 0) {
            User::where('id',$id)->setField('notshow',$flag);
            Ajson('执行成功!', '0000');
        }
        else {
            Ajson('执行失败!', '0001');
        }
    }

//    // 获取话费余额
//    public function getcallmoney()
//    {
//        $sip = input("post.sip");
//        if (!empty($sip) && isset($sip)){
//            $money = getcallmoney($sip);
//        }else{
//            $money = getcallmoney();
//        }
//
//        Ajson('查询成功!', '0000', $money);
//    }

    // 分配号码列表
    public function ywuserlist()
    {
//        $s = input('post.s');
//        $p = input('post.p');
        $username = trim(input('post.name'));
        $token = input('post.token');
        $sid = Cache::get($token);

//        if ($s == 1) {
//            $s = 0;
//        } else {
//            $s = ($s - 1) * $p;
//        }


        if (empty($username)) {
            $rs = User::where("sid",$sid)->order('id', 'asc')->select();
//            $rs = User::where("sid",$sid)->order('id', 'asc')->limit($s, $p)->select();
//            $re['total'] = User::where("sid",$sid)->count();
        } else {
            $rs = User::where("sid",$sid)->where('username', 'like', '%' . $username . '%')->order('id', 'asc')->select();
//            $rs = User::where("sid",$sid)->where('username', 'like', '%' . $username . '%')->order('id', 'asc')->limit($s, $p)->select();
//            $re['total'] = User::where("sid",$sid)->where('username', 'like', '%' . $username . '%')->count();
        }

        if (count($rs->toArray())>0){
            foreach ($rs as $v=>$a){
                $syhm = CrmMobile::where("aid",$a["id"])->where("zid",0)->where("isdel",0)->count();
                $rs[$v]["syhm"] = $syhm;
            }
            $re['rows']=$rs;
        }else{
            $re['rows'] = [];
        }

        Ajson('查询成功!','0000',$re);
    }

    // 余额信息
    public function yeuserlist()
    {
//        $s = input('post.s');
//        $p = input('post.p');
        $username = trim(input('post.name'));
        $token = input('post.token');
        $sid = Cache::get($token);

//        if ($s == 1) {
//            $s = 0;
//        } else {
//            $s = ($s - 1) * $p;
//        }


        if (empty($username)) {
            $rs = User::where("sid",$sid)->order('id', 'asc')->select();
//            $rs = User::where("sid",$sid)->order('id', 'asc')->limit($s, $p)->select();
//            $re['total'] = User::where("sid",$sid)->count();
        } else {
            $rs = User::where("sid",$sid)->where('username', 'like', '%' . $username . '%')->order('id', 'asc')->select();
//            $rs = User::where("sid",$sid)->where('username', 'like', '%' . $username . '%')->order('id', 'asc')->limit($s, $p)->select();
//            $re['total'] = User::where("sid",$sid)->where('username', 'like', '%' . $username . '%')->count();
        }

        if (count($rs->toArray())>0){
            foreach ($rs as $v=>$a){
                $info = UserBuy::where("uid",$a["id"])->where("cid",1)->find();
                if (!empty($info)){
                    $sip = json_decode($info["setting"],true);
                    $money = getcallmoney($sip["sip"]);
                    $rs[$v]["callmoney"] = $money["callmoney"];
                    $rs[$v]["password"] = $money["password"];
                    $rs[$v]["e164s"] = $money["e164s"];
                }else{
                    $rs[$v]["callmoney"] = 0;
                }
            }
            $re['rows']=$rs;
        }else{
            $re['rows'] = [];
        }

        Ajson('查询成功!','0000',$re);
    }

    // 分配话费列表
    public function hfuserlist()
    {
//        $s = input('post.s');
//        $p = input('post.p');
        $username = trim(input('post.name'));
        $token = input('post.token');
        $sid = Cache::get($token);;

//        if ($s == 1) {
//            $s = 0;
//        } else {
//            $s = ($s - 1) * $p;
//        }


        if (empty($username)) {
            $rs = User::where("sid",$sid)->order('id', 'asc')->select();
//            $rs = User::where("sid",$sid)->order('id', 'asc')->limit($s, $p)->select();
//            $re['total'] = User::where("sid",$sid)->count();
        } else {
            $rs = User::where("sid",$sid)->where('username', 'like', '%' . $username . '%')->order('id', 'asc')->select();
//            $rs = User::where("sid",$sid)->where('username', 'like', '%' . $username . '%')->order('id', 'asc')->limit($s, $p)->select();
//            $re['total'] = User::where("sid",$sid)->where('username', 'like', '%' . $username . '%')->count();
        }

        if (count($rs->toArray())>0){
            foreach ($rs as $v=>$a){
                $rs[$v]["amount"] = Task::where("aid",$a["id"])->where("del",0)->where("zid", ">", 0)->count();
                $limits = User::where("id", $a["id"])->value("charge");
                if($limits == 0){
                    $limits = "无限制";
                }
                $rs[$v]["limit"] = $limits;
            }
            $re['rows']=$rs;
        }else{
            $re['rows'] = [];
        }

        Ajson('查询成功!','0000',$re);
    }

    // 删除
    public function delnumpc(){
        $id = input("post.id");

        //CrmMobile::where("pid",$id)->delete();
        CrmMobile::where("pid",$id)->setField("isdel",1);
        CrmMobilePc::where("id",$id)->setField("isdel",1);
        //CrmMobilePc::where("id",$id)->delete();

        Ajson('删除成功!','0000');
    }

    // 分配号码 剩余号码数信息
    public function creckpc(){
        $id = input("post.id");
        $token = input('post.token');
        $sid = Cache::get($token);;

        $rs = CrmMobilePc::where("id",$id)->find();

        $rs["znum"] = CrmMobile::where("pid",$id)->where("sid",$sid)->count();
        $rs["snum"] = CrmMobile::where("pid",$id)->where("sid",$sid)->where("aid",0)->where("zid",0)->count();
        $rs["dateline"] = $id."@".$sid;

        Ajson('查询成功!','0000',$rs);
    }

    // 分配话费 余额信息
    public function lefthf(){

//        Ajson('查询成功!','0000',$rs);
    }

    // 分配号码
    public function mobilefp(){
        $token = input('post.token');
        $sid = Cache::get($token);
        $pid = input("post.pid");
        $nums = input("post.num");
        $aidlist = input("post.aidlist");

        $info = Suser::where("id",$sid)->find();

        $in = array(
            "dateline"=>time(),
            "name"=>$info["username"],
        );

        if(strpos($aidlist,",") > 0){
            $aidlist = explode(',', $aidlist);

            foreach ($aidlist as $aid){
                $in["aid"] = $aid;
                $npid = CrmMobilePc::insertGetId($in);

                $rs = CrmMobile::where("sid",$sid)->where("aid", 0)->where("zid",0)->where("pid",$pid)->where("isdel",0)->order("id","Asc")->limit(0,$nums)->select();

                foreach ($rs as $v=>$a){
                    CrmMobile::where('id', $a["id"])->setField('pid', $npid);
                    CrmMobile::where('id', $a["id"])->setField('aid', $aid);
                }
            }
        }else {
            $in["aid"] = $aidlist;
            $npid = CrmMobilePc::insertGetId($in);

            $rs = CrmMobile::where("sid", $sid)->where("aid", 0)->where("zid", 0)->where("pid", $pid)->where("isdel", 0)->order("id", "Asc")->limit(0, $nums)->select();

            foreach ($rs as $v => $a) {
                CrmMobile::where('id', $a["id"])->setField('pid', $npid);
                CrmMobile::where('id', $a["id"])->setField('aid', $aidlist);
            }
        }

        Ajson('分配成功!','0000');
    }

    // 分配话费
    public function chargefp(){
        $nums = input("post.num");
        $aidlist = input("post.aidlist");

        if(strpos($aidlist,",") > 0){
            $aidlist = explode(',', $aidlist);

            foreach ($aidlist as $aid){
//                $count = Task::where("aid",$aid)->where("del",0)->count();
//                $charge = $nums*$count;
                User::where('id', $aid)->setField('charge', $nums);
            }
        }else {
//            $count = Task::where("aid", $aidlist)->where("del", 0)->count();
//            $charge = $nums*$count;
            User::where('id', $aidlist)->setField('charge', $nums);
        }

        Ajson('分配成功!','0000');
    }

    // 获取报表信息
    public function countdatalist(){
//        $aid = Cookie::get('aid');
//        $s = input('post.s');
//        $p = input('post.p');
        $aid = input('post.aid');
        $time = input('post.time');
//        if ($s == 1) {
//            $s = 0;
//        } else {
//            $s = ($s - 1) * $p;
//        }


        $res['rows'] = array();
//        $robot = Task::where("aid",$aid)->select();
        $robot = Task::where("aid",$aid)->where("zid","<>",0)->select();
        $rs = User::where("id",$aid)->find();
        $rs2 = SeatWx::where("aid",$aid)->whereBetweenTime('dateline', $time)->find();

        $countcallnum = 0;
        $countbillnum = 0;
        $countbilllv = 0;
        $countfenpeinum = 0;
        $countaddnum = 0;
        $countaddlv = 0;
        $i = 0;
        $a = 0;
        $b = 0;


        foreach ($robot as $v=>$k){
            $res['rows'][$v]["id"] = $i;
            $res['rows'][$v]["bumen"] = $rs["username"];
            $rs = Seat::where("id",$k["zid"])->find();

            $num = new Number();
            $num->setid($aid, $k["zid"]);

            $res['rows'][$v]["username"] = $rs["seatname"];
            $res['rows'][$v]["callnum"] = $num->whereBetweenTime('calldate', $time)->count();
            $countcallnum += $res['rows'][$v]["callnum"];

            //接通率
            $res['rows'][$v]["billnum"] = $num->whereBetweenTime('calldate', $time)->where('bill', '>', 0)->count();
            $countbillnum += $res['rows'][$v]["billnum"];
            if ($res['rows'][$v]["callnum"] > 0) {
                $res['rows'][$v]["billlv"] = ceil(($res['rows'][$v]["billnum"] / $res['rows'][$v]["callnum"]) * 100) . "%";
                $countbilllv += ceil(($res['rows'][$v]["billnum"] / $res['rows'][$v]["callnum"]) * 100);
            } else {
                $res['rows'][$v]["billlv"] = (0) . '%';
            }

            //A类客户
            $a = $num->alias('a')->leftJoin('bbxxjs.bb_crm_usertype b', 'a.callid = b.callid')
                ->whereBetweenTime('a.calldate', $time)->where("b.type", "a")->where('a.state', 10)->where('b.zid', $k["zid"])
                ->count();
            //B类客户
            $b = $num->alias('a')->leftJoin('bbxxjs.bb_crm_usertype b', 'a.callid = b.callid')
                ->whereBetweenTime('a.calldate', $time)->where("b.type", "b")->where('a.state', 10)->where('b.zid', $k["zid"])->count();

            $res['rows'][$v]["fenpeinum"] = $a+$b;
            $countfenpeinum += $res['rows'][$v]["fenpeinum"];

            if (empty($rs2)){
                $res['rows'][$v]["addnum"] = 0;
            }else{
                $res['rows'][$v]["addnum"] = $rs2["todaywx"];
                $countaddnum = $rs2["todaywx"];
            }

            if ($res['rows'][$v]["addnum"] > 0 && $res['rows'][$v]["fenpeinum"] > 0) {
                $res['rows'][$v]["addlv"] = ceil(($res['rows'][$v]["addnum"] / $res['rows'][$v]["fenpeinum"]) * 100) . "%";
                $countaddlv += ceil(($res['rows'][$v]["addnum"] / $res['rows'][$v]["fenpeinum"]) * 100);
            } else {
                $res['rows'][$v]["addlv"] = (0) . '%';
            }

            $i++;
        }



        if ($countcallnum > 0 && $countbillnum > 0){
            $callnum = ceil(($countbillnum / $countcallnum) * 100);
        }else{
            $callnum = 0;
        }

        if ($countaddnum > 0 && $countfenpeinum > 0){
            $fenpeinum = ceil(($countaddnum / $countfenpeinum) * 100);
        }else{
            $fenpeinum = 0;
        }

        $res['rows'][count($res['rows'])] = array(
            "id"=>"合计",
            "bumen"=>$countcallnum,
            "username"=>$countbillnum,
            "callnum"=>$callnum . "%",
            "billnum"=>$countfenpeinum,
            "billlv"=>$countaddnum,
            "fenpeinum"=>$fenpeinum . "%",
        );

        $res['total'] = Task::where("aid",$aid)->count() + 1;

        Ajson('查询成功！','0000',$res);
    }

    // 提交充值备注信息
    public function setremarks(){
        $token = input('post.token');
        $sid = Cache::get($token);
        $remarks = input('post.remarks');
        if(!empty($remarks)) {
            $data['sid'] = $sid;
            $data['remarks'] = $remarks;
            $data['dateline'] = time();
            RechargeRemarks::insert($data);
            Ajson('提交成功!','0000', $remarks);
        } else{
            Ajson('提交失败!','0001');
        }
    }

//    public function linshi(){
//        CrmMobile::where("isdel",1)->delete();
//        Ajson('执行成功!','0000');
//    }

}
