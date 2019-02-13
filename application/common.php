<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\facade\Config;
use think\facade\Route;
use think\facade\Cache;
use think\Db;
use mailer\phpmailer;
use tools\NetWork;
use app\common\Config AS ConfigModel;
use tools\Emoji;
use tools\Workday;
//use Exception;

/**
 * @cc 获取服务器信息
 */
function _get_sys_info(){
    $sys_info['os']             = PHP_OS;
    $sys_info['zlib']           = function_exists('gzclose') ? 'YES' : 'NO';//zlib
    $sys_info['safe_mode']      = (boolean) ini_get('safe_mode') ? 'YES' : 'NO';//safe_mode = Off
    $sys_info['timezone']       = function_exists("date_default_timezone_get") ? date_default_timezone_get() : "no_timezone";
    $sys_info['curl']			= function_exists('curl_init') ? 'YES' : 'NO';
    $sys_info['web_server']     = $_SERVER['SERVER_SOFTWARE'];
    $sys_info['phpv']           = phpversion();
    $sys_info['ip'] 			= GetHostByName($_SERVER['SERVER_NAME']);
    $sys_info['fileupload']     = @ini_get('file_uploads') ? ini_get('upload_max_filesize') :'unknown';
    $sys_info['max_ex_time'] 	= @ini_get("max_execution_time").'s'; //脚本最大执行时间
    $sys_info['set_time_limit'] = function_exists("set_time_limit") ? true : false;
    $sys_info['domain'] 		= $_SERVER['HTTP_HOST'];
    $sys_info['memory_limit']   = ini_get('memory_limit');

    $sys_info['author']   	    = Config::get('version.AN_AUTHOR');
    $sys_info['version']   	    = Config::get('version.AN_VERSION');
    $sys_info['update_time']   	    = Config::get('version.AN_UPDATE_TIME');

    $sys_info['mysql_version']  = mysqli_get_client_info();
    if(function_exists("gd_info")){
        $gd = gd_info();
        $sys_info['gdinfo'] 	= $gd['GD Version'];
    }else {
        $sys_info['gdinfo'] 	= "未知";
    }
    return $sys_info;
}

//异步返回
function Ajson($msg=null,$code="0001",$data=null,$isCallback=false,$callback=null){
    $res['code'] = $code;
    $res['msg'] = $msg;
    $res['data']=$data;

    if ($isCallback){
        echo $callback."(".json_encode($res).")";exit;
    }else{
        echo  json_encode($res);exit;
    }
}

function getmd5($pwd){
    return md5($pwd."@4!@#$%@");
}

/**
 * 正则验证
 * @param $reg
 * @param $txt
 * @return bool|false|intz
 */
function regular($reg,$txt){
    return $reg?preg_match($reg, $txt):false;
}

/**
 * 写日志文件
 * @param $arrx
 * @param $url
 */
function setlog($arrx,$url){
    $php="<?php \n /*自动写入配置*/ \n return ".var_export($arrx,true)." \n ?>";
    file_put_contents($url,$php,FILE_APPEND);
}

/**
 * 删除指定文件夹和文件
 * @param $dir
 * @return bool
 */
function deldir($dir){
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }
    closedir($dh);
    //删除当前文件夹：
    if(rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}
/**
 * 产生一个指定长度的随机字符串,并返回给用户
 * @param type $len 产生字符串的长度
 * @return string 随机字符串
 */
function genRandomString($len = 6) {
    $chars = array(
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    );
    $charsLen = count($chars) - 1;
    // 将数组打乱
    shuffle($chars);
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}

/**
 * 文章描述截取
 * @param $info
 * @param int $length
 * @return string
 */
function description($info,$length=200){
    $info=img_empty($info);
    $info = htmlspecialchars_decode($info);//把一些预定义的 HTML 实体转换为字符
    $info = str_replace("&nbsp;","",$info);//将空格替换成空
    $info = strip_tags($info);//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
    return mb_substr($info, 0, $length,"utf-8");//返回字符串中的前100字符串长度的字符
}

function mbStrSplit ($string, $len=1) {
    $start = 0;
    $strlen = mb_strlen($string);
    while ($strlen) {
        $array[] = mb_substr($string,$start,$len,"utf8");
        $string = mb_substr($string, $len, $strlen,"utf8");
        $strlen = mb_strlen($string);
    }
    return $array;
}

/**
 * 过滤字符串图片
 * @param $content
 * @return null|string|string[]
 */
function img_empty($content){
    $content=preg_replace("/<img.*?>/si","",$content);
    return $content;
}
/**
 * 递归移动源目录（包括文件和子文件）到目的目录【或移动源文件到新文件】
 * @param [string] $source 源目录或源文件
 * @param [string] $target 目的目录或目的文件
 * @return boolean true
 */
function moveFolder($source, $target){

    if(!file_exists($source))return false; //如果源目录/文件不存在返回false

    //如果要移动文件
    if(filetype($source) == 'file'){
        $basedir = dirname($target);
        if(!is_dir($basedir))mkdir($basedir); //目标目录不存在时给它创建目录
        copy($source, $target);
        unlink($source);
    }else{ //如果要移动目录
        if(!file_exists($target))mkdir($target); //目标目录不存在时就创建

        $files = array(); //存放文件
        $dirs = array(); //存放目录
        $fh = opendir($source);

        if($fh != false){
            while($row = readdir($fh)){
                $src_file = $source . '/' . $row; //每个源文件
                if($row != '.' && $row != '..'){
                    if(!is_dir($src_file)){
                        $files[] = $row;
                    }else{
                        $dirs[] = $row;
                    }
                }
            }
            closedir($fh);
        }

        foreach($files as $v){
            copy($source . '/' . $v, $target . '/' . $v);
            unlink($source . '/' . $v);
        }

        if(count($dirs)){
            foreach($dirs as $v){
                moveFolder($source . '/' . $v, $target . '/' . $v);
            }
        }
    }
    return true;
}
/**
 * 系统邮件发送函数
 * @param string $toemail 接收邮件者邮箱
 * @param string $toname 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function SendMail($toemail, $toname, $subject = '', $body = '', $attachment = null) {
    $sendmail = "postmaster@mkangou.com"; //发件人邮箱
    $sendmailpswd = "Yaoyi1986519@"; //客户端授权密码,而不是邮箱的登录密码！
    $send_name = "web";// 设置发件人信息，如邮件格式说明中的发件人，
    $port =465;
    $host="smtp.mxhichina.com";

    $mail = new PHPMailer();
    $mail->isSMTP();// 使用SMTP服务
    $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
    $mail->Host = $host;// 发送方的SMTP服务器地址
    $mail->SMTPAuth = true;// 是否使用身份验证
    $mail->Username = $sendmail;//// 发送方的
    $mail->Password = $sendmailpswd;//客户端授权密码,而不是邮箱的登录密码！
    $mail->SMTPSecure = "ssl";// 使用ssl协议方式
    $mail->Port = $port;//  qq端口465或587）
    $mail->setFrom($sendmail,$send_name);// 设置发件人信息，如邮件格式说明中的发件人，
    $mail->addAddress($toemail,$toname);// 设置收件人信息，如邮件格式说明中的收件人，
    $mail->addReplyTo($sendmail,$send_name);// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
    //$mail->addCC("xxx@qq.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
    //$mail->addBCC("xxx@qq.com");// 设置秘密抄送人(这个人也能收到邮件)
    //$mail->addAttachment("bug0.jpg");// 添加附件
    $mail->Subject = $subject;// 邮件标题
    $mail->Body = $body;// 邮件正文
    //$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用

    if(!$mail->send()){// 发送邮件
        Ajson("发送失败:".$mail->ErrorInfo,0);
    }else{
        return true;
    }
}
/**
 * 字符截取
 * @param $string 需要截取的字符串
 * @param $length 长度
 * @param $dot
 */
function str_cut($sourcestr, $length, $dot = '...') {
    $returnstr = '';
    $i = 0;
    $n = 0;
    $str_length = strlen($sourcestr); //字符串的字节数
    while (($n < $length) && ($i <= $str_length)) {
        $temp_str = substr($sourcestr, $i, 1);
        $ascnum = Ord($temp_str); //得到字符串中第$i位字符的ascii码
        if ($ascnum >= 224) {//如果ASCII位高与224，
            $returnstr = $returnstr . substr($sourcestr, $i, 3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i = $i + 3; //实际Byte计为3
            $n++; //字串长度计1
        } elseif ($ascnum >= 192) { //如果ASCII位高与192，
            $returnstr = $returnstr . substr($sourcestr, $i, 2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i = $i + 2; //实际Byte计为2
            $n++; //字串长度计1
        } elseif ($ascnum >= 65 && $ascnum <= 90) { //如果是大写字母，
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，大写字母计成一个高位字符
        } else {//其他情况下，包括小写字母和半角标点符号，
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i = $i + 1;            //实际的Byte数计1个
            $n = $n + 0.5;        //小写字母和半角标点等与半个高位字符宽...
        }
    }
    if ($str_length > strlen($returnstr)) {
        $returnstr = $returnstr . $dot; //超过长度时在尾处加上省略号
    }
    return $returnstr;
}
/**
 * 取得URL地址中域名部分
 * @param type $url
 * @return \url 返回域名
 */
function urlDomain($url) {
    if ($url) {
        $pathinfo = parse_url($url);
        return $pathinfo['scheme'] . "://" . $pathinfo['host'] . "/";
    }
    return false;
}

/**
 * 获取当前页面完整URL地址
 * @return type 地址
 */
function get_url() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
}

/**
 * 友好的时间显示
 *
 * @param int    $sTime 待显示的时间
 * @param string $type  类型. normal | mohu | full | ymd | other
 * @param string $alt   已失效
 * @return string
 */
function friendlyDate($sTime,$type = 'normal',$alt = 'false') {
    //sTime=源时间，cTime=当前时间，dTime=时间差
    $cTime        =    time();
    $dTime        =    $cTime - $sTime;
    $dDay        =    intval(date("z",$cTime)) - intval(date("z",$sTime));
    //$dDay        =    intval($dTime/3600/24);
    $dYear        =    intval(date("Y",$cTime)) - intval(date("Y",$sTime));
    //normal：n秒前，n分钟前，n小时前，日期
    if($type=='normal'){
        if( $dTime < 60 ){
            return $dTime."秒前";
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
            //今天的数据.年份相同.日期相同.
        }elseif( $dYear==0 && $dDay == 0  ){
            //return intval($dTime/3600)."小时前";
            return '今天'.date('H:i',$sTime);
        }elseif($dYear==0){
            return date("m月d日 H:i",$sTime);
        }else{
            return date("Y-m-d H:i",$sTime);
        }
    }elseif($type=='mohu'){
        if( $dTime < 60 ){
            return $dTime."秒前";
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600)."小时前";
        }elseif( $dDay > 0 && $dDay<=7 ){
            return intval($dDay)."天前";
        }elseif( $dDay > 7 &&  $dDay <= 30 ){
            return intval($dDay/7) . '周前';
        }elseif( $dDay > 30 ){
            return intval($dDay/30) . '个月前';
        }
        //full: Y-m-d , H:i:s
    }elseif($type=='full'){
        return date("Y-m-d H:i:s",$sTime);
    }elseif($type=='ymd'){
        return date("Y-m-d",$sTime);
    }else{
        if( $dTime < 60 ){
            return $dTime."秒前";
        }elseif( $dTime < 3600 ){
            return intval($dTime/60)."分钟前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600)."小时前";
        }elseif($dYear==0){
            return date("Y-m-d H:i:s",$sTime);
        }else{
            return date("Y-m-d H:i:s",$sTime);
        }
    }
}

function percentEncode($string) {
    $string = urlencode ( $string );
    $string = preg_replace ( '/\+/', '%20', $string );
    $string = preg_replace ( '/\*/', '%2A', $string );
    $string = preg_replace ( '/%7E/', '~', $string );
    return $string;
}

function computeSignature($parameters, $accessKeySecret) {
    ksort ( $parameters );
    $canonicalizedQueryString = '';
    foreach ( $parameters as $key => $value ) {
        $canonicalizedQueryString .= '&' . percentEncode ( $key ) . '=' . percentEncode ( $value );
    }
    $stringToSign = 'GET&%2F&' . percentencode ( substr ( $canonicalizedQueryString, 1 ) );
    $signature = base64_encode ( hash_hmac ( 'sha1', $stringToSign, $accessKeySecret . '&', true ) );
    return $signature;
}

function creckwxappv($v){
    $sv =Config::get('version.WXAPP_VERSION');

    if ($v == $sv){
        return 1;
    }else{
        return 0;
    }
}

function AccessToken($appid,$secret){
    $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
    $AccessToken = file_get_contents($url);
    $AccessToken = json_decode($AccessToken , true);
    $AccessToken = $AccessToken['access_token'];
    return $AccessToken;
}

function thumb($file,$dir,$filename,$w,$h){
    $image = \think\Image::open($file);
    $path = "../uploads/" . $dir ."/";
    $filename = 'thumb_'.$filename;
    $image->thumb($w,$h)->save($path . $filename);
    return $filename;
}

function array_remove($data, $key){
    if(!array_key_exists($key, $data)){
        return $data;
    }
    $keys = array_keys($data);
    $index = array_search($key, $keys);
    if($index !== FALSE){
        array_splice($data, $index, 1);
    }
    return $data;

}

function randgy(){
    $pn = rand(1,742);
    $num = rand(1,48);
    $mapi = 'https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?from_mid=1&format=json&ie=utf-8&oe=utf-8&subtitle=%E6%A0%BC%E8%A8%80&query=%E6%A0%BC%E8%A8%80&rn=100&stat0=&pn='.$pn.'&resource_id=6844';
    $mdata = file_get_contents($mapi);
    $mdata= json_decode($mdata,true);
    $gy= $mdata["data"][0]["disp_data"][$num];

    return $gy["ename"].'-'.$gy["author"];
}

function getRndWords($giveStr="", $num){
    $str     = "奵奺奻奼奾奿妀妁妅妉妊妋妌妍妎妏妐妑妔妕妗妘妚妛妜妟妠妡妢妤妦妧妩妫妭妮妯妰妱妲妴妵妶妷妸妺妼妽妿姀姁姂姃姄姅姆姇姈姉姊姌姗姎姏姒姕姖姘姙姛姝姞姟姠姡姢姣姤姥奸姧姨姩姫姬姭姮姯姰姱姲
姳姴姵姶姷姸姹姺姻姼姽姾娀威娂娅娆娈娉娊娋娌娍娎娏娐娑娒娓娔娕娖娗娙娚娱娜娝娞娟娠娡娢娣娤娥娦娧娨娩娪娫娬娭娮娯娰娱娲娳娴娵娷娸娹娺娻娽娾娿婀娄婂婃婄婅婇婈婋婌婍婎婏婐婑婒婓婔婕婖婗婘婙婛婜婝婞
婟婠婡婢婣婤婥妇婧婨婩婪婫娅婮婯婰婱婲婳婵婷婸婹婺婻婼婽婾婿媀媁媂媄媃媅媪媈媉媊媋媌媍媎媏媐媑媒媓媔媕媖媗媘媙媚媛媜媝媜媞媟媠媡媢媣媤媥媦媨媩媪媫媬媭妫媰媱媲媳媴媵媶媷媸媹媺媻媪媾嫀嫃嫄嫅嫆嫇嫈
嫉嫊袅嫌嫍嫎嫏嫐嫑嫒嫓嫔嫕嫖妪嫘嫙嫚嫛嫜嫝嫞嫟嫠嫡嫢嫣嫤嫥嫦嫧嫨嫧嫩嫪嫫嫬嫭嫮嫯嫰嫱嫲嫳嫴嫳妩嫶嫷嫸嫹嫺娴嫼嫽嫾婳妫嬁嬂嬃嬄嬅嬆嬇娆嬉嬊娇嬍嬎嬏嬐嬑嬒嬓嬔嬕嬖嬗嬘嫱嬚嬛嬜嬞嬟嬠嫒嬢嬣嬥嬦嬧嬨嬩嫔
嬫嬬奶嬬嬮嬯婴嬱嬲嬳嬴嬵嬶嬷婶嬹嬺嬻嬼嬽嬾嬿孀孁孂娘孄孅孆孇孆孈孉孊娈孋孊孍孎孏嫫婿媚朲朳枛朸朹朻朼朾朿杁杄杅杆圬杈杉杊杋杍杒杓杔杕杗杘杙杚杛杝杞杢杣杤杦杧杩杪杫杬杮柿杰东杲杳杴杵杶杷杸杹杺杻杼
杽枀枂枃枅枆枇枈枊枋枌枍枎枏析枑枒枓枔枖枘枙枛枞枟枠枡枤枥枦枧枨枩枬枭枮枰枱枲枳枵枷枸枹枺枻枼枽枾枿柀柁柂柃柄柅柆柇柈柉柊柋柌柍柎柒柕柖柗柘柙查楂呆柙柚柛柜柝柞柟柠柡柢柣柤柦柧柨柩柪柬柭柮柯柰柲
柳栅柶柷柸柹拐査柼柽柾栀栁栂栃栄栆栈栉栊栋栌栍栎栐旬栔栕栗栘栙栚栛栜栝栞栟栠栢栣栤栥栦栧栨栩株栫栬栭栮栯栰栱栲栳栴栵栶核栺栻栽栾栿桀桁桂桄桅桇桉桊桋桍桎桏桒桕桖桗桘桙桚桛桜桝桞桟桠桡桢档桤桦桧桨
桩桪桫桬桭杯桯桰桱桲桳桴桵桶桷桸桹桺桻桼桽桾杆梀梁梂梃梄梅梆梇梈梉枣梌梍梎梏梐梑梒梓梕梖梗枧梙梚梛梜梞梠梡梢梣梤梥梧梩梪梫梬梭梮梯械梲梴梵梶梷梸梹梺梻梼梽梾梿检棁棂棃棅棆棇棈棉棊棋棌棍棎棏棐棒棓
棔棕枨枣棘棙棚棛棜棝棞栋棠棡棢棣棤棥棦棨棩棪棫桊棭棯棰棱栖棳棴棵梾棷棸棹棺棻棼棽棾棿椀椁椂椃椄椆椇椈椉椊椋椌椎桠椐椒椓椔椕椖椗椘椙椚椛検椝椞椟椠椡椢椣椤椥椦椧椨椩椪椫椬椭椮椯椰椱椲椳椴椵椶椷椸椹
椺椻椼椽椾椿楀楁楂楃楅楆楇楈楉杨楋楌楍楎楏楐楑楒楔楕楖楗楘楛楜楝楞楟楠楡楢楣楤楥楦楧桢楩楪楫楬楮椑楯楰楱楲楳楴极楶榉榊榋榌楷楸楹楺楻楽楾楿榀榁榃榄榅榆榇榈榉榊榋榌榍槝搌榑榒榓榔榕榖榗榘榙榚榛榜榝
榞榟榠榡榢榣榤榥榧榨榩杩榫榬榭榯榰榱榲榳榴榵榶榷榸榹榺榻榼榽榾桤槀槁槂盘槄槅槆槇槈槉槊构槌枪槎槏槐槑槒杠槔槕槖槗様槙槚槛槜槝槞槟槠槡槢槣槥槦椠椁槩槪槫槬槭槮槯槰槱槲桨槴槵槶槷槸槹槺槻槼槽槾槿樀桩
樃樄枞樆樇樈樉樊樋樌樍樎樏樐樒樔樕樖樗樘樚樛樜樝樟樠樢样樤樥樦樧樨権横樫樬樭樮樯樰樱樲樳樴樵樶樷朴树桦樻樼樽樾樿橀橁橂橃橄橅橆橇桡橉橊桥橌橍橎橏橐橑橒橓橔橕橖橗橘橙橚橛橜橝橞橠橡椭橣橤橥橧橨橩橪
橬橭橮橯橰橱橲橳橴橵橶橷橸橹橺橻橼柜橿檀檩檂檃檄檅檆檇檈柽檊檋檌檍檎檏檐檑檒檓档檕檖檗檘檙檚檛桧檝檞槚檠檡检樯檤檥檦檧檨檩檪檫檬檭梼檰檱檲槟檴檵檶栎柠檹檺槛檼檽桐檿櫀櫁棹柜櫄櫅櫆櫇櫈櫉櫊櫋櫌櫍櫎
櫏累櫑櫒櫔櫕櫖櫗櫘櫙榈栉櫜椟橼櫠櫡櫢櫣櫤橱櫦槠栌櫩枥橥榇櫭櫮櫯櫰櫱櫲栊櫴櫵櫶櫷榉櫹櫼櫽櫾櫿欀欁欂欃栏欅欆欇欈欉权欋欌欍欎椤欐欑栾欓欔欕榄欗欘欙欚欛欜欝棂欟氶氷凼氺氻氼氽氾氿汀汃汄汅氽汈汊汋汌泛汏
汐汑汒汓汔汕汖汘污汚汛汜汞汢汣汥汦汧汨汩汫汬汭汮汯汰汱汲汳汴汵汶汷汸汹汻汼汾汿沀沂沃沄沅沆沇沊沋沌冱沎沏洓沓沔沕沗沘沚沛沜沝沞沠沢沣沤沥沦沨沩沪沫沬沭沮沯沰沱沲沴沵沶沷沸沺沽泀泂泃泅泆泇泈泋泌泍
泎泏泐泑泒泓泔泖泗泘泙泚泜溯泞泟泠泤泦泧泩泫泬泭泮泯泱泲泴泵泶泷泸泹泺泾泿洀洂洃洄洅洆洇洈洉洊洌洍洎洏洐洑洒洓洔洕洖洘洙洚洜洝洠洡洢洣洤洦洧洨洫洬洭洮洯洰洱洳洴洵洷洸洹洺洼洽洿浀浂浃浄浈浉浊浌浍
浏浐浒浔浕浖浗浘浚浛浜浝浞浟浠浡浢浣浤浥浦浧浨浫浭浯浰浱浲浳浵浶浃浺浻浼浽浾浿涀涁涂涃涄涅涆泾涊涋涍涎涐涑涒涓涔涖涗涘涙涚涜涝涞涟涠涡涢涣涤涥涧涪涫涬涭涰涱涳涴涶涷涸涹涺涻凉涽涾涿淁淂淃淄淅淆淇
淈淉淊淌淍淎淏淐淓淔淕淖淗淙淛淜淞淟淠淢淣淤渌淦淧沦淬淭淯淰淲淳淴涞滍淾淿渀渁渂渃渄渆渇済渋渌渍渎渏渑渒渓渕渖渘渚渜渝渞渟沨渥渧渨渪渫渮渰渱渲渳渵渶渷渹渻渼渽渿湀湁湂湄湅湆湇湈湉湋湌湍湎湏湐湑湒
湓湔湕湗湙湚湜湝浈湟湠湡湢湤湥湦湨湩湪湫湬湭湮湰湱湲湳湴湵湶湷湸湹湺湻湼湽満溁溂溄溆溇沩溉溊溋溌溍溎溏溑溒溓溔溕溗溘溙溚溛溞溟溠溡溣溤溥溦溧溨溩溬溭溯溰溱溲涢溴溵溶溷溸溹溻溽溾溿滀滁滂滃沧滆滇滈
滉滊涤滍荥滏滐滒滓滖滗滘滙滛滜滝滞滟滠滢滣滦滧滪滫沪滭滮滰滱渗滳滵滶滹滺浐滼滽漀漃漄漅漈漉溇漋漌漍漎漐漑澙熹漗漘漙沤漛漜漝漞漟漡漤漥漦漧漨漪渍漭漮漯漰漱漳漴溆漶漷漹漺漻漼漽漾浆潀颍潂潃潄潅潆潇潈
潉潊潋潌潍潎潏潐潒潓洁潕潖潗潘沩潚潜潝潞潟潠潡潢潣润潥潦潧潨潩潪潫潬潭浔溃潱潲潳潴潵潶滗潸潹潺潻潼潽潾涠澁澄澃澅浇涝澈澉澊澋澌澍澎澏湃澐澑澒澓澔澕澖涧澘澙澚澛澜澝澞澟渑澢澣泽澥滪澧澨澪澫澬澭浍澯
澰淀澲澳澴澵澶澷澸澹澺澻澼澽澾澿濂濄濅濆濇濈濉濊濋濌濍濎濏濐濑濒濓沵濖濗泞濙濚濛浕濝濞济濠濡濢濣涛濥濦濧濨濩濪滥浚濭濮濯潍滨濲濳濴濵濶濷濸濹溅濻泺濽滤濿瀀漾瀂瀃灋渎瀇瀈泻瀊沈瀌瀍瀎浏瀐瀒瀓瀔濒瀖
瀗泸瀙瀚瀛瀜瀞潇潆瀡瀢瀣瀤瀥潴泷濑瀩瀪瀫瀬瀭瀮瀯弥瀱潋瀳瀴瀵瀶瀷瀸瀹瀺瀻瀼瀽澜瀿灀灁瀺灂沣滠灅灆灇灈灉灊灋灌灍灎灏灐洒灒灓漓灖灗滩灙灚灛灜灏灞灟灠灡灢湾滦灥灦灧灨灪";# 字库
    $newStr  = "";       # 随机生成的包含答案的字符串
    $anLo    = array();  # 设定的答案所在的位置。
    $bit     = 3;        # 位数，在本系统中是utf-8编码，一个中文长度为3
    $anLenth = floor(strlen($giveStr)/$bit); # 答案长度,在UTF编码中，

    # 这些汉字在18个汉字中的位置
    $i = 0;
    while ( $i<$anLenth ) {
        $rd = rand( 0, $num-1 );
        if(in_array($rd,$anLo)) continue; # 保证了不重复。
        $anLo[] = $rd;
        $i++;
    }

    for( $j=0; $j<$num;$j++ ){
        if(in_array($j,$anLo)){
            $k = array_search($j,$anLo);
            $newStr .= mb_substr($giveStr,$k*$bit,$bit); #echo $newStr."<br>";

        } else {
            $rd  = rand(0,(strlen($str)-1)/$bit);
            $wd  = mb_substr($str,$rd*$bit,$bit);
            $str = str_replace($wd, '', $str);
            $newStr .= $wd;
        }
    }
    return $newStr;
}

function getlaoshiinfo($lid){
    $webdb = ConfigModel::getConfig();

    $rs= Db::name('laoshi') ->where('id',$lid)->find();
    $rs["lsimg"] = $webdb["adminurl"].'/uploads/ls/'.$rs["lsimg"];
    $rs["lspic"] = $webdb["adminurl"].'/uploads/ls/'.$rs["lspic"];
    $rs["lsfmpic"] = $webdb["adminurl"].'/uploads/ls/'.$rs["lsfmpic"];
    $rs["bg"] = $webdb["adminurl"].'/uploads/ls/T_bg.jpg';
    $rs["gif"] = $webdb["adminurl"].'/uploads/ls/20180412164046.gif';
    $rs["lszw"] = $rs["lszw"];

    $lskc = Db::name('chanpin') ->where('id',$rs["cpid"])->find();
    $rs["lskc"] = $lskc;

    return $rs;
}

function gettigerlslist($yid){
    $a = Db::name("oa_user")->where("id",$yid)->find();
    if (!empty($a["tigerlslist"])){
        $b = explode(",",$a["tigerlslist"]);
        $lsdata = array();
        foreach ($b as $v=>$k){
            $lsdata[$v] = getlaoshiinfo($k);
        }
    }
    return $lsdata;
}

function getlslistinfo($lslist=""){
    $webdb = ConfigModel::getConfig();

    if ($lslist == ""){
        $list = Db::name('laoshi')->where("id","<>",24)->order(['id','dt'=>'desc'])->select();
    }else{
        $list = Db::name('laoshi')->where('id',"in",$lslist)->order(['id','dt'=>'desc'])->select();
    }

    foreach ($list as $v=>$a){
        $list[$v]["lsimg"] = $webdb["adminurl"].'/uploads/'.Config::get("upload.laoshiimg").'/'.$a["lsimg"];
        $list[$v]["lspic"] = $webdb["adminurl"].'/uploads/'.Config::get("upload.laoshiimg").'/'.$a["lspic"];
        $list[$v]["lsfmpic"] = $webdb["adminurl"].'/uploads/'.Config::get("upload.laoshiimg").'/'.$a["lsfmpic"];
        $list[$v]["bg"] = $webdb["adminurl"].'/uploads/'.Config::get("upload.laoshiimg").'/T_bg.jpg';
        $list[$v]["gif"] = $webdb["adminurl"].'/uploads/'.Config::get("upload.laoshiimg").'/20180412164046.gif';
    }

    return $list;
}


function ismobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;

    //此条摘自TPM智能切换模板引擎，适合TPM开发
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

function getTree($data, $pId)
{
    $arr = array();
    $temp = array();
    foreach($data as $k => $v)
    {
        if($v['fid'] == $pId)
        {        //父亲找到儿子
            $temp = getTree($data, $v['id']);
            if($temp){
                $v['son'] = $temp;
            }
            $arr[] = $v;
        }
    }
    return $arr;
}

function getcommentlist($id,$tag,$s,$p){
    $emoji = new Emoji();
    if ($tag == "gus"){
        $comment = Db::name('comment')->whereTime('dateline', 'today') ->where('aid',$id)->where('tag',$tag)->limit($s,$p)->order('dateline','desc')->select();
        $re["count"] = Db::name("comment")->whereTime('dateline', 'today')->where('aid',$id)->where('tag',$tag)->count();
    }else{
        $comment = Db::name('comment') ->where('aid',$id)->where('tag',$tag)->limit($s,$p)->order('dateline','desc')->select();
        $re["count"] = Db::name("comment")->where('aid',$id)->where('tag',$tag)->count();
    }

    foreach ($comment as $b=>$c){
        $comment[$b]["dateline"] = friendlyDate($c["dateline"], 'mohu');
        $comment[$b]["content"]=$emoji->Decode($c["content"]);
    }

    $re["list"] = $comment;
    $re["zpages"] = round($re["count"] / $p);

    return $re;
}

function getzhangdie(){
    $num = 300;
    $info = array(
        "zhang"=> Db::name("gus")->where("zhang",1)->whereTime('dateline', 'today') ->count() + $num,
        "die"=> Db::name("gus")->where("die",1)->whereTime('dateline', 'today') ->count() + $num,
    );
    $info["count"] = $info["zhang"] + $info["die"];
    $info["bv"] = round(sprintf("%.2f",$info["zhang"] / $info["count"]) * 100);

    return $info;
}

function getcontentstock($content,$type=0){
    $json = Cache::get('stockbasic');
    if (!is_array($json)){
        if (date("H") >= 9){
            Cache::store('redis')->set('stockbasic',getstockapidata("stockbasic",date("Y-m-d")));
        }else{
            Cache::store('redis')->set('stockbasic',getstockapidata("stockbasic",date("Y-m-d",strtotime("-1 day"))));
        }

        $json = Cache::get('stockbasic');
    }

    $arr = array();
    foreach ($json as $v=>$k){
        if ($type == 0){
            $content = str_replace($k["symbol"],"<a class=\"goUrl\" style=\"color: blue\" href=\"javascript:void(0);\" data-url=\"/presentationdetail/".$k["symbol"]."\">".$k["symbol"]."</a>",$content);
            $content = str_replace($k["name"],"<a class=\"goUrl\" style=\"color: blue\" href=\"javascript:void(0);\" data-url=\"/presentationdetail/".$k["symbol"]."\">".$k["name"]."</a>",$content);
        }

        if(strpos($content,$k["name"]) !== false || strpos($content,$k["symbol"]) !== false){
            $arr[] = $k;
        }
    }

    if ($type == 0){
        $re["content"] = $content;
        $re["arr"] = $arr;

        return $re;
    }else{
        return $arr;
    }
}

function getstockdata($stock){
    $arr = array();
    $a = getcontentstock($stock,1);
    $b = explode(".",$a[0]["ts_code"]);
    $stock = strtolower($b[1].$stock);
    $net = new NetWork();

    $url = "http://apitest.cninfo.com.cn/v5/hq/dataItem?codelist=".$stock;
    $content=$net->curl_request($url);
    $arr=json_decode($content,true);

    return $arr[0];
}

function getzbinfo($dnamen,$name){
    $webdb = ConfigModel::getConfig();
    $net = new NetWork();

    $data = array(
        // 公共参数
        'Format' => 'JSON',
        'Version' => '2016-11-01',
        'AccessKeyId' => $webdb["accessKeyId"],
        'SignatureVersion' => '1.0',
        'SignatureMethod' => 'HMAC-SHA1',
        'SignatureNonce'=> uniqid(),
        'Timestamp' => gmdate ( 'Y-m-d\TH:i:s\Z' ),
        // 接口参数
        'Action' => 'DescribeLiveStreamsOnlineList',
        'DomainName'=>$dnamen
    );

    $data ['Signature'] = computeSignature ( $data, $webdb["accessKeySecret"] );
    // 发送请求（此处作了修改）
    $url = 'http://live.aliyuncs.com/?' . http_build_query ( $data );

    $content=$net->curl_request($url);
    $zb=json_decode($content,true);

    $re = 0;

    foreach($zb["OnlineInfo"]["LiveStreamOnlineInfo"] as $k => $v){
        if(strstr($v["PublishUrl"], $name, true)){
            $re = 1;
        }
    }

    return $re;


}

function sdbddata(){
    $webdb = ConfigModel::getConfig();
    $data = Cache::get('sdbd');
    if (empty($data)){
        $data = getstockapidata("sdbd",date( "Y-m-d"));
        Cache::store('redis')->set('sdbd',$data,60);
    }

    $arr = array();
    $brrs = array();

    foreach ($data as $v=>$a){
        preg_match_all("/src=\"\/?(.*?)\"/i",$a["nr"],$match);

        if (!empty($match[1])){
            foreach ($match[1] as $b=>$c){
                $aa = explode("/",$c);
                $imgurl = $webdb["pythonurl"]."/sdbdimg/".$aa[count($aa)-1];
                $a["nr"] = str_replace($c,$imgurl,$a["nr"]);
            }
            $bb = explode("/",$match[1][0]);
            $a["pic"] = $webdb["pythonurl"]."/sdbdimg/".$bb[count($bb)-1];
            $arr[] = $a;
        }
    }

    foreach($arr as $b=>$vbl){
        $brrs[] = strtotime($vbl["time"]);
    }

    array_multisort($brrs, SORT_DESC, $arr);

    return $arr;
}

function gettodaydate($f = "Y-m-d"){
    $w = date("w");
    $day = new Workday();
    if ($day->day_type() == 1){
        if ($w == 0){
            $to = date($f,strtotime("-2 day"));
        }else{
            $to = date($f,strtotime("-1 day"));
        }
    }elseif ($day->day_type() == 2){
        $to = getlastworkday(date( "Ymd"));
    }else{
        if ($w == 1){
            $to = date($f,strtotime("-3 day"));
        }else{
            $to = date($f,strtotime("-1 day"));
        }
    }

    return $to;
}

function getlastworkday($day){
    $d = date("Y-m-d",strtotime($day."-1 day"));
    $day = new Workday();
    if ($day->day_type($d) != 0){
        return getlastworkday($d);
    }
    return $d;
}

function getHMS($n){
    if ($n == 0) return "00:00.00";
    $second_ms = 1000;
    $minute_ms = 60*$second_ms;
    $hour_ms = 60*$minute_ms;

    $hour = intval($n/$hour_ms);
    if($hour < 10){
        $hour = "0".$hour;
    }
    $minute = intval(($n%$hour_ms)/$minute_ms);
    if($minute < 10){
        $minute = "0".$minute;
    }
    $second = sprintf("%.2f",(($n%$hour_ms)%$minute_ms)/$second_ms);
    if($second < 10){
        $second = "0".$second;
    }

    $t=$hour.":".$minute .":".$second;

    return $t;
}

function getusersip(){
    $setting = Cookie::get('setting');
    $setting = json_decode($setting,true);
    $xml = simplexml_load_string(file_get_contents(Env::get('runtime_path')."sip/".$setting["sip"].".xml"));
    $xmljson= json_encode($xml);//将对象转换个JSON
    $xmlarray=json_decode($xmljson,true);//将json转换成数组
    $sip = $xmlarray["gateway"]["param"][0]["@attributes"]["value"];
    return $sip;
}

function getcallmoney($info=""){
    $net = new NetWork();
    $setting = Cookie::get('setting');
    $setting = json_decode($setting,true);
    if ($info == ""){
        $info = $setting["sip"];
    }

    $xml = simplexml_load_string(file_get_contents(Env::get('runtime_path')."sip/".$info.".xml"));
    $xmljson= json_encode($xml);//将对象转换个JSON
    $xmlarray=json_decode($xmljson,true);//将json转换成数组
    $huaji = $xmlarray["gateway"]["param"][0]["@attributes"]["value"];
    $api = "http://47.101.192.243:1987/creckcallmoney";
    $data["e164s"] = $huaji;

    $return = $net->curl_request($api,$data);
    $return = json_decode($return,true);
    $redata = $return["data"];

    if ($return["code"] == "0000"){
        $api = "http://47.101.192.243:1987/creckcallfee";
        $callmoney = $redata["infoCustomers"][0]["money"];
        $feeRateGroup = $redata["infoCustomers"][0]["feeRateGroup"];
        $data["feeRateGroup"] = $feeRateGroup;

        $return = $net->curl_request($api,$data);
        $return = json_decode($return,true);
        $redata = $return["data"];
        $callfee = $redata["infoFeeRates"][0]["fee"];

        $arr = array(
            "callmoney"=>sprintf("%.2f",substr(sprintf("%.3f", $callmoney), 0, -2)),
        "feeRateGroup"=>$feeRateGroup,
            "callfee"=>$callfee,
        );

        return $arr;
    }else{
        return $return["msg"];
    }
}

function reloadsipxml($gwt=""){
    $net = new NetWork();
    $api = "http://47.101.192.243:1987/reloadsipxml";
    if (!empty($gwt)){
        $data["gwt"] = $gwt;
        $return = $net->curl_request($api,$data);
    }else{
        $return = $net->curl_request($api);
    }

    $return = json_decode($return,true);
    $redata = $return["data"];

    return $redata;
}

function getseatreginfo($id){
    if (file_exists(Env::get('runtime_path')."seat/".$id.".xml")){
        $xml = simplexml_load_string(file_get_contents(Env::get('runtime_path')."seat/".$id.".xml"));
        $xmljson= json_encode($xml);//将对象转换个JSON
        $xmlarray=json_decode($xmljson,true);//将json转换成数组
        $sip = array(
            "impi"=>$xmlarray['user']["@attributes"]['id'],
            "impu"=>"sip:".$xmlarray['user']["@attributes"]['id']."@ai.haishunsh.com",
            "password"=>$xmlarray['user']['params']['param']["@attributes"]['value'],
        );
    }else{
        $sip = [];
    }

    return $sip;
}

//拆分字符串
function split_str($str) {
    preg_match_all("/./u", $str, $arr);
    return $arr[0];
}

//相似度检测
function similar_text_cn($str1, $str2) {
    $arr_1 = array_unique(split_str($str1));
    $arr_2 = array_unique(split_str($str2));
    $similarity = count($arr_2) - count(array_diff($arr_2, $arr_1));

    return $similarity;
}

