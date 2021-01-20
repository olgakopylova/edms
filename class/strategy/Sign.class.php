<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
require_once($modules_root."edms/class/SaveHome.class.php");
require_once($modules_root."edms/class/strategy/IStrategy.php");
require_once($modules_root."edms/class/ValidationHome.class.php");
require_once($modules_root."edms/class/RightsHome.class.php");
require_once($modules_root."edms/class/PrintHTMLHome.class.php");
class Sign  implements IStrategy
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
        return "signing.tpl";
    }

    public function check($items, $user){
        if ($items['sign'])
            $result['errors_none']=true;
        elseif(!isset($items['mode'])) {
            $signHome = new SignHome();
            $sign_param = $signHome->checkSignCookie($user);
            $cName = 'srfhh' . $user;
            if ($sign_param && $items['pin']) {

                $params = array(
                    'pin' => $items['pin'],
                    'id_user' => $user,
                    'hashmd' => $items['COOKIE'][$cName]
                );
                $opts = array(
                    'http'=>array(
                        'method'=>"POST",
                        'content' => http_build_query($params)
                    )
                );
                $context = stream_context_create($opts);
                $param = file_get_contents('https://lk.pnzgu.ru/ajax/sign/getmd/', false, $context);
                $decode_param = json_decode($param);
                if($decode_param->{'hash'}) {
                    $result['errors_none']=true;
                } else {
                    $result['errors'] = "Введен неверный pin-код. Обратитесь к администратору или попробуйте снова";
                }
            }elseif(!isset($items['COOKIE'][$cName]))
                $result['errors']="Не установлена подпись";
        }else
            $result['errors_none']=true;
        if($result['errors']){
            $temp['id']=$items['id'];
            $temp['n']="name_cl";
            $temp['t']="text";
            $temp['mode']=$items['mode'];
            if($temp['mode']==0)
                $temp['hide']=true;
            $temp=array_merge($temp,$result);
            $temp['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
            $modal['title']='Подписание';
            $modal['body']=$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/sign.tpl", $temp);
            $result['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/modal.tpl", $modal);
        }
        return $result;
    }

    public function show($id,$user){
        $doc=$this->docHome->getOneDocById($id);
        $text= $this->docHome->getText($id);
        unset($text['id']);

        $temp = array_merge($doc,$text);
        $param['mode'] = false;

        if (!$this->docHome->isRead($id) && $this->docHome->isItToMe($id, $user))
            $this->docSave->setRead($id);
        if ($this->docHome->isItToMe($id, $user))
            $this->docSave->setNotChanged($id,$user);
        if($doc['id_parent']==0)
            $document=$doc['id'];
        else{
            $parent=$this->docHome->getOneDocById($this->docHome->getParent($doc['id']));
            $document=$parent['id_parent']==0?$parent['id']:$this->docHome->getParent($parent['id']);
        }
        $temp['lock'] = true;
        $temp['id_doc']=$id;
        $temp['docName']=$doc['name'];
        $temp['rows0'] = $this->printHtml->makeOption($this->docHome->getDocumentName($document),$document);//должен быть документ который подписывается
        $temp['rows5'] = $this->printHtml->makeOption($this->docHome->getOneDocType(5),5);
        $temp['rows1'] = $this->printHtml->makeOption($this->right->getAssignment($doc['id_owner']), $doc['id_owner']);
        if ($this->docHome->isItToMe($id, $user) || (!$this->docHome->isItToMe($id, $user) && !$this->docHome->isItFromMe($id, $user)) || $this->docHome->isClosed($id)) {
            $temp['disabled'] = true;
            $param['mode'] = true;
        } elseif ((!isset($doc['id_user']) && $this->docHome->checkChildIsRead($id)) /*|| (isset($temp['id_user']) && ($temp['id_parent'] != 0 || $this->docHome->isRead($docId)))*/) {
            $temp['disabled'] = true;
            $param['mode'] = true;
        }
        $temp['rows2'] = !isset($temp['id_user'])?$this->printHtml->outListUser(DocumentationHome::getUsers($id)):$this->printHtml->outOneUser($temp['id_user']);$temp['rows3'] = $this->printHtml->outListUser(DocumentationHome::getWatchers($id));
        $temp['rows4'] = $this->printHtml->outOneUser($this->docHome->checkResponsible($id));
        if($doc['is_consistent']==1)
            $temp["checked"]=true;
        $temp['addFile'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []);
        $temp['type'] = 'sign';
        $temp['user']=$user;


        $history['table'] = $this->printHtml->makeHistoryList($id,$user);
        $param['history'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/history.tpl", $history);
        $param['body']=$this->templateHome->parse($this->modules_root . "edms/tpl/docs/".$this->tpl(), $temp);
        $param['buttons']=$this->buttons($doc,$user);
        $param['files']=$this->printHtml->getListOfFiles($id, $param['mode']);

        $all['content']= $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/show_card.tpl", $param);
        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/top.tpl", $this->printHtml->makeHeader($user));
        $create['user'] = $user;
        $create['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $create);

    }

    public function showCreate($id, $user)
    {
        $dep=$this->printHtml->makeDepartmentTree($user);
        $param=['rows0'=>$this->printHtml->makeOption($this->docHome->getName($id),$id),
            'rows1'=>$this->printHtml->makeOption($this->right->getAssignment($user), $user),
            'rows2'=>$dep,
            'rows3'=>$dep,
            'rows5'=>$this->printHtml->makeOption($this->docHome->getOneDocType(5),5),
            'addFile'=>$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/add_file.tpl", []),
            'type'=> 'sign',
            'user'=>$user];
        $create['body'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs/".$this->tpl(), $param);

        $create['sign']=true;
        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/top.tpl", $this->printHtml->makeHeader($user));
        $all['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/create.tpl", $create);
        $start['user'] = $user;
        $start['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    /**
     * Сохранение запроса подписания
     * @param $user
     * @param $params
     * @param $files
     */
    public function save($user, $params, $files){
        if ($params['id']!='')
            $doc = $this->docHome->getOneDocById($params['id']);
        if($params['id']==""){
            $text = $this->docSave->saveText($params);
            $this->docSave->saveFiles($files, $text);

            if ($params['from'] != $user&&$doc['id_user']!=$user)
                $params['id_operator'] = $user;
            $params['date_create']=$params['date'] = time();
            $params['id_text'] = $text;
            $params['id_resp']=UserHome::getUserFromExecution($params['id_resp']);
            if($params['id_prot']!=null)
                $params['id_parent'] = $params['id_prot'];
            $params['code'] = $params['id_prot']!=null?$this->docHome->getCode($params['id_prot']):$this->docSave->saveMainDoc();
            $params['id_owner'] = $params['from'];
            $users = $params['id_user'];
            $watchers = $params['id_watcher'];

            unset($params['id_user'],$params['id_watcher']);
            if (count($users) > 1) {
                $params['id_parent'] = $this->docSave->saveDocFlow($params);
                $this->docSave->setChanged($params['id_parent'],$user);
                $params['type']=4;
            }
            if (is_array($users)) {
                foreach ($users as $key => $value) {
                    $params['id_user'] =UserHome::getUserFromExecution($value);
                    if($params['id_user']==$params['id_resp'])
                        $params['is_responsible']=1;
                    else
                        unset($params['is_responsible']);
                    $a=$this->docSave->saveDocFlow($params);
                    if (is_array($watchers)) {
                        foreach ($watchers as $key2 => $value2) {
                            $params['id_watcher'] =UserHome::getUserFromExecution($value2);
                            $this->docSave->saveWatcher($a,$params['id_watcher'], $value2);
                            $this->docSave->setChanged($a,$user);
                        }
                    }
                }
            }
            /*else{
                $params['is_responsible']=1;
                $params['id_watcher'] = UserHome::getUserFromExecution($params['id_watcher1']);
                $a=$this->docSave->saveDocFlow($params);
                $this->docSave->saveWatcher($a,$params['id_watcher'], $params['id_watcher1']);
                $this->docSave->setChanged($a,$user);
            }*/
        }
    }

    public function buttons($doc,$user){
        $result="";
        if($doc['is_closed']==0) {
            if ($doc['id_owner'] == $user || $doc['id_user'] == $user|| $doc['id_operator'] == $user){
                if ($doc['id_user'] == $user) {//если документ на подписание направлен мне
                    if (($doc['is_consistent']==1&&$doc['is_responsible']==1&&$this->docHome->allSignChildIsClose($doc['id_parent']))||$doc['is_consistent']==0) {
                        $result .= "<button type=\"button\" class=\"btn btn-outline-success btn-sm btn-block\" onclick='Sign(1," . $doc['id'] . ",this," . $user . ")'>Подписать</button>";
                        $result .= "<button type=\"button\" class=\"btn btn-outline-danger btn-sm btn-block\" onclick='Sign(0," . $doc['id'] . ",this," . $user . ")'>Отклонить</button>";
                        $result .= "<button type=\"button\" class=\"btn btn-outline-primary btn-sm btn-block\" onclick='Errand(" . $doc['id'] . "," . $user . ")'>Поручить</button>";
                    }else
                        $result="<div class=\"alert alert-info\" role=\"alert\">Подписание возможно после остальных согласующих</div>";
                }elseif ($doc['id_owner']== $user||$doc['id_operator']== $user){//если документ отправлен от меня
                    if(!isset($doc['date_show']))//если это отдельный запрос на каждого пользователя, то если он не прочитал, то можно отменить его
                        $result.="<button type=\"button\" class=\"btn btn-outline-danger btn-sm btn-block\" onclick='CloseAll(".$doc['id'].", this)'>Отменить процесс</button>";
                }
            }
        }
        return $result;
    }

    public function preview($id, $user)
    {
        if (!$this->docHome->isRead($id) && $this->docHome->isItToMe($id, $user))
            $this->docSave->setRead($id);
        if ($this->docHome->isItToMe($id, $user))
            $this->docSave->setNotChanged($id,$user);
        $doc = $this->docHome->getOneDocById($id);
        $temp=$this->docHome->getPreview($id);
        if (!$this->docHome->isRead($id) && $this->docHome->isItToMe($id, $user))
            $this->docSave->setRead($id);
        $temp['btn'] = $this->buttons($doc, $user);
        $temp['files'] = $this->printHtml->getListOfFiles($id,true);
        $result['content']=$this->templateHome->parse($this->modules_root."edms/tpl/docs_card/preview.tpl",$temp);
        return $result;
    }
}