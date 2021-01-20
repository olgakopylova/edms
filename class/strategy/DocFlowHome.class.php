<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
require_once($modules_root."edms/class/SaveHome.class.php");
require_once($modules_root."edms/class/strategy/IStrategy.php");
require_once($modules_root."edms/class/ValidationHome.class.php");
require_once($modules_root."edms/class/RightsHome.class.php");
require_once($modules_root."edms/class/PrintHTMLHome.class.php");
class DocFlowHome
{
    private $templateHome;
    private $modules_root;
    private $rights;
    private $print;
    private $docHome;
    private $docSave;
    private $db_esia;
    private $db_edms;
    public function  __construct(){
        $this->templateHome=$GLOBALS['templateHome'];
        $this->modules_root=$GLOBALS['modules_root'];
        $this->db_edms = $GLOBALS ["db10"];
        $this->db_esia=$GLOBALS["db2"];
        $this->docHome=new DocumentationHome();
        $this->docSave=new SaveHome();
        $this->rights=new RightsHome();
        $this->print=new PrintHTMLHome();
    }

    /**
     * @param $id - ИД запроса
     * @param $mode
     * @param array $arr
     * @return array
     */
    public function showClose($id,$mode,$arr=[]){
        $items['id_type'] = 1;
        if($mode==3){
            $close=$this->docHome->getText($id);
            $temp=array('addFile'=>$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []),
                'close'=>true, 'id'=>$id,'type'=>'report','name'=>'Закрытие поручения','text'=>$close['text']);
        }
        elseif($mode==1)
            $temp=array('addFile'=>$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []),
                'close'=>true, 'id'=>$id,'type'=>'closing','name'=>'Закрытие поручения');
        $temp=array_merge($temp,$arr);
        $param['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/cl_apt_rev_modal.tpl", $temp);
        $param['title']="Закрытие";
        return ['content'=> $this->templateHome->parse($this->modules_root . "edms/tpl/modal.tpl", $param)];
    }

    /**
     * Отображение продления
     * @param $id - ИД запроса
     * @param $mode
     * @param $items
     * @return array - Код в tpl
     */
    public function showExtension($id,$mode, $items=[]){
        /**if($mode==1){//продление выше
            $temp=$this->docHome->getOneDocById($id);
            $temp['name']="Запрос на продление";
            $temp['id']=$this->docHome->getOneDocById($this->docHome->getParent($temp['id_parent']))['id'];
            $temp['dop']=$id;
            $temp['text']=$this->docHome->getText($temp['id'])['text'];
            $temp['ext']='3';
        }else//просто продление*/
            $temp['id']=$id;
        $temp['name']="Запрос на продление";
        $temp['type']="ext";
        $temp['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
        $temp=array_merge($items,$temp);

        $modal['body']=$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/extension.tpl", $temp);
        $modal['title']="Продление";
        return ['content'=>$this->templateHome->parse($this->modules_root . "edms/tpl/modal.tpl", $modal)];
    }

    /**
     * @param $id - ИД запроса
     * @param $user - ИД пользователя
     * @param array $items
     * @return array
     */
    public function showErrand($id, $user, $items=[]){

        if(isset($items['id_prot'])){

            $type=$this->docHome->getOneDocById($items['id_prot']);
            $doc=$this->docHome->getText($items['id_prot']);
            $doc['id']=$type['id'];
            $temp['rows0'] = $this->print->makeOption([0=>$doc],$doc['id']);
            $temp['rows1'] = $this->print->makeOption($this->rights->getAssignment($user), $user);
            $temp['type'] = 'new';
            $sel=$this->print->makeOption($this->docHome->getOneDocType($type['id_type']), $type['id_type']);
        }else{
            $temp=$this->docHome->getAllInformation($id);
            $temp['id']=$id;
        }
        $temp['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
        PrintHTMLHome::set($this->rights->getDepartmentTree($user));
        $departments=UserHome::getUserDepartment($user);
        $temp['rows2']="";
        foreach ($departments as $department)
            $temp['rows2'].=$this->print->outDepartmentTree($department['id_parent'],0,"",$user);
        $temp['rows3']=$temp['rows2'];
        $temp['user']=$user;
        $temp=array_merge($temp,$items);
        if(isset($items['id_prot']))
            $modal['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/select.tpl", ['body' => $this->templateHome->parse($this->modules_root . "edms/tpl/docs/task.tpl", $temp),
                'rows1' => $sel, 'user' => $user,'close'=>true]);
        else
            $modal['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/errand.tpl", $temp);
        $modal['title']="Поручение";
       return ['content'=>$this->templateHome->parse($this->modules_root . "edms/tpl/modal.tpl", $modal)];
    }

    /**
     * Отображение подписания
     * @param $id - ИД запроса
     * @param $mode
     * @return array - Код в tpl
     */
    public function showSigning($id,$mode){
        $temp=['id'=>$id,'n'=>'name_cl','t'=>'text', 'type'=>'sign','mode'=>$mode,'addFile'=>$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", [])];
        if($temp['mode']==0)
            $temp['hide']=true;
        return ['content'=>$this->templateHome->parse($this->modules_root . "edms/tpl/modal.tpl", ['title'=>'Подписание',
            'body'=>$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/sign.tpl", $temp)])];
    }

    /**
     * @param $id
     * @return array
     */
    public function showSupplement($id, $items=[]){
        $supplement=['id'=>$id,
            'addFile'=>$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []),'suppl'=>true];
        $supplement=array_merge($items,$supplement);
        $modal['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/supplement.tpl", $supplement);
        $modal['title']="Дополнение";
        return ['content'=>$this->templateHome->parse($this->modules_root . "edms/tpl/modal.tpl", $modal)];
    }

    public function showRevision($id, $items=[]){
        $temp=['id'=>$id,'type'=>'rev','hide'=>true,'name'=>'Отклонено'];
        $temp=array_merge($temp,$items);
        $temp['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
        $param['body']=$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/cl_apt_rev_modal.tpl", $temp);
        $param['title']="Закрытие";
        return ['content'=>$this->templateHome->parse($this->modules_root . "edms/tpl/modal.tpl", $param)];
    }

    public function saveReport($id,$user,$params,$files){
        $rpt=$this->docHome->getOneDocById($id);
        unset($rpt["is_closed"]);
        $text=$this->docSave->saveText(['name'=>"Закрытие поручения"]);
        $this->docSave->copyFiles($id,$text);
        $this->docSave->saveFiles($files,$text);
        $parent=$this->docHome->getOneDocById($this->docHome->getParent($rpt['id_parent']));//когда
        if(($parent['id_user']==""||$parent['id_user']==null)&&$parent['id_type']==1)//если было найдено общее поручение
            $parent=$this->docHome->getOneDocById($parent['id_parent']);
        $save=array('id_parent'=>$parent['id'], 'id_text'=>$text, 'id_type'=>$parent['id_type'], 'id_owner'=>$user, 'id_user'=>$parent['id_owner'],
            'date'=>time(), 'date_create'=>time(), 'code'=>$parent['code'], 'type'=>3);
        if($parent['id_operator']!="") $save['id_operator']=$parent['id_operator'];
        if($parent['id_watcher']!="") $save['id_watcher']=$parent['id_watcher'];
        if($parent['is_tracked']==0){//запрос создается закрытим если поручение было без отслеживания
            $this->docSave->setClosed($parent['id']);//закрываю само поручение
            $this->docSave->setChanged($parent['id'],$parent['id_user']);//изменение для закрытого поручения
            //это поручение имеет общее, которое выдано на нескольких людей и ответственный закрыл свое поручение
            $p=$this->docHome->getOneDocById($this->docHome->getParent($parent['id_parent']));
            if(isset($p['id'])&&$p['id_user']==""&&$p['id_type']==1)
                if($this->docHome->responsibleCompleted($p['id']))//если ответственный завершил выполнение
                    $this->docSave->setAllClose($p['id']);
        }
        $this->docSave->setChanged($this->docSave->saveDocFlow($save),$user);
    }

    /**
     * Сохранение запроса закрытия или подтверждение закрытия
     * @param $id - ИД задачи
     * @param $user - ИД пользователя
     * @param $items - Заменяемые параметры
     * @param $files - Массив файлов
     */
    public function saveClose($id,$user,$items,$files){
        if($id!=null){
            $doc = $this->docHome->getOneDocById($id);
            if ($doc['type'] == 3){//подтвердение закрытия
                $textId = $this->docSave->saveText(['name' => "Подтверждено", 'text' => 'Закрытие поручения одобрено']);
                $this->docSave->saveFiles($files,$textId);
                $id=$this->docSave->saveDocFlow(['id_type' => 1, 'id_parent' => $doc['id'], 'id_owner' => $doc['id_user'],
                    'id_user' => $doc['id_owner'], 'id_text' => $textId, 'is_closed' => 1, 'code' => $doc['code'],
                    'date_create' => time(),'date' => time(), 'date_end' => time(), 'type' => 1]);
                $this->docSave->setChanged($id,$user);
                $this->docSave->setClosed($doc['id']);//закрываю запрос на закрытие
                $this->docSave->setClosed($doc['id_parent']);//закрываю само поручение
                //это поручение имеет общее, которое выдано на нескольких людей и ответственный закрыл свое поручение
                $p=$this->docHome->getOneDocById($this->docHome->getParent($doc['id_parent']));
                if(isset($p['id'])&&$p['id_user']==""&&$p['id_type']==1)
                    if($this->docHome->responsibleCompleted($p['id']))//если ответственный завершил выполнение
                        $this->docSave->setAllClose($p['id']);
            }else{//Создание закрытия поручения
               $textId = $this->docSave->saveText(['name' => "Закрытие поручения", 'text' => $items['text']]);
                $this->docSave->saveFiles($files,$textId);
                $params=array('id_type' => 1, 'id_parent' => $id, 'id_owner' => $doc['id_user'], 'id_user' => $doc['id_owner'], 'id_text' => $textId,
                    'code' => $doc['code'], 'date_create' => time(),'date' => time(), 'type' => 3);
                if($doc['is_tracked']==0){//запрос создается закрытим если поручение было без отслеживания
                    $params['is_closed']=1;
                    $params['date_end_fact']=time();
                    $this->docSave->setClosed($doc['id']);//закрываю само поручение
                    $this->docSave->setChanged($doc['id'],$doc['id_user']);//изменение для закрытого поручения
                    //это поручение имеет общее, которое выдано на нескольких людей и ответственный закрыл свое поручение
                    $p=$this->docHome->getOneDocById($doc['id_parent']);
                    if(isset($p['id'])&&$p['id_user']==""&&$p['id_type']==1)
                        if($this->docHome->responsibleCompleted($p['id']))//если ответственный завершил выполнение
                            $this->docSave->setAllClose($p['id']);
                }
                $id=$this->docSave->saveDocFlow($params);//создание запроса
                $this->docSave->setChanged($id,$user);
            }
        }
    }

    /**
     * Сохранение продления или отклонение закрытия
     * @param $doc - массив записи
     * @param $user - ИД пользователя
     * @param $params - Заменяемые параметры
     * @param $files - Массив файлов
     * @throws Exception
     */
    public function saveExtensionOrRevision($doc,$user,$params,$files){
        $text = $this->docSave->saveText($params);
        $this->docSave->saveFiles($files, $text);
        $doc['id_parent'] = $doc['id'];
        unset($doc['id']);
        $t = $doc['id_owner'];
        $doc['id_owner'] = $doc['id_user'];
        $doc['id_user'] = $t;
        $doc['id_text'] = $text;
        $doc['date_create'] = $doc['date'] = time();
        unset($doc['date_show']);
        if (isset($params['ext'])) {
            $doc['date_end_fact'] = time();
            $doc['date_end'] = UtilHome::getTimeStamp($params['date_end']);
            $doc['type'] = 2;
        }else {
            $this->docSave->setClosed($params['id']);
            $doc['id_owner']=$user;
            $doc['is_closed']=1;
            $doc['date_end']=time();
            $doc['type']=5;
        }
        $this->docSave->setChanged($this->docSave->saveDocFlow($doc),$user);
    }

    /**
     * Дополнение к задаче
     * @param $user - ИД пользователя
     * @param $params - Текст дополнения
     * @param $files - Массив файлов
     */
    public function saveSupplement($user, $params, $files){
        $text=$this->docHome->getText($params['id']);
        $this->docSave->saveFiles($files, $text['id']);
        $text['text'].="<p><b style=\"background-color: rgb(255, 231, 156);\">Дополнено ".date("d-m-Y H:i:s")."</b></p>".$params['text'];
        $this->docSave->update(['text'=>$text['text']],'text',$text['id']);
        $this->docSave->setChanged($params['id'],$user);
    }

    /**
     * Сохранение ответа по подписанию
     * @param $userId
     * @param $params
     * @param $files
     */
    public function saveAnswerSign($userId, $params, $files){
        $doc = $this->docHome->getOneDocById($params['id']);
        $hash=$params['COOKIE']['srfhh' . $userId];
        //в любом случае это уже закрытый запрос
        $this->docSave->setClosed($params['id']);
        /** Сохранение текста*/
        $params['name']=$params['mode']==1?"Подписано":"Отклонено";
        $textId = $this->docSave->saveText($params);

        //подписание
        if($params['mode']==1){
            //подписывает конкретно у человека
            $this->docSave->setSign($params['id'],$hash);
            //если человек главный по подписи или все уже подписали
            if($doc['is_responsible']==1||$this->docHome->allChildIsClose($doc['id_parent'])){
                $this->docSave->setSign($doc['id_parent'],$hash);
                $this->docSave->setSign($this->docHome->getParent($doc['id_parent']),null);
                if($this->docHome->getShowType($doc['id_parent'])==0)
                    $this->docSave->setAllClose($doc['id_parent']);
            }
        }else//отклонение подписания
            if($doc['is_responsible']==1&&$this->docHome->getShowType($doc['id_parent'])==0)
                $this->docSave->setAllClose($doc['id_parent']);
        $temp=$doc['id_user'];
        $doc['id_parent']=$doc['id'];

        $doc['id_user']=$doc['id_owner'];
        $doc['id_owner']=$temp;
        if($textId!=null)
            $doc['id_text']=$textId;
        $doc['date_create']=$doc['date_end']=$doc['date_end_fact']=$doc['date']=time();
        $doc['type']=1;
        $doc['is_closed']=1;
        unset($doc['id'],$doc['is_responsible']);
        $this->docSave->setChanged($this->docSave->saveDocFlow($doc),$userId);
    }


    /**
     * Сохранение ответа на продление поручнеия
     * @param $id - ИД запроса
     * @param $type - Тип ответа(подтверждение, отклонение)
     * @param $user - ИД пользователя
     * @param $date - Дата
     * @throws Exception
     */
    public function answerExtension($id, $type, $user, $date){
        $doc=$this->docHome->getOneDocById($id);
        $this->docSave->setClosed($id);
        if($user==$doc['id_user'])
            unset($doc['id_operator']);
        switch ($type){
            case 1:
                if($date=="")
                    $date=$doc['date_end'];
                $this->docSave->update(['date_end'=>$date],'docs_flow',$id);
                $param=['name'=>"Подтверждено", 'text'=>"<p>Продление даты окончания одобрено</p>"];
                //продление даты окончания
                $sql="SELECT id, id_parent FROM docs_flow WHERE id=".$doc['id_parent'];
                $res=$this->db_edms->getOneData($sql,['id','id_parent']);
                if(isset($res['id']))
                    $this->docSave->update(['date_end'=>$date],'docs_flow',$res['id']);
                //если поручение на нескольких человек, то продлить общее
                $sql="SELECT id, id_parent FROM docs_flow WHERE id=".$res['id_parent']." AND id_user IS NULL";
                $res=$this->db_edms->getOneData($sql,['id']);
                if(isset($res['id']))
                    $this->docSave->update(['date_end'=>$date],'docs_flow',$res['id']);
                break;
            case 0:
                $param=['name'=>"Отклонено", 'text'=>"<p>Продление даты окончания не одобрено</p>"];
                break;
        }
        $id=$this->docSave->saveDocFlow(['id_type'=>1,'id_text'=>$this->docSave->saveText($param),'code'=>$doc['code'],'id_parent'=>$doc['id'],'id_user'=>$doc['id_owner'],
            'id_operator'=>$doc['id_operator'],'id_owner'=>$doc['id_user'],'date_create'=>time(),'date'=>time(),'date_end'=>time(),
            'date_end_fact'=>time(),'type'=>$type==1?6:7,'is_closed'=>1]);
        $this->docSave->setChanged($id,$user);
    }
}