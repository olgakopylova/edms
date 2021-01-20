<?php
require_once($modules_root."edms/class/PrintHTMLHome.class.php");
require_once($modules_root."edms/class/RightsHome.class.php");
require_once($modules_root."edms/class/SaveHome.class.php");
class UserHome{
    public static $assignment=array(
        'id',
        'id_boss',
        'id_alternate',
        'date_start',
        'date_end',
        'type',
        'is_deleted'
    );
    protected $db_edms;
    protected $db_esia;
    protected $save;
    protected $templateHome;
    protected $modules_root;

    public function __construct($request = null) {
        $this->templateHome=$GLOBALS['templateHome'];
        $this->modules_root=$GLOBALS['modules_root'];
        $this->db_edms = $GLOBALS ["db10"];
        $this->db_esia=$GLOBALS["db2"];
        $this->save = new SaveHome();
    }

    /**
     * Получение ИД пользователя по ИД исполнения
     * @param $execution - ИД исполнения
     * @return mixed
     */
    public static function getUserFromExecution($execution){
        $sql="SELECT id_user FROM user_execution WHERE id=".$execution;
        return $GLOBALS["db2"]->getOneData($sql, ['id_user'])['id_user'];
    }

    /**
     * Получение подразделения пользователя
     * @param $user - ИД пользователя
     * @return mixed
     */
    public static function getUserDepartment($user){
        $sql = "SELECT id,id_parent, name, level FROM department where id in (SELECT id_department FROM user_execution 
                WHERE user_execution.id_user=".$user." AND (user_execution.date_end=0 OR user_execution.date_end IS NULL 
                OR user_execution.date_end>'".date('U')."') AND user_execution.id_category>6) and department.is_deleted=0";
        $items=$GLOBALS["db2"]->getListData($sql, ['id','id_parent','name','level']);
        return  $items;
    }

    /**
     * Получение должности и подразделения пользователя
     * @param $user - ИД пользователя
     * @return string
     */
    public static function getPosition($user){
       if($user!=null&&$user!=""){
           $sql="SELECT CONCAT_WS(', ', up.name,d.name) as name FROM user_execution ue
              JOIN user_position up ON up.id=ue.id_position 
              JOIN department d ON d.id=ue.id_department 
              where ue.id_user=".$user." AND ue.is_deleted=0 AND (ue.date_end=0 OR ue.date_end IS NULL OR ue.date_end>'".date('U')."') ";
           return $GLOBALS["db2"]->getOneData($sql, ['name'])['name'];
       }else
           return "";
    }

    /**
     * Получение должности пользователя
     * @param $user - ИД пользователя
     * @return string
     */
    public static function getShortPosition($user){
        if($user!=null&&$user!=""){
            $sql="SELECT up.name as name FROM user_execution ue
              JOIN user_position up ON up.id=ue.id_position 
              where ue.id_user=".$user." AND ue.is_deleted=0 AND (ue.date_end=0 OR ue.date_end IS NULL OR ue.date_end>'".date('U')."') ";
            return $GLOBALS["db2"]->getOneData($sql, ['name'])['name'];
        }else
            return "";
    }

    /**
     * Получение дочерних подразделений текущего подразделения
     * @param $id - ИД подразделения
     * @return mixed
     */
    public function getChildDepartments($id){
        $sql="SELECT id,id_parent, name, level FROM department where id_parent=".$id." AND is_deleted=0";
        return $this->db_esia->getListData($sql, ['id','id_parent','name','level']);
    }

    /**
     * Получение подразделения по ИД
     * @param $id - ИД подразделения
     * @return mixed
     */
    public static function getDepartment($id){
        $sql="SELECT id,id_parent, name, level FROM department where id=".$id." AND is_deleted=0";
        return $GLOBALS["db2"]->getOneData($sql, ['id','id_parent','name','level']);
    }

    /**
     * Получение всех пользователей, относящихся к текущему подраздалению
     * @param $id - ИД подразделения
     * @return mixed
     */
    public static function getUsersFromDepartment($id){
        //if($userId!=""&&$userId!=null)
            //$text="AND `ue`.id_user<>".$userId;
        $sql = "SELECT `ue`.`id` AS `id`, `user`.`code`, CONCAT_WS(' ',`lastname`,`firstname`,`secondname`, `dep`.`name`, `up`.`name`) AS `name` FROM `user_execution` ue ";
        $sql .= "LEFT JOIN `user` ON `user`.`id`=`ue`.`id_user` ";
        $sql .= "LEFT JOIN `department` `dep` ON `ue`.`id_department`=`dep`.`id` ";
        $sql .= " JOIN user_position up ON up.id=ue.id_position ";
        $sql .= "WHERE `user`.is_deleted=0 AND `ue`.id_department=".$id." AND ue.id_category>6 AND `ue`.is_deleted=0 AND (ue.date_end=0 OR ue.date_end IS NULL OR ue.date_end>'".date('U')."') ";
        return $GLOBALS["db2"]->getListData($sql, ['id','name']);
    }

    public function getListUsers($ids){
        $part1="(SELECT ue.id, CONCAT_WS(', ',CONCAT_WS(' ', u.lastname, u.firstname, u.secondname),d.name, up.name) as name from user_execution ue
              LEFT JOIN user u ON u.id=ue.id_user
              LEFT JOIN user_position up ON up.id=ue.id_position 
              LEFT JOIN department d ON d.id=ue.id_department 
              where ue.id=";
        $part2=" AND ue.id_category>6 AND u.is_deleted=0 AND (ue.date_end=0 OR ue.date_end IS NULL OR ue.date_end>'".date('U')."') GROUP BY u.id) ";
        $ids=explode(',',$ids);
        $sql="";
        foreach ($ids as $key=>$id){
            if($key!=count($ids)-1)
                $sql.=$part1.$id.$part2." UNION ";
            else
                $sql.=$part1.$id.$part2;
        }
        $users=$GLOBALS["db2"]->getListData($sql, ['id','name']);
        $text='';
        if(count($users)!=0)
            foreach($users as $user){
                $text.="<option style='padding-left: 30px' value='".$user['id']."' selected>".$user['name']."</option>";
            }
        return $text;
    }

    /**
     * Получение полого ФИО пользователя
     * @param $user - ИД пользователя
     * @return string
     */
    public static function getFullFIO($user){
        if($user!=null&&$user!=""){
            $sql="SELECT CONCAT_WS(' ', u.lastname, u.firstname, u.secondname) AS name FROM user u
              WHERE u.id=".$user;
            return $GLOBALS["db2"]->getOneData($sql, ['name'])['name'];
        }else
            return "";
    }

    /**
     * Получение фамилии с инициалами
     * @param $user - ИД пользователя
     * @return string
     */
    public static function getFIO($user){
        if($user!=null&&$user!="") {
            $sql = "SELECT CONCAT_WS(' ', u.lastname, CONCAT_WS('',SUBSTRING(u.firstname,1,1),'.'), CONCAT_WS('',SUBSTRING(u.secondname,1,1),'.')) 
                    as name from user u where u.id=" . $user;
            return $GLOBALS["db2"]->getOneData($sql, ['name'])['name'];
        }else
            return "";
    }

    /**
     * Получение ФИО, подразделения, должности пользователя
     * @param $user - ИД пользователя
     * @return mixed
     */
    public static function getUser($user){
        $sql="SELECT u.id, CONCAT_WS(', ',CONCAT_WS(' ', u.lastname, u.firstname, u.secondname),d.name, up.name) as name from user u
              LEFT JOIN user_execution ue ON ue.id_user=u.id
              LEFT JOIN user_position up ON up.id=ue.id_position 
              LEFT JOIN department d ON d.id=ue.id_department 
              where u.id=".$user." AND u.is_deleted=0 AND ue.id_category>6  AND (ue.date_end=0 OR ue.date_end IS NULL OR ue.date_end>'".date('U')."')";
        return $GLOBALS["db2"]->getListData($sql, ['id','name']);
    }
}