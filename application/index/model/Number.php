<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Number extends Model
{
    protected $connection = "db_autodialer";
    protected $name = 'Number';
    protected $str = "CREATE TABLE `{name}` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`number` VARCHAR(20) NOT NULL,
`state` INT(11) NULL DEFAULT NULL,
`description` VARCHAR(255) NULL DEFAULT NULL,
`recycle` INT(11) NULL DEFAULT NULL,
`callid` VARCHAR(255) NULL DEFAULT NULL,
`calleridnumber` VARCHAR(255) NULL DEFAULT NULL,
`calldate` DATETIME NULL DEFAULT NULL,
`answerdate` DATETIME NULL DEFAULT NULL,
`hangupdate` DATETIME NULL DEFAULT NULL,
`bill` INT(11) NULL DEFAULT NULL,
`duration` INT(11) NULL DEFAULT NULL,
`hangupcause` VARCHAR(255) NULL DEFAULT NULL,
`bridge_callid` VARCHAR(255) NULL DEFAULT NULL,
`bridge_number` VARCHAR(20) NULL DEFAULT NULL,
`bridge_calldate` DATETIME NULL DEFAULT NULL,
`bridge_answerdate` DATETIME NULL DEFAULT NULL,
`recordfile` VARCHAR(255) NULL DEFAULT NULL,
`status` VARCHAR(255) NULL DEFAULT NULL,
`call_notify_url` varchar(1024) DEFAULT NULL,
`call_notify_type` int(11) DEFAULT NULL,
`gender` INT(8) NULL DEFAULT NULL,
PRIMARY KEY (`id`)
)";

    protected $dstr = "DROP TABLE `{name}`";

    public function setid($aid,$zid)
    {
        $this->name = $this->name."_".$aid."_".$zid;
    }

    public function Createtable(){
        $sql = str_replace("{name}","autodialer_".$this->name,$this->str);
        Number::execute($sql);
    }

    public function Deltable(){
        $sql = str_replace("{name}","autodialer_".$this->name,$this->dstr);
        Number::execute($sql);
    }
}