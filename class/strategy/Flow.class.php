<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
require_once($modules_root."edms/class/SaveHome.class.php");
require_once($modules_root."edms/class/strategy/IStrategy.php");
require_once($modules_root."edms/class/strategy/DocFlowHome.class.php");
require_once($modules_root."edms/class/PrintHTMLHome.class.php");
require_once($modules_root."edms/class/ValidationHome.class.php");
class Flow implements IStrategy
{
    private $check;
    private $templateHome;
    private $modules_root;
    private $printHtml;
    private $docHome;
    private $docSave;
    private $action;
    private $extension=[2,6,7];
    private $closing=[1,3,5];
    public function  __construct(){
        $this->check=new ValidationHome();
        $this->templateHome=$GLOBALS['templateHome'];
        $this->modules_root=$GLOBALS['modules_root'];
        $this->docHome=new DocumentationHome();
        $this->docSave=new SaveHome();
        $this->action=new DocFlowHome();
        $this->printHtml=new PrintHTMLHome();
    }

    public function tpl(){
        return null;
    }

    public function check($items, $user){
        if (isset($items['ext'])){
            $error=$this->check->checkExtension($items);
            $result=array_merge($error,$this->action->showExtension($items['id'],3, array_merge($items,$error)));
        }elseif (isset($items['rev'])){
            $error=$this->check->checkRevision($items);
            $result=array_merge($error,$this->action->showRevision($items['id'], array_merge($items, $error)));
        }elseif (isset($items['closing'])||isset($items['report'])){
            $error=$this->check->checkClosing($items);
            $result=array_merge($error,$this->action->showClose($items['id'], 1,array_merge($items,$error)));
        }elseif(isset($items['suppl'])){
            $error=$this->check->checkSupplement($items);
            $result=array_merge($error,$this->action->showSupplement($items['id'], array_merge($items,$error)));
        }
        return $result;
    }

    public function show($id,$user){
        $temp = $this->docHome->getText($id);
        unset($temp['id']);
        $temp = array_merge($temp, $this->docHome->getOneDocById($id));
        $param['buttons'] = $this->buttons($temp, $user);
        $type=$this->docHome->getFlowType($id);
        if($type==2){
            $temp['type'] = "ext";
            if ($this->docHome->isItToMe($id, $user)) {
                if ($this->docHome->isClosed($id))
                    $temp['close'] = true;
                else
                    $temp['edit'] = true;
            } else {
                $temp['close'] = true;
            }
            $param['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/extension.tpl", $temp);
        }else{
            $temp['disabled']=true;
            $param['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/cl_apt_rev.tpl", $temp);
        }

        $param['addFile'] = $this->printHtml->getListOfFiles($id, true);

        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/top.tpl", $this->printHtml->makeHeader($user));
        $all['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/show_2.tpl", $param);

        $start['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    public function save($user, $params, $files){
        $doc = $this->docHome->getOneDocById($params['id']);
        if (isset($params['ext']) || isset($params['rev'])) {
            $this->action->saveExtensionOrRevision($doc,$user,$params, $files);
        }elseif (isset($params['closing'])||isset($params['report'])) {
            $this->action->saveClose($params['id'],$user,$params,$files);
            if(isset($params['report']))
                $this->action->saveReport($params['id'],$user,$params,$files);
        }elseif (isset($params['suppl'])){
            $this->action->saveSupplement($user,$params,$files);
        }
        elseif(isset($params['sign'])){
            $this->action->saveAnswerSign($user,$params,$files);
        }
    }

    public function buttons($doc,$user){
        $result="";
        if($doc['is_closed']==0) {
            if ($doc['id_owner'] == $user || $doc['id_user'] == $user || $doc['id_operator'] == $user) {
                switch ($doc['type']) {
                    case 2://запрос продления
                        if ($doc['id_user'] == $user) {//запрос мне
                            $result = "<button type=\"button\" class=\"btn btn-outline-success btn-sm btn-block\" onclick='Answer(1," . $doc['id'] . ",this," . $user . ")'>Подтвердить</button>";
                            $result .= "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm btn-block\" onclick='Answer(0," . $doc['id'] . ",this," . $user . ")'>Отклонить</button>";
                        } else//запрос от меня
                            $result = "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm btn-block\" onclick='Answer(2," . $doc['id'] . ",this," . $user . ")'>Отменить запрос</button>";
                        break;
                    case 3://запрос на закрытие
                        if ($doc['id_user'] == $user) {
                            $result = "<button type=\"button\" class=\"btn btn-outline-success btn-sm btn-block\" onclick='Close(" . $doc['id'] . "," . $user . ")'>Принять</button>";
                            if ($this->printHtml->haveLevelsAbove( $doc['id']))//дополнить условие
                                $result .= "<button type=\"button\" class=\"btn btn-outline-success btn-sm btn-block\" onclick='Close(" . $doc['id'] . "," . $user . ",3)'>Принять с отчетом</button>";
                            $result .= "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm btn-block\" onclick='Revision(" . $doc['id'] . "," . $user . ", this)'>Отказать</button>";
                        } elseif ($doc['id_owner'] == $user || $doc['id_operator'] == $user)//от меня
                            $result .= "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm btn-block\" onclick='CloseAll(" . $doc['id'] . ", this)'>Отменить процесс</button>";
                        break;
                    /*default:
                        if($doc['type']){
                            if ($doc['id_owner'] == $user || $doc['id_operator'] == $user) {
                                if ($doc['type'] == 4)
                                    $result .= "<button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-block\" onclick='Supplement(" . $doc['id'] . "," . $doc['id_user'] . ")'>Дополнить задачу</button>";
                                $result .= "<button type=\"button\" class=\"btn btn-outline-success btn-sm btn-block\" onclick='Close(" . $doc['id'] . "," . $user . ",2)'>Завершить</button>";
                                if (!$this->docHome->checkChildIsRead($doc['id']))
                                    $result .= "<button type=\"button\" class=\"btn btn-outline-secondary btn-sm btn-block\" onclick='CloseAll(" . $doc['id'] . ", this)'>Отменить процесс</button>";
                            } elseif ($doc['id_user'] == $user) {//мне
                                $result .= "<button type=\"button\" class=\"btn btn-outline-success btn-sm btn-block\" onclick='Close(" . $doc['id'] . "," . $user . ",1)'>Завершить</button>
                                <button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-block\" onclick='Errand(" . $doc['id'] . "," . $user. ")'>Поручить</button>";
                                if ($doc['date_show'] != "" && $doc['date_show'] != null)
                                    $result .= "<button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-block\" onclick='Extension(" . $doc['id'] . ")'>Запросить продление</button>";
                            }
                        }
                        break;*/
                }
            }
        }
        return $result;
    }

    public function showCreate($id,$user){

    }

    public function preview($id,$user){
        if (!$this->docHome->isRead($id) && $this->docHome->isItToMe($id,$user))
            $this->docSave->setRead($id);
        $this->docSave->setNotChanged($id,$user);
        $doc = $this->docHome->getOneDocById($id);
        $preview=$this->docHome->getPreview($id);
        $type=$this->docHome->getFlowType($id);
        if(array_search($type,$this->extension)!==false){
            $preview['date_end']=$doc['date_end'];
            $preview['ext']=true;
            $preview['btn'] = $this->buttons($doc, $user);
            $preview['files'] = $this->printHtml->getListOfFiles($id,true);
            $result['content']=$this->templateHome->parse($this->modules_root."edms/tpl/docs_card/preview.tpl",$preview);
        }elseif(array_search($type,$this->closing)!==false){
            $preview['btn'] = $this->buttons($doc, $user);
            $preview['files'] = $this->printHtml->getListOfFiles($id,true);
            $result['content']=$this->templateHome->parse($this->modules_root."edms/tpl/docs_card/preview.tpl",$preview);
        }
        return $result;
    }

}