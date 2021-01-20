<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
class SaveHome{
    private $docHome;
    protected $db_edms;
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
    protected $files=array(
        'id',
        'id_text',
        'real_name',
        'name',
        'weight',
        'is_deleted',
    );
    protected $users=array(
        'id',
        'id_flow',
        'id_user',
        'id_execution',
        'is_deleted',
    );
    protected $assignment=array(
        'id',
        'id_boss',
        'id_alternate',
        'date_start',
        'date_end',
        'type',
        'is_deleted'
    );
    protected $text=array(
        'id',
        'text',
        'name'
    );
    public function __construct(){
        $this->docHome = new DocumentationHome();
        $this->db_edms = $GLOBALS ["db10"];
    }



    /**
     * Установление флага 'Завершено'
     * @param $id - ИД задачи
     */
    public function setClosed($id){
        $sql="UPDATE docs_flow SET is_closed=1, date_end_fact=".time()." WHERE id=".$id." ";
        $this->db_edms->getOneData($sql, []);
    }

    /**
     * Удаление всего дерева начиная с текущего
     * @param $id - ИД задачи
     */
    public function deleteTree($id){
        $result=array();
        array_push($result,['id'=>$id]);
        $parent=$id;
        $flag=0;
        while ($flag!=-1){
            $sql = "SELECT docs_flow.id FROM docs_flow
                JOIN text ON text.id=docs_flow.id_text WHERE docs_flow.id_parent=".$parent;
            $temp = $this->db_edms->getListData($sql, ['id']);
            if(isset($temp[0]['id'])){
                $result=array_merge($result,$temp);
                $flag++;
                $parent=$result[$flag]['id'];
            }
            else
                $flag=-1;
        }
        foreach ($result as $value) //Обходим массив
            $this->setDeleted('docs_flow',$value['id']);
    }

    /**
     * Выставление флагов 'Изменения просмотрены'
     * @param $id - ИД задачи
     * @param $user - ИД пользователя
     */
    function setNotChanged($id,$user){
        $items=$this->docHome->getOneDocById($id);
        $sql="";
        if($items['id_user']==$user) $sql.=" is_changed2=0,";
        if ($items['id_owner']==$user) $sql.=" is_changed1=0,";
        if ($items['id_operator']==$user) $sql.=" is_changed4=0,";
        if ($items['id_watcher']==$user) $sql.=" is_changed3=0,";
        if($sql!=""){
            $sql="UPDATE docs_flow SET ".substr($sql,0,strlen($sql)-1)." WHERE id=".$id;
            $this->db_edms->getOneData($sql, []);
        }
    }

    /**
     * Выставление флагов 'Непросмотренные изменения'
     * @param $id - ИД задачи
     * @param $user - ИД пользователя
     */
    public function setChanged($id,$user){
        $items=$this->docHome->getOneDocById($id);
        if($items['id_user']==$user){
            $sql="UPDATE docs_flow SET is_changed1=1";
            if($items['id_watcher']!="") $sql.=", is_changed3=1 ";
            if($items['id_operator']!="") $sql.=", is_changed4=1 ";
        }elseif ($items['id_owner']==$user){
            $sql="UPDATE docs_flow SET is_changed2=1 ";
            if($items['id_watcher']!="") $sql.=", is_changed3=1 ";
            if($items['id_operator']!="") $sql.=", is_changed4=1 ";
        }elseif ($items['id_operator']==$user){
            $sql="UPDATE docs_flow SET is_changed1=1, is_changed2=1 ";
            if($items['id_watcher']!="") $sql.=", is_changed3=1 ";
        }
        $sql.=" WHERE id=".$id;
        $this->db_edms->getOneData($sql, []);
    }

    /**
     * Выставление флага 'Удалено'
     * @param $table - Наименование таблицы БД
     * @param $id - ИД записи
     */
    public function setDeleted($table,$id){
        $sql="UPDATE ".$table." SET is_deleted=1 WHERE id=".$id." ";
        $this->db_edms->getOneData($sql, []);
    }

    /**
     * Закрытие всего дерева запросав начиная с текущего
     * @param $id - ИД задачи
     */
    public function setAllClose($id){
        $sql="SELECT id FROM docs_flow WHERE id_parent=".$id." AND is_deleted=0 AND is_closed=0";
        if($this->docHome->getShowType($this->docHome->getDocType($id))==1) $this->setClosed($id);
        $result=$this->db_edms->getListData($sql, ['id']);
        if(isset($result[0])&&$result[0]!="")
            foreach ($result as $res)
                $this->setAllClose($res['id']);
    }

    /**
     * Копирование файлов
     * @param $id - ИД задачи
     * @param $text - ИД описания
     */
    public function copyFiles($id,$text){
        $sql="SELECT f.name FROM files f LEFT JOIN docs_flow df ON df.id_text=f.id_text WHERE df.id=".$id." AND f.is_deleted=0";
        $files=$this->db_edms->getListData($sql, ['name']);
        foreach ($files as $file){
            $file['id_text']=$text;
            $this->db_edms->saveData($file, 'files', $this->files);
        }
    }

    /**
     * Редактирование
     * @param $user - ИД пользователя
     * @param $params - массив параметров (название, описание, ИД, удаляемые файлы и тд)
     * @param $files - массив файлов для добавления
     * * @throws Exception
     */
    public function edit($user, $params, $files){
        if ($params['id']!='')
            $doc = $this->docHome->getOneDocById($params['id']);
        $text=$doc['id_text'];
        unset($params['id']);
        $this->update($params, 'text', $doc['id_text']);
        $allId=$this->docHome->getDoc($doc['id_text'],$doc['code']);
        foreach ($allId as $key => $item) {
            $this->update($params, 'docs_flow', $item['id']);
            $this->setChanged($item['id'],$user);
        }
        $this->saveFiles($files, $text);
        $this->deleteFiles($params['scans'], $doc['id']);
    }

    /**
     * Выставление флага 'Прочитано'
     * @param $id - ИД задачи
     * @throws Exception
     */
    public function setRead($id){
        $date=time();
        $this->update(['date_show'=>$date],'docs_flow',$id);
        $doc=$this->docHome->getOneDocById($id);
        if($doc['type']==4) {
            $sql="SELECT COUNT(*) cn FROM docs_flow WHERE id_parent=".$doc['id_parent']." AND date_show IS NULL AND is_deleted=0";
            $count=$this->db_edms->getOneData($sql,['cn'])['cn'];
            if($count==0)
                $this->update(['date_show'=>$date],'docs_flow',$doc['id_parent']);
        }
    }

    /**
     * Сохранение описания нового запроса
     * @param $params - массив параметров (name, text)
     * @return mixed
     */
    public function saveText($params){
        if(isset($params['id'])) unset($params['id']);
        $this->db_edms->saveData($params, 'text', $this->text);
        $sql = "select LAST_INSERT_ID() as id";
        return $this->db_edms->getOneData($sql, ['id'])['id'];
    }

    /**
     * Регистрация нового потока задач
     * @return mixed
     */
    public function saveMainDoc(){
        $this->db_edms->saveData(['is_deleted' => 0], 'docs', ['id','is_deleted']);
        $sql = "select LAST_INSERT_ID() as id";
        return $this->db_edms->getOneData($sql, ['id'])['id'];
    }

    /**
     * Сохранение одной задачи
     * @param $params - массив параметров
     * @return mixed
     */
    public function saveDocFlow($params){
        if(isset($params['id'])) unset($params['id']);
        $this->db_edms->saveData($params, 'docs_flow', $this->docs_flow);
        $sql = "select LAST_INSERT_ID() as id";
        return $this->db_edms->getOneData($sql, ['id'])['id'];
    }

    /**
     * Выставление флага подписания и сохранение хэша
     * @param $id - ИД задачи
     * @param $hash - Хэш-код подписывающего пользователя
     */
    public function setSign($id,$hash){
        $sql="UPDATE docs_flow SET is_signed=1".($hash!=null?", hash='".$hash."'":'')." WHERE id=".$id;
        $this->db_edms->getOneData($sql,[]);
    }

    /**
     * Сохранение изменений в таблицы
     * @param $items - массив изменяемых параметров
     * @param $table - Наименование таблицы
     * @param $id - ИД записи
     * @throws Exception
     */
    public function update($items,$table,$id){
        $sql="UPDATE `".$table."` SET ";
        foreach($items as $key=>$item){
            if(in_array($key,$this->$table)){
                if($key=="date_start"||$key=="date_end"){
                    if(is_string($item)){
                        $item=UtilHome::getTimeStamp($item);
                    }
                }
                if($key=='name'||$key=='text'||$key=='number')
                    $sql.=$key."='".$item."',";
                else
                    $sql.=$key."=".$item.",";
            }
        }
        $sql=substr($sql,0,strlen($sql)-1);
        $sql.=" WHERE id=".$id;
        $this->db_edms->getListData($sql,[]);
    }

    /**
     *
     * @param $text - ИД описания
     * @param $files - массив файлов
     */
    public function saveCopy($text,$files){
        if(is_array($files))
            foreach ($files as $file)
                $this->db_edms->saveData(['id_text'=>$text, 'name'=>$file['name'], 'real_name'=>$file['real_name'], 'weight'=>2],'files',$this->files);
    }

    /**
     * Сохранение наблюдателей
     * @param $id - ИД поручения
     * @param $user - ИД пользователя
     * @param $execution - ИД исполнения
     */
    public function saveWatcher($id, $user, $execution){
        $this->db_edms->saveData(['id_flow'=>$id, 'id_user'=>$user, 'id_execution'=>$execution],'flow_watchers', $this->users);
    }

    /**
     * Удаление файлов
     * @param $scans - массив файлов для удаления
     * @param $id - ИД задачи
     */
    public function deleteFiles($scans,$id){
        $sql="SELECT files.id,files.name FROM files
              LEFT JOIN text ON text.id=files.id_text
              LEFT JOIN docs_flow on docs_flow.id_text=text.id
              WHERE docs_flow.id=".$id." AND files.is_deleted=0 ";
        $arr=$this->db_edms->getListData($sql,['id','name']);
        foreach ($scans as $scan)
            $this->setDeleted('files',$this->find($arr,basename($scan)));
    }

    private function find($arr,$name){
        foreach ($arr as $item)
            if($item['name']==$name)
                return $item['id'];
    }

    /**
     * Сохранение файлов
     * @param $files - масиив файлов
     * @param $text - ИД описания
     * @return array
     */
    public function saveFiles($files, $text){
        $result=array();
        if($files['user_file']['tmp_name'][0]){
            foreach ($files['user_file']['tmp_name'] as $key => $file) {
                $realName=$files['user_file']['name'][$key];
                $files['user_file']['name'][$key]=UtilHome::translit($files['user_file']['name'][$key]);
                $name=substr($files['user_file']['name'][$key],0,stripos($files['user_file']['name'][$key],"."))."_".UtilHome::generate(5).strrchr($files['user_file']['name'][$key],".");
                $uploadfile = UtilHome::setScanPath($name);
                move_uploaded_file($file, iconv('UTF-8', 'Windows-1251', $uploadfile));
                array_push($result,['name'=>$name, 'real_name'=>$realName]);
                $this->db_edms->saveData(['id_text'=>$text, 'name'=>$name, 'real_name'=>$realName, 'weight'=>2],'files',$this->files);
            }
        }
        return $result;
    }

    /**
     * Сохранение замещения
     * @param $params - массив параметров замещения
     * @throws Exception
     */
    public function saveAssignment($params){
        unset($params['rights']);
        if(!$params['mode']){
            $params['date_start']=UtilHome::getTimeStamp($params['date_start']);
            $params['date_end']=UtilHome::getTimeStamp($params['date_end']);
        }else
            unset($params['date_start'],$params['date_end']);
        $this->db_edms->saveData($params,'assignment',$this->assignment);
    }

    /**
     * Сохранение изменений
     * @param $params - массив параметров
     * @throws Exception
     */
    public function updateAssignment($params){
        unset($params['rights']);
        $text=!$params['mode']?" date_start=".UtilHome::getTimeStamp($params['date_start']).", date_end=".UtilHome::getTimeStamp($params['date_end']).", type=0":
            " date_start=NULL, date_end=NULL, type=1";
        $sql="UPDATE assignment SET ".$text." WHERE id=".$params['id'];
        $this->db_edms->getListData($sql, []);
    }

    /**
     * Удаление замещения
     * @param $id - ИД замещения
     */
    public function deleteAssignment($id){
        $sql="UPDATE assignment SET is_deleted=1 WHERE id=".$id;
        $this->db_edms->getOneData($sql, []);
    }

    public function closing($id,$user,$items,$files)
    {
        if($id!=null){
            $doc = $this->docHome->getOneDocById($id);
            if ($doc['type'] == 3){//подтвердение закрытия
                $text = $this->saveText(['name' => "Подтверждено", 'text' => 'Закрытие поручения одобрено']);
                $this->saveFiles($files,$text);
                $newId=$this->saveDocFlow(['id_type' => 1, 'id_parent' => $doc['id'], 'id_owner' => $doc['id_user'],
                    'id_user' => $doc['id_owner'], 'id_text' => $text, 'is_closed' => 1, 'code' => $doc['code'],
                    'date_create' => time(),'date' => time(), 'date_end' => time(), 'type' => 1]);
                $this->setChanged($newId,$user);
                $this->setClosed($doc['id']);//закрываю запрос на закрытие
                $this->setClosed($doc['id_parent']);//закрываю само поручение
                //это поручение имеет общее, которое выдано на нескольких людей и ответственный закрыл свое поручение
                $p=$this->docHome->getOneDocById($this->docHome->getParent($doc['id_parent']));
                if(isset($p['id'])&&$p['id_user']==""&&$p['id_type']==1){
                    if($this->docHome->responsibleCompleted($p['id'])){//если ответственный завершил выполнение
                        $this->setAllClose($p['id']);
                    }
                }
            }else{//Создание закрытия поручения
                $text = $this->saveText(['name' => "Закрытие поручения", 'text' => $items['text']]);
                $this->saveFiles($files,$text);
                $params=array('id_type' => 1, 'id_parent' => $id, 'id_owner' => $doc['id_user'], 'id_user' => $doc['id_owner'], 'id_text' => $text,
                    'code' => $doc['code'], 'date_create' => time(),'date' => time(), 'type' => 3);
                if($doc['is_tracked']==0){//запрос создается закрытим если поручение было без отслеживания
                    $params['is_closed']=1;
                    $params['date_end_fact']=time();
                    $this->setClosed($doc['id']);//закрываю само поручение
                    $this->setChanged($doc['id'],$doc['id_user']);//изменение для закрытого поручения
                    //это поручение имеет общее, которое выдано на нескольких людей и ответственный закрыл свое поручение
                    $p=$this->docHome->getOneDocById($doc['id_parent']);
                    if(isset($p['id'])&&$p['id_user']==""&&$p['id_type']==1){
                        if($this->docHome->responsibleCompleted($p['id'])){//если ответственный завершил выполнение
                            $this->setAllClose($p['id']);
                        }
                    }
                }
                $newId=$this->saveDocFlow($params);//создание запроса
                $this->setChanged($newId,$user);
            }
        }
    }

}