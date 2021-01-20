<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
require_once($modules_root."edms/class/SaveHome.class.php");
require_once($modules_root."edms/class/strategy/IStrategy.php");
require_once($modules_root."edms/class/ValidationHome.class.php");
require_once($modules_root."edms/class/RightsHome.class.php");
require_once($modules_root."edms/class/PrintHTMLHome.class.php");
class Document  implements IStrategy
{
    private $check;
    private $right;
    private $printHtml;
    private $templateHome;
    private $modules_root;
    private $docHome;
    private $docSave;
    public function  __construct(){
        $this->check=new ValidationHome();
        $this->right=new RightsHome();
        $this->printHtml=new PrintHTMLHome();
        $this->templateHome=$GLOBALS['templateHome'];
        $this->modules_root=$GLOBALS['modules_root'];
        $this->docHome=new DocumentationHome();
        $this->docSave=new SaveHome();
    }
    public function tpl(){
        return "document.tpl";
    }

    public function check($items, $user){
        $result['errors_none']=true;
        return $result;
    }

    public function show($id,$user){
        $doc=$this->docHome->getOneDocById($id);
        $text=$this->docHome->getText($id);
        unset($text['id']);
        $doc=array_merge($doc, $text);
        $name=UserHome::getFIO($doc['id_owner']).', '.UserHome::getShortPosition($doc['id_owner']);
        $show['buttons']=$this->buttons($doc,$user);

        if(($this->docHome->haveChild($id)&&($doc['id_owner']==$user||$doc['id_operator']==$user))||($doc['id_owner']!=$user&&$doc['id_operator']!=$user)){
            $show['disabled']=$doc['disabled']=true;
            $show['files'] = $this->printHtml->getListOfFiles($id,true);
        }else{
            $show['files'] = $this->printHtml->getListOfFiles($id,false);
            $doc['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
        }

        /*//если документ подписан, то отарывать присвоение номера (убрано)
        if($doc['is_signed']==1&&$this->docHome->isMainSign($user,$id))
            $doc['num_show']=true;*/
        $doc['type']='edit';
        $doc['rows1']=$this->printHtml->makeOption([['id'=>$doc['id_owner'],'name'=>$name]],$doc['id_owner']);
        $doc['show']=true;
        if(/*!$this->right->canSet()||*/$doc["id_owner"]!=$user&&$doc["id_operator"]!=$user)
            $doc['hide']=true;

        $show['table']=$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/history.tpl",
            ['table'=>$this->printHtml->makeErrandForDoc($id, $user)]);
        $show['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/select.tpl",
            ['body' => $this->templateHome->parse($this->modules_root . "edms/tpl/docs/".$this->tpl(), $doc),
            'rows1' => $this->printHtml->makeOption($this->docHome->getOneDocType($doc['id_type']),$doc['id_type']),
                'user' => ($user), 'disabled'=>true]);

        if($doc["id_owner"]==$user||$doc["id_operator"]==$user||$doc['num_show'])
            unset($show['disabled']);
        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/top.tpl", $this->printHtml->makeHeader($user));
        $all['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/show_document.tpl", $show);
        $start['user']=$user;
        $start['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    /**
     * Редактирование/сохранение документа
     * @param $user - ИД пользователя
     * @param $items - параметы
     * @param $files - массив файлов
     * @throws Exception
     */
    public function save($user, $items, $files){
        if($items['id']!=""){
            $doc=$this->docHome->getOneDocById($items['id']);
            if(isset($items['name'])&&isset($items['text']))
                $this->docSave->update(['name'=>$items['name'],'text'=>$items['text']],'text',$doc['id_text']);
            if($items['date_create']!=""){
                $items['date_create']=$items['date_create'].' '.date('H').':'.date('i').':'.date('s');
                $temp['date_create']=UtilHome::getTimeStamp($items['date_create']);
            }
            if(isset($items['number']))
                $temp['number']=$items['number'];
            if(count($temp)!=0)
                $this->docSave->update($temp,'docs_flow',$doc['id']);

            $this->docSave->deleteFiles($items['scans'], $doc['id']);
            $this->docSave->saveFiles($files, $doc['id_text']);
        }else{
            $params=array('id_type'=>$items['id_type'], 'code'=>$this->docSave->saveMainDoc(), 'id_text'=>$this->docSave->saveText($items), 'id_owner'=>$items['id_user'],
                'date'=>time());
            if($items['id_user']!=$user)
                $params['id_operator']=$user;
            $this->docSave->saveFiles($files, $params['id_text']);
            $this->docSave->saveDocFlow($params);
        }
    }

    public function showCreate($id, $user)
    {
        $param['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
        $param['date_first']=date("Y-m-d",time());
        //$name=UserHome::getFIO($this->result['user']).', '.UserHome::getShortPosition($userId);
        $param['rows1'] = $this->printHtml->makeOption($this->right->getAssignment($user), $user);
        $create['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/select.tpl", ['body' => $this->templateHome->parse(
            $this->modules_root . "edms/tpl/docs/document.tpl", $param),
            'rows1' => $this->printHtml->makeOption($this->docHome->getAllTypes(),2), 'user' => ($user)]);
        $start['user'] = $user;
        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/top.tpl", $this->printHtml->makeHeader($user));
        $all['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/create.tpl", $create);
        $start['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    public function buttons($doc,$user){
        $result="<button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-block\" onclick='Errand(".$doc['id'].",".$user.")'>Поручить</button>";
        return $result;
    }

    public function preview($id, $user)
    {
        $doc = $this->docHome->getOneDocById($id);
        $param = $this->docHome->getPreview($id);
        //Если задача не прочитана и для текущего пользователя, то выставлять флаг прочитано
        if (!$this->docHome->isRead($id) && $this->docHome->isItToMe($id, $user))
            $this->docSave->setRead($id);
        if($doc['is_signed']==1) {
            $param['sgn']="Статус: <font color='#27AE60'> ПОДПИСАНО</font>";
            $param['mainsgn']=$this->docHome->getMainSignTitle($id);
            $param['other']=$this->docHome->getOtherSign($id);
            $param['filesgn']="<a href=\"/edms/doc_sign/?id_doc=".$id."\" traget=\"_blank\" >Подписи</a>";
        } elseif($this->docHome->isActiveSigning($doc['id']))
            $param['sgn']="Статус: НА ПОДПИСАНИИ";
        $param['btn'] = $this->buttons($doc,$user);
        $param['files'] = $this->printHtml->getListOfFiles($id,true);
        $result['content']=$this->templateHome->parse($this->modules_root."edms/tpl/docs_card/preview.tpl",$param);
        return $result;
    }
}