<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
require_once($modules_root."edms/class/UserHome.class.php");
require_once($modules_root."edms/class/ValidationHome.class.php");
class RightsHome extends UserHome {
    private $departmentTree=array();

    /**
     * Сохранение/изменение замещения
     * @param $items - массив параметров
     * @return array
     * @throws Exception
     */
    public function save($items){
        $check=new ValidationHome();
        $errors=$check->checkRights($items);
        if($errors['errors_none']){
            if($items['id']==""||$items['id']==null){
                $items['id_alternate']=UserHome::getUserFromExecution($items['id_alternate']);
                $this->save->saveAssignment($items);
            }
            else
                $this->save->updateAssignment($items);
        }
        return $errors;
    }

    public function canShow($doc,$userId){
        //если поручение мне
        //если поручение от меня
        //если я наблюдатель
        //если я заместитель человека, кто является наблюдателем, кому или от кого поручение
    }

    /**
     * Получение списка доступных замещаемых пользователей (для select)
     * @param $user - ИД пользователя
     * @return array
     */
    public function getAssignment($user){
        $result=array();
        $sql="SELECT u.id, CONCAT_WS(' ', u.lastname, u.firstname, u.secondname) as name from user u where u.id=".$user." AND u.is_deleted=0";
        array_push($result,['id'=>-1, 'name'=>'Я'],$this->db_esia->getOneData($sql, ['id','name']));
        $sql="SELECT assignment.id_boss as id FROM assignment
              WHERE id_alternate=".$user." AND is_deleted=0 AND ((".time().">=date_start AND ".time()."<=date_end) OR (type=1))";
        $temp=$this->db_edms->getListData($sql, ['id','name']);
        if(count($temp)>0&&$temp[0]){
            array_push($result,['id'=>-1, 'name'=>'Замещения']);
            foreach ($temp as $res)
                array_push($result, array('id'=>$res['id'], 'name'=>UserHome::getFullFIO($res['id'])));
        }
        return $result;
    }

    /**
     * Получение параметров одного замещения по ИД
     * @param $id - ИД замещения
     * @return mixed
     * @throws Exception
     */
    public static function getOneAssignment($id){
        $sql="SELECT * FROM assignment WHERE id=".$id;
        $result=$GLOBALS["db10"]->getOneData($sql, UserHome:: $assignment);
        if($result['type']==0){
            if($result['date_end']!=''&&$result['date_end']!=0)
                $result['date_end'] = UtilHome::getDateFormat($result['date_end'],"Y-m-d");
            if($result['date_start']!=''&&$result['date_start']!=0)
                $result['date_start']=UtilHome::getDateFromTimestamp($result['date_start']);
            unset($result['type']);
        }else
            unset($result['date_start'],$result['date_end']);
        return $result;
    }

    public function getDepartmentTree($userId,$inptText) {
        if(!$this->isRectorat($userId)||$inptText==null||strlen($inptText)<=3){
            $result=UserHome::getUserDepartment($userId);
            $count=1;
            foreach ($result as $key=>$value) { //Обходим массив
                $this->departmentTree[$value['id_parent']][]=$value;
                $this->right($value,$count,$inptText);
            }
        }else{
            $result=$this->getDepartment(31391769);
            $this->right($result,1,$inptText);
        }
        return $this->departmentTree;
    }

    /**
     * Проверка, входит ли пользователь в ректорат
     * @param $user - ИД пользователя
     * @return bool
     */
    public function isRectorat($user){
        $sql="SELECT is_superuser FROM user WHERE id=".$user;
        $result=$this->db_esia->getOneData($sql, ['is_superuser'])['is_superuser'];
        return $result==1?true:false;
    }

    /**
     * Поиск пользователей по введенным символам
     * @param $text - Введенный текст
     * @return mixed
     */
    public function searchUsers($text){
        $inptText=$str = preg_replace('/\s+/', ' ', $text);
        $sql = "SELECT `ue`.`id` AS `id`,  CONCAT_WS(' ',`lastname`,`firstname`,`secondname`, `dep`.`name`, `up`.`name`) AS `name`, `ue`.`id_department` as `id_dep` FROM `user` ";
        $sql .= "LEFT JOIN `user_execution` ue ON `user`.`id`=`ue`.`id_user` ";
        $sql .= "LEFT JOIN `department` `dep` ON `ue`.`id_department`=`dep`.`id` ";
        $sql .= " JOIN user_position up ON up.id=ue.id_position ";
        $sql .= "WHERE user.is_deleted=0 ";

        $sql .= " AND (CONCAT_WS(' ',`lastname`,`firstname`,`secondname`) like '%" . $text . "%' ";
        $sql .= " OR CONCAT_WS(' ',`lastname`,`secondname`,`firstname`) like '%" . $text . "%' ";
        $sql .= " OR CONCAT_WS(' ',`firstname`,`lastname`,`secondname`) like '%" . $text . "%' ";
        $sql .= " OR CONCAT_WS(' ',`firstname`,`secondname`,`lastname`) like '%" . $text . "%' ";
        $sql .= " OR CONCAT_WS(' ',`secondname`,`firstname`,`lastname`) like '%" . $text . "%' ";
        $sql .= " OR CONCAT_WS(' ',`secondname`,`lastname`,`firstname`) like '%" . $text . "%') ";

        $sql .= " AND ue.is_deleted=0 AND (ue.date_end=0 OR ue.date_end IS NULL OR ue.date_end>'".date('U')."') AND ue.id_category>6";
        $sql .= " ORDER BY `ue`.`id_department`, `lastname`,`firstname` limit 20";

        $items = $this->db_esia->getListData($sql, array('id','name','id_dep'));
        return $items;
    }

    private function right($value,$count,$text){
        if($count<3||$text!=null){
            $result=$this->getChildDepartments($value['id']);
            $count++;
            foreach ($result as $key=>$value) { //Обходим массив
                $this->departmentTree[$value['id_parent']][]=$value;
                $this->right($value,$count,$text);
            }
        }
    }

    /**
     * Проверка является ли теущий пользователь заместителем конкретного пользователя
     * @param $boss - ИД замещаеиого пользователя
     * @param $alternate - ИД заместителя
     * @return bool
     */
    public static function isAssignment($boss,$alternate){
        $sql="SELECT COUNT(*) cn FROM assignment WHERE id_boss=".$boss." AND id_alternate=".$alternate." AND (".time()."<=date_end OR mode=1) AND is_deleted=1";
        if($GLOBALS ["db10"]->getOneData($sql,['cn'])['cn']!=0)
            return true;
        else
            return false;
    }

    /**
     * Для дальнейшего разрешения выставлять номер и дату документа
     * @return bool
     */
    public function canSet(){
        return false;
    }

    /**
     * Получение списка пользователей, которых замещает пользователь
     * @param $user - ИД пользователя
     * @return mixed
     */
    public static function assignmentToMe($user){
        $sql="SELECT u.id as id_user, a.id, CONCAT_WS(' ', u.lastname, u.firstname, u.secondname) as user, 
            if(a.date_start is NULL,'-',	from_unixtime(a.date_start,'%d.%m.%Y')) as start, 
            if(a.date_end is NULL,'-',	from_unixtime(a.date_end,'%d.%m.%Y')) as end,
            if(a.type=1,'Постоянное','Временное') as type,IF(a.is_deleted,'Удалено',if( a.date_end<".time()." AND a.type=0, 'Действие истекло',IF((a.date_start>=".time()." and a.type=0) or a.type=1,'Действует','Не вступило в силу'))) as status FROM edms.assignment a 
            LEFT JOIN esia.user u ON u.id=a.id_boss
            LEFT JOIN esia.user_execution ue ON ue.id_user=u.id AND ue.id_category>0
            WHERE a.id_alternate=".$user."  GROUP BY a.id";
        return $GLOBALS["db2"]->getListData($sql,['id_user','id','user','start','end','type','status']);
    }

    /**
     * Получение списака пользователей, котрый замещают пользователя
     * @param $user - ИД пользователя
     * @return mixed
     */
    public static function assignmentFromMe($user){
        $sql="SELECT u.id as id_user, a.id, CONCAT_WS(' ', u.lastname, u.firstname, u.secondname) as user, 
            if(a.date_start is NULL,'-',	from_unixtime(a.date_start,'%d.%m.%Y')) as start, 
            if(a.date_end is NULL,'-',	from_unixtime(a.date_end,'%d.%m.%Y')) as end,
            if(a.type=1,'Постоянное','Временное') as type,IF(a.is_deleted,'Удалено',if( a.date_end<".time()." AND a.type=0, 'Действие истекло',IF((a.date_start<=".time()." and a.type=0) or a.type=1,'Действует','Не вступило в силу'))) as status FROM edms.assignment a 
            LEFT JOIN esia.user u ON u.id=a.id_alternate
            LEFT JOIN esia.user_execution ue ON ue.id_user=u.id AND ue.id_category>0
            WHERE a.id_boss=".$user."  GROUP BY a.id";
        return $GLOBALS["db2"]->getListData($sql, ['id_user','id','user','start','end','type', 'status']);
    }
}