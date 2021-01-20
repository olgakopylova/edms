<?php

use http\Client\Curl\User;

require_once($modules_root."edms/class/RightsHome.class.php");
require_once($modules_root."edms/class/UserHome.class.php");
require_once("core/lib/vendor/phpoffice/phpword/bootstrap.php");
require_once($modules_root."edms/class/UtilHome.class.php");
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\PhpWord;

class DocumentationHome
{
    protected $db_edms;
    protected $db_esia;
    protected $templateHome;
    protected $modules_root;
    public static $docsDir="files/lk/docs/documentation/";
    protected static $sign="files/lk/docs/documentation/";
    protected $dateFormat="d.m.Y";
    protected $doc=array(
        'id',
        'is_deleted'
    );
    protected $docs_flow=array(
        'id',
        'id_parent',
        'id_type',
        'number',
        'id_owner',
        'id_operator',
        'id_user',
        'id_watcher',
        'id_text',
        'code',
        'date_create',
        'date',
        'date_end',
        'date_end_fact',
        'date_show',
        'type',
        'is_consistent',
        'is_changed1',
        'is_changed2',
        'is_changed3',
        'is_changed4',
        'is_tracked',
        'is_closed',
        'is_signed',
        'hash',
        'is_responsible',
        'is_deleted'
    );
    protected $text=array(
        'id',
        'text',
        'name'
    );
    protected $files=array(
        'id',
        'id_text',
        'name',
        'weight',
        'is_deleted',
    );
    protected $now_date;

    public function __construct() {
        $this->templateHome=$GLOBALS['templateHome'];
        $this->modules_root=$GLOBALS['modules_root'];
        $this->db_edms = $GLOBALS ["db10"];
        $this->db_esia=$GLOBALS["db2"];
        $this->now_date=new DateTime("now");
        $this->now_date = $this->now_date->format("Y-m-d");
    }

    /**
     * Получение глобального типа сущности (Поручение, подписание, документ и тд)
     * @param $id - ИД задачи
     * @return mixed - число(тип сущности)
     */
    public function getDocType($id){
        $sql="SELECT id_type FROM docs_flow WHERE id=".$id;
        return $this->db_edms->getOneData($sql,['id_type'])['id_type'];
    }

    /**
     * Это запросы работы с сущностью или нет (завершение, продление)
     * @param $id - ИД задачи
     * @return bool - true-работа с сущностью, false-нет(сам запрос поручения и тд)
     */
    public function isFlow($id){
        $sql="SELECT type FROM docs_flow WHERE id=".$id;
        $type=$this->db_edms->getOneData($sql,['type'])['type'];
        return $type!=0&&$type!=4&&$type!=10;
    }

    /**
     * Получение локального типа сущности (задача на одного человека, на нскольких, запрос завершения и тд)
     * @param $id - ИД задачи
     * @return mixed - число(тип сущности)
     */
    public function getFlowType($id){
        $sql="SELECT type FROM docs_flow WHERE id=".$id;
        $type=$this->db_edms->getOneData($sql,['type'])['type'];
        return $type;
    }

    /**
     * Является ли тип сущности документом
     * @param $id - ИД типа сущности
     * @return mixed - 0-да, 1-нет
     */
    public function getShowType($id){
        $sql="SELECT is_show from docs_types WHERE id=".$id;
        return $this->db_edms->getOneData($sql,['is_show'])['is_show'];
    }

    /**
     * Информация о типе сущности
     * @param $id - ИД типа сущности
     * @return mixed - массив(ИД, название)
     */
    public function getOneDocType($id){
        $sql="SELECT * FROM docs_types WHERE id=".$id;
        return $this->db_edms->getListData($sql,['id','name']);
    }

    /**
     * @param $textId
     * @param $code
     * @return mixed
     */
    public function getDoc($textId, $code){
        $sql = "SELECT id FROM docs_flow Where id_text=" . $textId . " AND code=" . $code;
        return $this->db_edms->getListData($sql, ['id']);
    }

    /**
     * Получение списка документов
     * @return mixed
     */
    public function getAllTypes(){
        $sql="SELECT * FROM docs_types WHERE is_deleted=0 AND is_show=0";
        return $this->db_edms->getListData($sql,['id','name']);
    }

    /**
     * Получение номера потока работ
     * @param $id - ИД задачи
     * @return mixed - число(код)
     */
    public function getCode($id){
        $sql="SELECT code FROM docs_flow where id=".$id;
        return $this->db_edms->getOneData($sql,['code'])['code'];
    }

    /**
     * Получение списка всех внесенных документов
     * @param $user - ИД текущего пользователя
     * @param $filters - массив фильтров
     * @return mixed
     */
    protected function getDocumentList($user,$filters){
        $text="";
        if(!empty($filters['date_start']))
            $text.=" AND df.date>=".strtotime($filters['date_start']);
        if(!empty($filters['date_end']))
            $text.=" AND df.date<=".strtotime($filters['date_end']);
        $text.=!empty($filters['id_type'])?" AND df.id_type=".$filters['id_type']:" AND dt.is_show=0";
        $sql="SELECT df.id, t.name, t.text, from_unixtime(df.date,'%d.%m.%Y') AS date_first, df.is_signed FROM docs_flow df 
              LEFT JOIN text t ON t.id=df.id_text
              LEFT JOIN docs_types dt ON df.id_type=dt.id WHERE df.is_deleted=0 AND (SELECT COUNT(*) FROM docs_flow WHERE 
              (docs_flow.id_owner=".$user." OR docs_flow.id_user=".$user." OR docs_flow.id_owner=".$user." OR docs_flow.id_operator=".$user.") 
              AND docs_flow.code=df.code AND docs_flow.is_deleted=0)>0".$text;
        $result=$this->db_edms->getListData($sql,['id','name','text','date','is_signed']);
        return $result;
    }

    /**
     * Получение всех задач по коду и приведение массива к удобному виду для посмтроения дерева
     * @param $code - Код потока задач
     * @return mixed
     */
    protected function _getTree($code) {
        $sql = "SELECT docs_flow.id,docs_flow.id_type, docs_flow.id_parent, docs_flow.id_owner, docs_flow.id_operator, docs_flow.id_user, docs_flow.id_watcher, 
                docs_flow.code, text.id as id_text,text.text, text.name,docs_flow.type, docs_flow.date_show, docs_flow.date_create,docs_flow.date_end_fact, 
                docs_flow.date,docs_flow.date_end, docs_flow.is_closed, docs_flow.is_changed1, docs_flow.is_changed2, docs_flow.is_changed3, 
                docs_flow.is_changed4, docs_flow.is_signed, docs_flow.is_consistent, docs_flow.is_responsible FROM docs_flow
                JOIN text ON text.id=docs_flow.id_text WHERE docs_flow.code=".$code." AND docs_flow.is_deleted=0 ORDER BY docs_flow.date_create ASC";
        $result = $this->db_edms->getListData($sql, ['id','id_parent','id_type','id_owner','id_operator','id_user', 'id_watcher','code','id_text','text','name','type','date_show','date_create','date_end','date_end_fact', 'date','status','is_closed','is_changed1','is_changed2','is_changed3','is_changed4','is_signed','is_consistent','is_responsible']);
        foreach ($result as $value)
            $return[$value['id_parent']][]=$value;
        return $return;
    }

    /**
     * Завершил ли выполнение ответственный исполнитель
     * @param $id - ИД задачи
     * @return bool
     */
    public function responsibleCompleted($id){
        $sql="SELECT is_closed FROM docs_flow WHERE id_parent=".$id." AND is_responsible=1 AND is_deleted=0";
        $result=$this->db_edms->getOneData($sql,['is_closed'])['is_closed'];
        return $result==1?true:false;
    }

    /**
     * Подсчет количества задач по категориям
     * @param $userId - ИД текущего пользователя
     * @return array
     */
    public function getCount($user){
        $result=array();
        $text1="SELECT count(DISTINCT(docs_flow.code)) cn FROM docs_flow
              JOIN text ON text.id=docs_flow.id_text
              JOIN esia.user own ON own.id=docs_flow.id_owner
              JOIN esia.user us ON us.id=docs_flow.id_user
              join docs_types dt ON dt.id=docs_flow.id_type ";
        $text2=" AND dt.is_show=1 AND docs_flow.is_deleted=0";
        $main=" (docs_flow.id_owner=".$user." OR docs_flow.id_operator=".$user." OR  docs_flow.id_user=".$user." 
                    OR EXISTS(SELECT fw.id FROM flow_watchers fw WHERE fw.is_deleted=0 AND fw.id_flow=docs_flow.id AND fw.id_user=".$user."))";
        $sql=" WHERE (((docs_flow.id_user=".$user." OR EXISTS(SELECT fw.id FROM flow_watchers fw WHERE fw.is_deleted=0 AND fw.id_flow=docs_flow.id AND fw.id_user=".$user.")) 
                    AND docs_flow.date_show IS NULL AND docs_flow.is_closed=0 and docs_flow.id_type=1) OR (docs_flow.id_user=".$user." AND ((SELECT COUNT(*) FROM docs_flow d WHERE d.id_parent=docs_flow.id AND d.id_owner=docs_flow.id_user AND d.is_deleted=0)=0 OR 
                    ((SELECT COUNT(*) FROM docs_flow d WHERE d.id_parent=docs_flow.id AND d.id_owner=docs_flow.id_user and d.is_closed=1 AND d.is_deleted=0)>0) AND 
                    (SELECT COUNT(*) FROM docs_flow d WHERE d.id_parent=docs_flow.id AND d.id_owner=docs_flow.id_user AND d.is_deleted=0)=0) AND docs_flow.is_closed=0) 
                    OR (docs_flow.id_user=".$user." AND docs_flow.is_changed2=1) OR (EXISTS(SELECT fw.id FROM flow_watchers fw WHERE fw.is_deleted=0 AND fw.id_flow=docs_flow.id AND fw.id_user=".$user.") AND docs_flow.is_changed3=1) 
                    OR (docs_flow.id_owner=".$user." AND docs_flow.is_changed1=1) OR (docs_flow.id_operator=".$user." AND docs_flow.is_changed4=1)) ";
        $result['new']=$this->db_edms->getOneData($text1.$sql.$text2,['cn'])['cn'];
        $sql=" WHERE ".$main;
        $result['all']=$this->db_edms->getOneData($text1.$sql.$text2,['cn'])['cn'];
        $sql=" WHERE ((".$main." AND docs_flow.is_closed=0) OR 
                    (docs_flow.id_user=".$user." AND (SELECT COUNT(*) FROM docs_flow d WHERE d.id_parent=docs_flow.id AND d.id_owner=docs_flow.id_user AND d.is_closed=0 AND d.is_deleted=0))>0) ";
        $result['inwork']=$this->db_edms->getOneData($text1.$sql.$text2,['cn'])['cn'];
        $sql=" WHERE (".$main." AND docs_flow.type=0 AND docs_flow.date_end<".time()." AND docs_flow.is_closed=0) ";
        $result['past']=$this->db_edms->getOneData($text1.$sql.$text2,['cn'])['cn'];
        $sql=" WHERE (".$main." AND ((SELECT COUNT(df.id) FROM docs_flow df WHERE df.code=docs_flow.code AND df.is_closed=1 AND df.type in (0,10) AND df.is_deleted=0)>0)) ";
        $result['done']=$this->db_edms->getOneData($text1.$sql.$text2,['cn'])['cn'];
        foreach ($result as $key=>$res)
            if($res==0) unset($result[$key]);
        return $result;
    }

    /**
     * Краткая информация и задаче для предпросмотра
     * @param $id - ИД задачи
     * @return mixed
     * @throws Exception
     */
    public function getPreview($id){
        $sql="SELECT text.text, docs_flow.date FROM docs_flow left join text ON text.id=docs_flow.id_text WHERE docs_flow.id=".$id;
        $result=$this->db_edms->getOneData($sql,['text','date']);
        if($result['text']==""||$result['text']==null) unset($result['text']);
        $result['date_first_full'] = $result['date'];
        $result['date_first'] = UtilHome::getDateFormat($result['date'], "Y-m-d");
        return $result;
    }

    /**
     * Является ли данная задачия документом на подписании
     * @param $id - ИД задачи
     * @return bool - true-да, false-нет
     */
    public function isActiveSigning($id){
        $sql="SELECT COUNT(*) cn FROM docs_flow WHERE docs_flow.id_parent=".$id." AND id_type=5 and is_closed=0 and is_deleted=0";
        $result=$this->db_edms->getOneData($sql,['cn'])['cn'];
        return $result>0?true:false;
    }

    /**
     * Получение ФИО и должножсти главного ответственного за подписание
     * @param $id - ИД задачи
     * @return string
     */
    public function getMainSignTitle($id){
        $sql="SELECT id from docs_flow Where id_parent=".$id." AND id_type=5 AND is_deleted=0";
        $ids= $this->db_edms->getListData($sql,['id']);
        $user="";
        if(isset($ids[0]['id']))
            foreach ($ids as $key=>$value){
                $sql="SELECT id_user from docs_flow Where id_parent=".$value['id']." AND is_responsible=1 and id_type=5 AND is_deleted=0 and is_signed=1 ORDER BY date_end";
                $uid=$this->db_edms->getOneData($sql,['id_user'])['id_user'];
                if($uid!=null||$uid!=""){
                    $user.=UserHome::getFIO($uid).' ('.UserHome::getShortPosition($uid).")";
                    if($key<count($ids)-1)
                        $user.=", ";
                }
            }
        return $user;
    }

    /**
     * Получение ФИО и должножсти остальных подписавших
     * @param $id - ИД задачи
     * @return string
     */
    public function getOtherSign($id){
        $sql="SELECT id from docs_flow Where id_parent=".$id." AND id_type=5 AND is_deleted=0";
        $ids= $this->db_edms->getListData($sql,['id']);
        $user="";
        if(isset($ids[0]['id']))
            foreach ($ids as $key=>$value){
                $sql="SELECT id, id_user,code,id_text, is_signed from docs_flow Where id_parent=".$value['id']." AND is_responsible=0 and id_type=5 AND is_deleted=0 and type=4 AND is_closed=1 ORDER BY is_signed DESC ";
                $uids=$this->db_edms->getListData($sql,['id','id_user','code','id_text','is_signed']);
                foreach ($uids as $key2=>$uid)
                    $uids[$key2]['id_watcher']=DocumentationHome::getWatchers($uids[$key2]['id']);
                    if($uid['id_user']!=null||$uid['id_user']!="") {
                        $status=$uid['is_signed']==1?"ПОДПИСАНО":"ОТКЛОНЕНО";
                        $user .= UserHome::getFIO($uid['id_user']) . ' (' . UserHome::getShortPosition($uid['id_user']).") ".$status;
                        if($key2<count($uids)-1)
                            $user.="<br> ";
                    }
            }
        return $user;
    }

    /**
     * Получение списка всех подписавших(или отклонив) документ
     * @param $id
     * @return mixed
     */
    public static function getMainSignForFile($id){
        $sql="SELECT id from docs_flow Where id_parent=".$id." AND id_type=5 AND is_deleted=0";
        $ids= $GLOBALS ["db10"]->getListData($sql,['id']);
        $user['other']=array();
        $user['main']=array();
        if(isset($ids[0]['id']))
            foreach ($ids as $key=>$value){
                $sql="SELECT id_user from docs_flow Where id_parent=".$value['id']." AND is_responsible=1 and id_type=5 AND is_deleted=0 and is_signed=1 ORDER BY date_end ASC";
                $uids=$GLOBALS ["db10"]->getListData($sql,['id_user']);
                foreach ($uids as $uid){
                    if($uid['id_user']!=null||$uid['id_user']!="") {
                        $temp=UserHome::getFIO($uid['id_user']) . ' (' . UserHome::getShortPosition($uid['id_user']).") ";
                        array_push($user['main'],$temp);
                    }
                }
            }
        if(isset($ids[0]['id']))
            foreach ($ids as $key=>$value){
                $sql="SELECT id, id_user,code,id_text, is_signed from docs_flow Where id_parent=".$value['id']." AND is_responsible=0 and id_type=5 AND is_deleted=0 and type=4 AND is_closed=1 ORDER BY is_signed DESC ";
                $uids=$GLOBALS ["db10"]->getListData($sql,['id','id_user','code','id_text','is_signed']);
                foreach ($uids as &$uid){
                    $uid['id_watcher']=DocumentationHome::getWatchers($uid['id']);
                    if($uid['id_user']!=null||$uid['id_user']!="") {
                        $status=$uid['is_signed']==1?"ПОДПИСАНО":"ОТКЛОНЕНО";
                        $temp=UserHome::getFIO($uid['id_user']) . ' (' . UserHome::getShortPosition($uid['id_user']).") ".$status;
                        array_push($user['other'],$temp);
                    }
                }
            }
        return $user;
    }

    /**
     * Проверка является данная задача запросом подписания у ответственного пользователя
     * @param $userId - ИД пользователя
     * @param $id - ИД задачи
     * @return bool
     */
    public function isMainSign($userId,$id){
        $sql="SELECT id from docs_flow Where id_parent=".$id." AND id_type=5 AND is_deleted=0";
        $ids= $this->db_edms->getListData($sql,['id']);
        if(isset($ids[0]['id']))
            foreach ($ids as $key=>$value){
                $sql="SELECT id_user from docs_flow Where id_parent=".$value['id']." AND is_responsible=1 and id_type=5 AND is_deleted=0 and is_signed=1";
                $user=$this->db_edms->getOneData($sql,['id_user'])['id_user'];
                if($user!=null||$user!="")
                    if($user==$userId||RightsHome::isAssignment($user,$userId))
                        return true;
            }
        return false;
    }

    /**
     * Поручение темы задачи
     * @param $id - ИД задачи
     * @return mixed
     */
    public function getName($id){
        $sql="SELECT df.id, t.name FROM docs_flow df 
              LEFT JOIN text t ON t.id=df.id_text WHERE df.id=".$id;
        return $this->db_edms->getListData($sql,['id','name']);
    }

    /**
     * Получение полной информации о задаче
     * @param $id - ИД задачи
     * @return mixed
     */
    public function getOneDocById($id){
        $sql="SELECT * FROM docs_flow WHERE id=".$id;
        $result=$this->db_edms->getOneData($sql,$this->docs_flow);
        if(isset($result['date_create_unix'])){
            $result['date_create_full'] = $result['date_create'];
            $result['date_create'] = UtilHome::getDateFormat($result['date_create'], "Y-m-d");
        }
        if($result['date_end_unix']!=null)
            $result['date_end'] = UtilHome::getDateFormat($result['date_end'], "Y-m-d");
        else
            unset($result['date_end'] );

        $result['date_first_full'] = $result['date'];
        $result['date_first'] = UtilHome::getDateFormat($result['date'], "Y-m-d");

        return $result;
    }

    public function checkResponsible($id){
        $doc=$this->getOneDocById($id);
        if($doc['id_user']==null&&$this->getShowType($doc['id_type'])==1)
            $sql="SELECT id_user FROM docs_flow WHERE id_parent=".$id." AND is_responsible=1";
        else{
            $parentId=$this->getParent($id);
            if($parentId!=0)
                $parent=$this->getOneDocById($parentId);
            $sql="SELECT id_user FROM docs_flow WHERE ".(is_array($parent)&&$parent['id_user']==null&&$this->getShowType($parent['id_type'])==1?
                    "id_parent=".$parentId:"id=".$id)." AND is_responsible=1";
        }
        return $this->db_edms->getOneData($sql,['id_user'])['id_user'];
    }

    /**
     * Генерация файла со списком подписей
     * @param $id - ИД документа
     * @return string
     */
    public static function generateSign($id){
        $templateProcessor = new TemplateProcessor(realpath(self::$docsDir."template/sign.docx"));
        $users=self::getMainSignForFile($id);
        if(count($users['main']) > 0) {
            $templateProcessor->cloneRow('main', count($users['main']));
            $n=1;
            foreach ($users['main'] as $user){
                $templateProcessor->setValue('main#'.$n, $user);
                $n ++;
            }
        }
        if(count($users['other']) > 0) {
            $templateProcessor->cloneRow('other', count($users['other']));
            $n=1;
            foreach ($users['other'] as $user){
                $templateProcessor->setValue('other#'.$n, $user);
                $n ++;
            }
        }else
            $templateProcessor->setValue('other', 'Нет');
        $filename = 'Подписи_'.$id;
        $file = realpath(self::$sign) . '/' . $filename . '.docx';
        $templateProcessor->saveAs($file);
        return $file;
    }

    public function haveLevelsAbove($id){
        $sql="SELECT count(*) cn from docs_flow df WHERE df.id=(SELECT id_parent from docs_flow WHERE docs_flow.id=".$id.") AND type IN (0,4,10) 
        AND ((df.id_parent<>0 AND (SELECT id_type FROM docs_flow WHERE docs_flow.id=df.id_parent)=1) OR ((select id_parent from docs_flow WHERE docs_flow.id=df.id_parent AND docs_flow.id_type=1)<>0 AND (select id_user from docs_flow WHERE docs_flow.id=df.id_parent AND docs_flow.id_type=1) is null))";
        $result=$this->db_edms->getOneData($sql, ['cn']);
        return $result['cn']!=0?true:false;
    }

    /**
     * Проверка наличия и получение главного документа от которого ведется поток задач
     * @param $code - Код потока задач
     * @return |null
     */
    public function haveDoc($code){
        $sql="SELECT docs_flow.id,docs_flow.id_type, docs_flow.id_parent, docs_flow.id_owner, docs_flow.id_operator, docs_flow.id_user, 
              docs_flow.code, text.id as id_text,text.text, text.name,docs_flow.type, docs_flow.date_show, docs_flow.date_create,docs_flow.date_end_fact,
              docs_flow.date_end, docs_flow.is_closed, docs_flow.is_changed1, docs_flow.is_changed2, docs_flow.is_changed3, docs_flow.is_changed4 FROM docs_flow
              JOIN text ON text.id=docs_flow.id_text WHERE docs_flow.code=".$code." AND docs_flow.id_type<>1 AND docs_flow.is_deleted=0";
        $result = $this->db_edms->getOneData($sql, ['id','id_parent','id_type','id_owner','id_operator','id_user','code','id_text','text','name','type','date_show','date_create','date_end','date_end_fact','status','is_closed','is_changed1','is_changed2','is_changed3','is_changed4']);
        return isset($result["id"])?$result:null;
    }

    /**
     * Получение списка наблюдателей для текущей задачи
     * @param $id - ИД задачи
     * @return array
     */
    public static function getWatchers($id){
        $result=array();
        $sql="SELECT DISTINCT id_user FROM flow_watchers WHERE id_flow=".$id." AND is_deleted=0";
        $watchers=$GLOBALS ["db10"]->getListData($sql,['id_user']);
        foreach ($watchers as $watcher)
            array_push($result,$watcher['id_user']);
        return $result;
    }
    /**
     * Получение списка исполнителей для текущей задачи
     * @param $id - ИД задачи
     * @return array
     */
    public static function getUsers($id){
        $sql="SELECT id_user FROM docs_flow WHERE id_parent=".$id." AND type in (4,10) AND id_user IS NOT NULL GROUP BY id_user";
        $result = $GLOBALS ["db10"]->getListData($sql,['id_user']);
        return $result[0]?$result:false;
    }

    /**
     * Проверка прочтения текущей задачи
     * @param $id - ИД задачи
     * @return bool
     */
    public function isRead($id){
        $sql="SELECT date_show FROM docs_flow WHERE id=".$id;
        return $this->db_edms->getOneData($sql, ['date_show'])['date_show']?true:false;
    }

    /**
     * Проверка является ли задача запросом на продление/ответом на продление(подтверждение или отклонение)
     * @param $id - ИД задачи
     * @return bool
     */
    public function isExtension($id){
        $sql="SELECT COUNT(*) cn FROM docs_flow WHERE id=".$id." AND type IN (2,6,7)";
        return $this->db_edms->getOneData($sql, ['cn'])['cn']!=0?true:false;
    }

    /**
     * Получение родительской задачи
     * @param $id - ИД задачи
     * @return mixed
     */
    public function getParent($id){
        $sql="SELECT id_parent FROM docs_flow WHERE id=".$id;
        return $this->db_edms->getOneData($sql, ['id_parent'])['id_parent'];
    }

    /**
     * Проверка, является ли задача отправленной данному пользователю
     * @param $id - ИД задачи
     * @param $user - ИД пользователя
     * @return bool
     */
    public function isItToMe($id, $user){
        $sql="SELECT COUNT(*) cn FROM docs_flow df WHERE df.id=".$id."  AND (df.id_user=".$user." 
            OR (df.id_user IN (SELECT id_boss FROM assignment where id_alternate=".$user.") AND df.id_operator=".$user."))";
        return $this->db_edms->getOneData($sql, ['cn'])['cn']!=0?true:false;
    }

    /**
     * Проверка, отправлена ли задача данным пользователем(имеет ли он к ней доступ)
     * @param $id - ИД задачи
     * @param $user - ИД пользователя
     * @return bool
     */
    public function isItFromMe($id, $user){
        $sql="SELECT COUNT(*) cn FROM docs_flow df WHERE df.id=".$id." AND (df.id_owner=".$user." OR (df.id_owner IN (SELECT id_boss FROM assignment where id_alternate=".$user.") AND df.id_operator=".$user."))";
        return $this->db_edms->getOneData($sql, ['cn'])['cn']!=0?true:false;
    }

    /**
     * Проверка все ли дочернии задачи прочитаны
     * @param $id - ИД задачи
     * @return bool
     */
    public function checkChildIsRead($id){
        $sql="SELECT date_show FROM docs_flow df WHERE df.id_parent=".$id." AND is_deleted=0";
        $allCh=$this->db_edms->getListData($sql, ['date_show']);
        foreach ($allCh as $ch)
            if(isset($ch['date_show']))
                return true;
        return false;
    }

    /**
     * Проверка, все ли дочернии задачи закрыты у данной
     * @param $id - ИД задачи
     * @return bool
     */
    public function allChildIsClose($id){
        $sql="SELECT COUNT(*) cn FROM docs_flow WHERE id_parent=".$id." AND is_closed=0 AND is_deleted=0";
        return $this->db_edms->getOneData($sql,['cn'])['cn']==0?true:false;
    }

    /**
     * Проверка есть ли у данной задачи дочернии задачи
     * @param $id - ИД задачи
     * @return bool
     */
    public function haveChild($id){
        $sql="SELECT COUNT(*) cn FROM docs_flow WHERE id_parent=".$id." AND is_deleted=0";
        return $this->db_edms->getOneData($sql,['cn'])['cn']>0?true:false;
    }

    /**
     * Получение названия и описания задачи
     * @param $id - ИД задачи
     * @return mixed
     */
    public function getText($id){
        $sql="SELECT text.id, text.name, text.text FROM text 
              LEFT JOIN docs_flow ON docs_flow.id=".$id."
              WHERE text.id=docs_flow.id_text";
        return $this->db_edms->getOneData($sql, ['id','name','text']);
    }

    /**
     * Получение наименования документа
     * @param $id - ИД запроса
     * @return mixed
     */
    public function getDocumentName($id){
        $sql="SELECT df.id,t.name FROM docs_flow df 
              LEFT JOIN text t ON t.id=df.id_text WHERE df.id=".$id;
        return $this->db_edms->getListData($sql,['id','name']);
    }


    public function getAllInformation($id){
        $sql="SELECT df.id_owner, df.id_operator, df.id_user, df.date_end, t.name, t.text from docs_flow df
              LEFT JOIN text t ON t.id=df.id_text 
              WHERE df.id=".$id;
        $result=$this->db_edms->getOneData($sql, ["id_owner", "id_operator", "id_user", "name", "text", "date_start", "date_end"]);
        if($result['date_end_unix']!=null)
            $result['date_end'] = UtilHome::getDateFormat($result['date_end'],"Y-m-d");
        else
            unset($result['date_end']);
        return $result;
    }

    /**
     * Проверка все ли запросы на подписание закрыты
     * @param $id - ИД задачи
     * @return bool
     */
    public function allSignChildIsClose($id){
        $sql="SELECT COUNT(*) cn FROM docs_flow WHERE id_parent=".$id." AND id_type=5 AND is_responsible=0 AND is_deleted=0";
        $max=$this->db_edms->getOneData($sql,['cn'])['cn'];
        $sql="SELECT COUNT(*) cn FROM docs_flow WHERE id_parent=".$id." AND id_type=5 AND is_responsible=0 AND is_closed=1 AND is_deleted=0";
        $real=$this->db_edms->getOneData($sql,['cn'])['cn'];
        return ($max-$real)==0?true:false;
    }

    /**
     * Проверка закрыта задача или нет
     * @param $id - ИД задачи
     * @return bool
     */
    public function isClosed($id){
        $sql="SELECT is_closed FROM docs_flow WHERE id=".$id;
        return $this->db_edms->getOneData($sql, ['is_closed'])['is_closed']==1?true:false;
    }

    /**
     * Проверка является ли данная задача заспросом завершения
     * @param $id - ИД задачи
     * @return bool
     */
    public function isClosing($id){
        $sql="SELECT type FROM docs_flow WHERE docs_flow.id=".$id;
        return $this->db_edms->getOneData($sql, ['type'])['type']==3?true:false;
    }

    /**
     * Проверка существует ли уже незакрытый запрос на завершение у даной задачи
     * @param $id
     * @return bool
     */
    public function openClosing($id){
        $sql="SELECT COUNT(*) cn FROM docs_flow WHERE id_parent=".$id." AND is_closed=0 AND type=3 AND is_deleted=0";
        return $this->db_edms->getOneData($sql,['cn'])['cn']>0?true:false;
    }

    /**
     * Проверка существует ли уже незакрытый запрос на продление у даной задачи
     * @param $id
     * @return bool
     */
    public function openExtension($id){
        $sql="SELECT COUNT(*) cn FROM docs_flow WHERE id_parent=".$id." AND is_closed=0 AND type=3 AND is_deleted=0";
        return $this->db_edms->getOneData($sql,['cn'])['cn']>0?true:false;
    }


}