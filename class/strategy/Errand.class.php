<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
require_once($modules_root."edms/class/SaveHome.class.php");
require_once($modules_root."edms/class/strategy/IStrategy.php");
require_once($modules_root."edms/class/strategy/DocFlowHome.class.php");
require_once($modules_root."edms/class/ValidationHome.class.php");
require_once($modules_root."edms/class/RightsHome.class.php");
require_once($modules_root."edms/class/PrintHTMLHome.class.php");
class Errand implements IStrategy
{
    private $check;
    private $right;
    private $printHtml;
    private $templateHome;
    private $modules_root;
    private $docHome;
    private $docSave;
    private $action;
    public function  __construct(){
        $this->check=new ValidationHome();
        $this->right=new RightsHome();
        $this->printHtml=new PrintHTMLHome();
        $this->templateHome=$GLOBALS['templateHome'];
        $this->modules_root=$GLOBALS['modules_root'];
        $this->docHome=new DocumentationHome();
        $this->docSave=new SaveHome();
        $this->action=new DocFlowHome();
    }

    public function tpl(){
        return "task.tpl";
    }

    public function check($items, $user){
        if($items['id_watcher']==null||$items['id_watcher']=="")
            unset($items['id_watcher']);
        $result=array_merge($items,$this->check->checkNewAndErrand($items));
        $result=array_merge($this->action->showErrand($items['id'], $user,array_merge($result,$items)),$result);
//        PrintHTMLHome::set($this->right->getDepartmentTree($user));
//        $departments=UserHome::getUserDepartment($user);
//        if(isset($items['new'])){
//            /*if($items['id_prot']!=''){
//                $doc=$this->docHome->getOneDocById($items['id_prot']);
//                $param['rows0'] = $this->printHtml->makeOption($this->docHome->getName($doc['id']),$doc['id']);
//                $param['rows1'] = $this->printHtml->makeOption($this->right->getAssignment($user),$items['from1']);
//            }else
//                $param['hide']=true;
//            $param['rows2']="";
//            foreach ($departments as $department)
//                $param['rows2'].=$this->printHtml->outDepartmentTree($department['id_parent'],0,"",$user);
//            $param['rows3']=$param['rows2'];
//            $param['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
//            $param['type']='new';
//
//            $select=array_merge(['body'=>$this->templateHome->parse($this->modules_root . "edms/tpl/docs/".$this->tpl(),
//                array_merge($result,$items,$param)), 'user'=>$user, 'close'=>true], $result);
//
//            $create['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/select.tpl", $select);
//
//            $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/top.tpl", $this->printHtml->makeHeader($user));
//            $all['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/create.tpl", $create);
//
//            $result['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);*/
//
//        }elseif (isset($items['ernd'])){
//            /*$errand['user']=$user;
//            $errand=$this->docHome->getAllInformation($items['id']);
//            $errand['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
//            PrintHTMLHome::set($this->right->getDepartmentTree($user));
//            $departments=UserHome::getUserDepartment($user);
//            $errand['rows2']=$temp['rows3']="";
//            foreach ($departments as $department){
//                $errand['rows2'].=$this->printHtml->outDepartmentTree($department['id_parent'],0,"",$user,null,$items['id_user1']);
//                $errand['rows3'].=$this->printHtml->outDepartmentTree($department['id_parent'],0,"",$user,null,$items['id_watcher1']);
//            }
//            $errand['rows4'].=$this->printHtml->outResponsible($items['id_user1'],$items['id_resp']);
//
//            $errand=array_merge($result,$errand,$items);
//
//
//            $modal['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/errand.tpl", $errand);
//            $modal['title']="Поручение";
//            $result['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/modal.tpl", $modal);*/
//            $result=$this->action->showErrand($items['id'], $user,array_merge($result,$items));
//        }
        return $result;
    }

    public function show($id, $user){
        $param['mode'] = false;
        //установление флага 'Прочитано'
        if (!$this->docHome->isRead($id) && $this->docHome->isItToMe($id, $user))
            $this->docSave->setRead($id);
        //установление флага 'Изменения просмотрены'
        if ($this->docHome->isItToMe($id, $user))
            $this->docSave->setNotChanged($id,$user);

        $temp = $this->docHome->getOneDocById($id);
        $text=$this->docHome->getText($id);
        unset($text['id']);
        $temp = array_merge($temp, $text);
        //если мне/не от меня и не мне/закрыто то закрыть для редактирования
        if ((!isset($temp['id_user']) && $this->docHome->checkChildIsRead($id))||$this->docHome->isItToMe($id, $user) || (!$this->docHome->isItToMe($id, $user) &&
                !$this->docHome->isItFromMe($id, $user)) || $this->docHome->isClosed($id)||($this->docHome->isItFromMe($id, $user)&&$this->docHome->isRead($id))) {
            $temp['disabled'] = true;
            $param['mode'] = true;
        }
        $param['buttons'] = $this->buttons($temp, $user);

        $temp['rows2'] = !isset($temp['id_user'])?$this->printHtml->outListUser(DocumentationHome::getUsers($id)):$this->printHtml->outOneUser($temp['id_user']);
        $temp['rows3'] = $this->printHtml->outListUser(DocumentationHome::getWatchers($id));
        $temp['rows4'] = $this->printHtml->outOneUser($this->docHome->checkResponsible($id));

        $temp['id'] = $id;
        $temp['rows1'] = $this->printHtml->outOneUser($temp['id_owner']);
        $temp=array_merge($temp,['type'=>'edit','not_mult'=>true,'hide'=>true]);
        if ($temp['is_tracked'] != 1)
            $temp['checked'] = true;
        $temp['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
        if($this->docHome->getDocType($this->docHome->getParent($id))!=1&&$this->docHome->getDocType($this->docHome->getParent($id))!=null){
            $sel=$this->docHome->getText($this->docHome->getParent($id))['name'];
            $param['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/documents/select.tpl",
                ['body' => $this->templateHome->parse($this->modules_root . "edms/tpl/docs/" . $this->tpl(), $temp), 'name' => $sel, 'user' => $user,'disabled'=>true]);
        }else
            $param['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs/" . $this->tpl(), $temp);
        $temp['table'] = $this->printHtml->makeHistoryList($id, $user);
        //Формирование списка прикрепленных файлов
        $param['files'] = $this->printHtml->getListOfFiles($id, $param['mode']);
        //Формирование списка истории
        $param['history'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/history.tpl", $temp);

        $all['content']= $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/show_card.tpl", $param);
        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/top.tpl", $this->printHtml->makeHeader($user));

        $start['usersa'] = $user;
        $start['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    public function showCreate($id, $user)
    {
        if($id!=''){
            $items=$this->docHome->getOneDocById($id);
            $temp['rows0'] = $this->printHtml->makeOption($this->docHome->getName($id),$id);
            $sel=$this->printHtml->makeOption($this->docHome->getOneDocType($items['id_type']), $items['id_type']);
        }else{
            $temp['hide']=true;
            $sel=$this->printHtml->makeOption($this->docHome->getOneDocType(1), 1);
        }
        $temp['rows1'] = $this->printHtml->makeOption($this->right->getAssignment($user), $user);
        $temp['rows3'] = $temp['rows2'] = $this->printHtml->makeDepartmentTree($user);
        $temp['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
        $temp['type'] = 'new';
        $temp['user']=$user;
        $t['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/select.tpl", ['body' => $this->templateHome->parse($this->modules_root . "edms/tpl/docs/" . $this->tpl(), $temp),
            'rows1' => $sel, 'user' => $user,'close'=>true]);
        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/top.tpl", $this->printHtml->makeHeader($user));
        $all['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/create.tpl", $t);
        $result['user']=$user;
        $result['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $result);
    }

    /**
     * Сохранение поручения или перепоручения
     * @param $user - ИД пользователя, создающего задачу
     * @param $params - параметры (текст, тип, дата окончания)
     * @param $files - массив, прикрепленных файлов
     * @throws Exception
     */
    public function save($user, $params, $files){
        if ($params['id']!='')
            $doc = $this->docHome->getOneDocById($params['id']);
        $text = $this->docSave->saveText($params);
        $saveFiles=$this->docSave->saveFiles($files, $text);
        $params['date_create']=$params['date'] = time();
        $params['date_end'] = UtilHome::getTimeStamp($params['date_end']);
        $params['id_text'] = $text;
        $params['id_resp']=UserHome::getUserFromExecution(isset($params['supp'])?$params['id_user']:$params['id_resp']);
        $users = !isset($params['id_user1'])?$params['id_user']:$params['id_user1'];
        $watchers = !isset($params['id_watcher1'])?$params['id_watcher']:$params['id_watcher1'];
        if (isset($params['new'])) {
            if($params['id_prot']!=null)
                $params['id_parent'] = $params['id_prot'];
            $params['code']=$params['id_prot']!=null?$this->docHome->getCode($params['id_prot']):$this->docSave->saveMainDoc();
            $params['id_owner'] = $params['from'];
            unset($params['id_user'],$params['id_watcher']);
        } else {
            $params['id_type'] = 1;
            $params['id_owner'] = $user;
            $params['is_tracked'] = $params['is_tracked1'];
            $params['id_parent'] = $doc['id'];
            $params['code'] = $doc['code'];
        }
        if ($params['from'] != $user&&!isset($params['ernd'])&&$doc['id_user']!=$user)
            $params['id_operator'] = $user;

        if (count($users) > 1) {
            $params['id_parent'] = $this->docSave->saveDocFlow($params);
            $params['type']=4;
        }else
            $params['type']=10;
        if (is_array($users)) {
            foreach ($users as $key => $value) {
                $params['id_user'] =UserHome::getUserFromExecution($value);
                $textId = $this->docSave->saveText($params);
                $this->docSave->saveCopy($textId, $saveFiles);
                $params['id_text']=$textId;
                $params['is_responsible']=($params['id_user']==$params['id_resp']?1:0);
                $a=$this->docSave->saveDocFlow($params);
                if (is_array($watchers)) {
                    foreach ($watchers as $key2 => $watcher) {
                        $params['id_watcher'] =UserHome::getUserFromExecution($watcher);
                        $this->docSave->saveWatcher($a,$params['id_watcher'], $watcher);
                        $this->docSave->setChanged($a,$user);
                    }
                }
            }
        }
    }

    public function buttons($doc,$user){
        $result="";
        if($doc['is_closed']==0) {
            if ($doc['id_owner'] == $user || $doc['id_user'] == $user || $doc['id_operator'] == $user) {
                if($doc['id_owner'] == $user || $doc['id_operator'] == $user){
                    if ($doc['type'] == 4||$doc['type'] ==10)
                        $result .= "<button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-block\" onclick='Supplement(" . $doc['id'] . "," . $doc['id_user'] . ")'>Дополнить задачу</button>";
                    $result .= "<button type=\"button\" class=\"btn btn-outline-success btn-sm btn-block\" onclick='Close(" . $doc['id'] . "," . $user . ",2)'>Завершить</button>";
                    if (!$this->printHtml->checkChildIsRead($doc['id']))
                        $result .= "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm btn-block\" onclick='CloseAll(" . $doc['id'] . ", this)'>Отменить процесс</button>";
                }elseif($doc['id_user'] == $user){
                    $result .= "<button type=\"button\" class=\"btn btn-outline-success btn-sm btn-block\" onclick='Close(" . $doc['id'] . "," . $user . ",1)'>Завершить</button>
                                <button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-block\" onclick='Errand(" . $doc['id'] . "," . $user . ")'>Поручить</button>";
                    if ($doc['date_show'] != "" && $doc['date_show'] != null)
                        $result .= "<button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-block\" onclick='Extension(" . $doc['id'] . ")'>Запросить продление</button>";
                }
            }
        }
        return $result;
    }

    public function preview($id, $user)
    {
        if (!$this->docHome->isRead($id) && $this->docHome->isItToMe($id, $user))
            $this->docSave->setRead($id);
        $this->docSave->setNotChanged($id,$user);
        $doc = $this->docHome->getOneDocById($id);
        $temp=$this->docHome->getPreview($id);
        if ($this->docHome->isExtension($id)) {
            $temp['date_end']=$doc['date_end'];
            $temp['ext']=true;
        }
        $temp['btn'] = $this->buttons($doc, $user);
        $temp['files'] = $this->printHtml->getListOfFiles($id,true);
        $result['content']=$this->templateHome->parse($this->modules_root."edms/tpl/docs_card/preview.tpl",$temp);
        return $result;
    }
}