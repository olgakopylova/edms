<?php
require_once($modules_root."edms/class/UserHome.class.php");
require_once($modules_root."edms/class/RightsHome.class.php");
require_once($modules_root."edms/class/Paginate.php");

class PrintHTMLHome extends DocumentationHome {
    public static $departmentTree=array();
    private $bookmark="<button type='button' id='more' title='Есть запрос без ответа' class='btn' aria-hidden=\"true\"><i class=\"fa fa-bookmark\" aria-hidden=\"true\"></i></button>";
    /**
     * Инфо для заголовка
     * @param $id - ИД пользователя
     * @return array
     */
    public function makeHeader($id){
        return ['user'=>$id,'fio'=>UserHome::getFIO($id)];
    }

    /**
     * Генерация страницы со всеми документами
     * @param $user - ИД пользователя
     * @return mixed
     */
    public function getAllDocuments($user){
        $table['table']=$this->makeTable($this->makeDocumentsList($user),'documents/row.tpl');
        $all['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/documents/table.tpl", $table);
        $top=array_merge($this->makeHeader($user),['rows1'=>$this->makeOption($this->getAllTypes())]);
        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/doc_top.tpl", $top);
        $start['user']=$user;
        $start['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    /**
     * Генерация страницы поручений
     * @param $user - ИД пользователя
     * @return mixed
     */
    public function getAllErrands($user)
    {
        $navi = new Paginate();
        $result = $this->getAllWithFilter($user);

        $count=$result['count'];
        $tree['tree']=$this->makeErrandTree($result);

        $main['user']=$user;
        $main['pagination']=$navi->build( 10, $count, 1 );
        $main['tree'] = $this->templateHome->parse($this->modules_root . "edms/tpl/tree.tpl", $tree);
        $main=array_merge($main,$this->getCount($user));

        $all['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/errands_main.tpl", $main);
        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/main_top.tpl", $this->makeHeader($user));

        $start['user']=$user;
        $start['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    /**
     * Генерация списка задач
     * @param $items - Массив веток задач
     * @return string
     */
    private function makeErrandTree($items){
        $tree="<ul>";
        unset($items['count']);
        foreach ($items as $key=>$value)
            $tree.="<li>".$value."</li>";
        $tree.="</ul>";
        return $tree;
    }

    /**
     * Генерация таблиц замещений и заместителей пользователя
     * @param $user - ИД пользователя
     * @return mixed
     */
    public function getAllAssignments($user){
        $tables['table2']=$this->makeTable(RightsHome::assignmentToMe($user),'assignment/row.tpl');
        $tables['table1']=$this->makeAssignmentTable(RightsHome::assignmentFromMe($user),$user);

        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/user_top.tpl", $this->makeHeader($user));
        $all['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/assignment/table.tpl", $tables);

        $start['user']=$user;
        $start['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    /**
     * Генерация формы создания нового или отобращения существующего замещения
     * @param $user - ИД пользователя
     * @param $id - ИД замещения (если новое замещение id=null)
     * @return mixed
     * @throws Exception
     */
    public function showAssignment($user,$id){
        if($id!=null){
            $items=RightsHome::getOneAssignment($id);
            $items['rows1'] = $this->outOneUser($items['id_boss']);
            $items['rows2'] = $this->outOneUser($items['id_alternate']);
            $items['disabled']=true;
        }else{
            $items['rows1'] = $this->outOneUser($user);
            $items['rows2']=$this->makeDepartmentTree($user);
        }
        $items['type'] = 'rights';

        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/top.tpl", $this->makeHeader($user));
        $all['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/assignment/assignment.tpl", $items);

        $start['user']=$user;
        $start['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    /**
     * Генерация списка задач на основе документа
     * @param $user - ИД пользователя
     * @param $id - ИД документа
     * @return mixed
     */
    public function getErrandsOfDocument($user,$id){
        $top=array_merge($this->makeHeader($user),['title'=>"<p class=\"h4\">Поручения к документу ".$this->getText($id)['name']."</p>"]);
        $all['top'] = $this->templateHome->parse($this->modules_root . "edms/tpl/top/one_doc_top.tpl", $top);
        $table['history']=$this->templateHome->parse($this->modules_root . "edms/tpl/docs_card/history.tpl",
            ['table'=>$this->makeErrandForDoc($id,$user)]);
        $all['content']=$this->templateHome->parse($this->modules_root . "edms/tpl/documents/list.tpl", $table);

        $start['content'] = $this->templateHome->parse($this->modules_root . "edms/tpl/all.tpl", $all);
        return $this->templateHome->parse($this->modules_root . "edms/tpl/start.tpl", $start);
    }

    /**
     * @param $parent_id - ИД родительской записи
     * @param $level - уровень вложенности
     * @param $text - формируемое дерево
     * @param $user - ИД пользователя
     * @param $mode - Флаг видимости всего внутреннего дерева (если поручение от меня или я наблюдатель)
     * @param $isShow - не используется теперь, но вдруг пригодится
     * @return array
     * @throws Exception
     */
    private function outTree($parent_id, $level,$text, $user,$mode,$isShow) {
        if (isset($this->_tree[$parent_id])) { //Если категория с таким parent_id существует
            $level++;
            foreach ($this->_tree[$parent_id] as $value){
                $my=false;
                //если это последовательное подписание, где главная подпись у текущего пользователя и остальные еще не подписали, то скрываем сущность
                if($value['id_type']==5&&$value['is_consistent']==1&&$value['id_user']==$user&&
                    $value['is_responsible']==1&&!$this->allSignChildIsClose($value['id_parent'])||(!isset($value['id_user'])&&
                        (!$this->allSignChildIsClose($value['id'])&&$value['is_consistent']==1&&$value['id_type']==5&&$this->checkHaveChildToUserIsResponsible($value['id'],$user)))){
                    $s=false;
                    $my=false;
                    $mode=false;
                    $isShow=false;
                    $showDoc=false;
                }
                //если это главное поручение на нескольких человек и на пользователя есть такое поручение
                if((!isset($value['id_user'])&&($this->checkHaveChildToUser($value['id'],$user)||
                        $this->checkHaveChildToWatcher($value['id'],$user)))/*||($isShow&&$value['id_user']==$user)*/)
                    $s=true;
                else
                    $s=false;
                //если это задача от/для/наблюдатель
                if($value['id_owner']==$user||$value['id_operator']==$user||$value['id_user']==$user/*||array_search($value['id_watcher'],$user)!==false */)
                    $my=true;
                //Если сущность - главный документ документ(Служебная записка, протокол и тд)
                if($this->getShowType($value['id_type'])==0&&$value['id_parent']==0)
                    $showDoc=true;

                /** Рекурсивный вызов */
                $temp=$this->outTree($value['id'], $level,"", $user,$my,$s);
                $hide="";

                $options=array();
                $options['docId']=$value['id'];
                $options['userId']=$user;

                /**Выбор цвета для блока*/
                if(!$value['date_show']) $options['option']="style='background-color: #e2e3e5; !important;'";
                $now_date = UtilHome::getNowDate();
                $end=UtilHome::modifyDate($value['date_end'],1,'+','days');
                if($this->getShowType($value['id_type'])==1&&$value['id_type']!=5){//если это не документ
                    if((strtotime($now_date) > strtotime($end))&&$value['is_closed']==0&&($value['type']==0||$value['type']==4||$value['type']==10)){
                        $options['option']="style='background-color: #f8d7da; !important;'";//просрочено-красный
                    }elseif((UtilHome::dateDiff($value['date_end'])<=1)&&$value['is_closed']==0&&
                        /*$value['id_type']!=2&&*/($value['type']==0||$value['type']==4||$value['type']==10))//color-beige
                        $options['option']="style='background-color: #fff3cd; !important;'";//скоро истечет срок-желтый
                }

                /** Генерация подписи исполнителя и инициатора */
                if(($this->getShowType($value['id_type'])!=0&&($value['type']==0||$value['type']==10))||(isset($value['id_user'])&&$level==0)){
                    //если это главное поручение(не документ к которому прикреплено поручение)
                    if(isset($value['id_operator'])||$value['id_user']==$user)
                        $options['more']="Инициатор: <a title='".UserHome::getPosition($value['id_operator'])."'>".UserHome::getFIO($value['id_operator'])."</a> за 
                                         <a title='".UserHome::getPosition($value['id_owner'])."'>".UserHome::getFIO($value['id_owner'])."</a>";
                    elseif(isset($value['id_owner'])||$value['id_user']==$user)
                        $options['more']="Инициатор: <a title='".UserHome::getPosition($value['id_owner'])."'>".UserHome::getFIO($value['id_owner'])."</a>";
                    if(isset($value['id_user']))
                        $options['more'].=", Исполнитель: <a title='".UserHome::getPosition($value['id_user'])."'>" . UserHome::getFIO($value['id_user'])."</a>";
                }elseif($value['type']==4||$this->getShowType($value['id_type'])!=0)
                    $options['more']="Исполнитель: <a title='".UserHome::getPosition($value['id_user'])."'>" . UserHome::getFIO($value['id_user'])."</a>";

                /** Генерация названия */
                if(($value['type']!=0&&$value['type']!=4&&$value['type']!=10)||$value['id_type']!=1)
                    $options['name']=$value['name']." от ".UtilHome::getDateFormat($value['date']);
                else
                    $options['name']=$value['name']." от ".date($this->dateFormat, strtotime($value['date_create']))." до ".date($this->dateFormat, strtotime($value['date_end']));
                if($value['is_closed']==1) $options['name']="<s>".$options['name']."</s>";

                /** скрывает +/- у элементов у которых нет дочернего уровня */
                if ($this->_tree[$value['id']][0]['id']==''||(!$mode&&!$my&&!$showDoc&&!$s/*&&!$isShow*/))
                    $hide="hidden";
                /** Свернула все*/
                $options['collapse']="<i ".$hide." class=\"fa fa-plus\" aria-hidden=\"true\"></i>";
                $options['hide']="style='display:none;'";
                /** Если есть изменения то выделю жирным */
                if(($value['is_changed1']==1&&$value['id_owner']==$user)||($value['is_changed2']==1&&$value['id_user']==$user)||
                    ($value['is_changed3']==1&&$value['id_watcher']==$user)||($value['is_changed4']==1&&$value['id_operator']==$user)||$temp['check']){
                    $options['style']="font-weight: bold;";
                    $temp['check']=true;
                    $buf=true;
                    if($value['id_parent']!=0){
                        $options['collapse']="<i ".$hide." class=\"fa fa-minus\" aria-hidden=\"true\"></i>";
                        $options['hide']="";
                    }
                }

                /** Bookmark должен быть у все ветки  */
                if(($value['id_user']==$user&&$value['is_closed']==0&&!$this->haveChild($value['id']))||$temp['bookmark'] == true) {
                    $temp['bookmark'] = true;
                    $buf2=true;
                    if($value['id_parent']!=0){
                        $options['collapse']="<i ".$hide." class=\"fa fa-minus\" aria-hidden=\"true\"></i>";
                        $options['hide']="";
                    }
                    $options['bookmark'] = $this->bookmark;
                }

                /** Присваивание статуса: документу, запросу */
                if($this->getShowType($value['id_type'])==0){
                    if($value['is_signed']==1)
                        $options['status']="<font color=\"#27AE60\">ПОДПИСАНО</font>";
                    elseif($this->isActiveSigning($value['id']))
                        $options['status']="<font color=\"#909497\">ПОДПИСАНИЕ</font>";
                }

                /** Статус документа после подписания*/
                if($value['id_type']==5){
                    if($value['type']==4&&$value['is_closed']==1&&$value['is_signed'])
                        $options['status']="<font color=\"#27AE60\">ПОДПИСАНО</font>";
                    elseif($value['type']==4&&$value['is_closed']==1&&$value['date_show']!=""&&$value['date_show']!=null)
                        $options['status']="<font color=\"#C0392B\">ОТКЛОНЕНО</font>";
                }

                if($mode||($my&&($value['type']==0||$value['type']==4||$value['type']==10))||$showDoc||$s/*||$isShow*/){
                    $text.=$GLOBALS['templateHome']->parse($GLOBALS['modules_root'] .'edms/tpl/branch.tpl', $options);
                    if ($temp['text'] == "") $text .= "</details></div>";
                    $text .= $temp['text'];
                }elseif ($temp['text'] != ""){
                    $temp['text']=substr($temp['text'], 0, strlen($temp['text'])-16);
                    $text .= $temp['text'];
                }
            }
            $level--; //Уменьшаем уровень вложености
            if($text!=""&&$parent_id!=0)
                $text.="</details></div>";
            if($buf) $temp['check']=true;
            if($buf2) $temp['bookmark']=true;
            return ['check'=>$temp['check'], 'bookmark'=>$temp['bookmark'],'text'=>$text];
        }
    }

    /**
     * Проверка есть ли дочерняя задача от этой общей задачи(на нескольких пользователей)
     * на данного пользователя
     * @param $id - ИД задачи
     * @param $user - ИД пользователя
     * @return bool
     */
    private function checkHaveChildToUser($id,$user){
        if (isset($this->_tree[$id]))
            foreach ($this->_tree[$id] as $value)
                if($value['id_user']==$user) return true;
        return false;
    }

    /**
     * Проверка есть ли дочерняя задача от этой общей задачи(на нескольких пользователей)
     * на данного пользователя и является ли он ответственным
     * @param $id - ИД задачи
     * @param $user - ИД пользователя
     * @return bool
     */
    private function checkHaveChildToUserIsResponsible($id,$user){
        if (isset($this->_tree[$id]))
            foreach ($this->_tree[$id] as $value)
                if($value['id_user']==$user&&$value['is_responsible']) return true;
        return false;
    }

    /**
     * Проверка является ли пользователь наблюдателем для данной задачи
     * @param $id - ИД задачи
     * @param $user - ИД пользователя
     * @return bool
     */
    private function checkHaveChildToWatcher($id,$user){
        if (isset($this->_tree[$id]))
            foreach ($this->_tree[$id] as $value)
                if(array_search($user,$value['id_watcher'])!=false) return true;
        return false;
    }

    public static function set($arr){
        self::$departmentTree=$arr;
    }

    /**
     * @param $parent_id
     * @param $text
     * @param $userId
     * @param $inptText
     * @param $ids
     * @return string
     */
    public function outDepartmentTree($parent_id, $text,$userId,$inptText,$ids=null){
        if (isset(self::$departmentTree[$parent_id])) { //Если категория с таким parent_id существует
            $count=0;
            foreach (self::$departmentTree[$parent_id] as $value) { //Обходим ее
                $users=UserHome::getUsersFromDepartment($value['id'],$userId,$inptText);
                if(count($users)>1){
                    $text.="<option class='group-result' disabled>".$value['name']."</option>";
                    foreach ($users as $user){
                        $temp="";
                        if(is_array($ids)){
                            $temp="";
                            if(in_array($user['id'],$ids)){
                                $temp=" selected ";
                                unset($ids[array_search($user['id'],$ids)]);
                            }
                        }
                        $text.="<option value='".$user['id']."' ".$temp.">".$user['name']."</option>";
                    }
                }
                //Рекурсивно вызываем этот же метод, но с новым $parent_id и $level
                $count++;
                $text.=$this->outDepartmentTree($value['id'],"",$userId,$inptText,$ids);
            }
            return $text;
        }
    }

    /**
     * Генерация одной таблицы замещений
     * @param $items - Массив замещений
     * @param $user - ИД пользователя
     * @return string
     */
    function makeAssignmentTable($items,$user){
        $rows="";
        foreach ($items as $key=>$item){
            $item['id_user']=$user;
            if($item['status']=="Действует")
                $item['btn']=true;
            $rows.=$GLOBALS['templateHome']->parse($GLOBALS['modules_root'] .'edms/tpl/assignment/row.tpl',$item);
        }
        return $rows;
    }

    /**
     * Генерация таблицы
     * @param $items - Массив значений
     * @param $row - Наименование tpl в которой структура строки таблицы
     * @return string
     */
    function makeTable($items,$row){
        $rows="";
        foreach ($items as $key=>$item)
            $rows.=$GLOBALS['templateHome']->parse($GLOBALS['modules_root'] .'edms/tpl/'.$row,$item);
        return $rows;
    }

    /**
     * Генерация option для одного пользователя
     * @param $id - ИД пользователя
     * @return string
     */
    public function outOneUser($id){
        if($id){
            $user=UserHome::getUser($id)[0];
            $text="<option style='padding-left: 30px' value='".$user['id']."' selected>".$user['name']."</option>";
        }else
            $text="<option value='' selected>Не назначен</option>";
        return $text;
    }

    /**
     * Генерация option для нескольких пользователей
     * @param $users - Массив ИД пользователей
     * @return string
     */
    public function outListUser($users){
        $rows='';
        if(count($users)!=0)
            foreach($users as $ket=>$user){
                $u=isset($user['id_user'])?UserHome::getUser($user['id_user'])[0]:(isset($user)?UserHome::getUser($user)[0]:'');
                $rows.="<option style='padding-left: 30px' value='".$u['id']."' selected>".$u['name']."</option>";
            }
        return $rows;
    }

    /**
     * Генерация option для ответственного
     * @param $users - Массив пользователей
     * @param null $id - ИД выбранного пользователя
     * @return string
     */
    public function outResponsible($users,$id=null){
        $text='';
        if($id==null)
            $id=$users[count($users)-1];
        if($users!=0)
            foreach($users as $user)
                $text.="<option style='padding-left: 30px' value='".$user."'".($user==$id?"selected":"").">".UserHome::getUser(UserHome::getUserFromExecution($user))[0]['name']."</option>";
        return $text;
    }

    /**
     * @param $user
     * @param array $filters
     * @return array
     */
    public function getAllWithFilter($user,$filters=['status'=>'new','limit'=>10,'page'=>1]){
        $text="";
        $main=" (docs_flow.id_owner=".$user." OR docs_flow.id_operator=".$user." OR  docs_flow.id_user=".$user." 
                    OR EXISTS(SELECT fw.id FROM flow_watchers fw WHERE fw.is_deleted=0 AND fw.id_flow=docs_flow.id AND fw.id_user=".$user."))";
        if(isset($filters['status'])){
            switch ($filters['status']){
                case 'new'://мне поручили новое,неотписанные,ответы
                    $text.=" WHERE (((docs_flow.id_user=".$user." OR EXISTS(SELECT fw.id FROM flow_watchers fw WHERE fw.is_deleted=0 AND fw.id_flow=docs_flow.id AND fw.id_user=".$user.")) 
                    AND docs_flow.date_show IS NULL AND docs_flow.is_closed=0 and docs_flow.id_type=1)";//новые и не прочитанные мне как исполнителю и наблюдателю
                    $text.=" OR (docs_flow.id_user=".$user." AND ((SELECT COUNT(*) FROM docs_flow d WHERE d.id_parent=docs_flow.id AND d.id_owner=docs_flow.id_user AND d.is_deleted=0)=0 OR 
                    ((SELECT COUNT(*) FROM docs_flow d WHERE d.id_parent=docs_flow.id AND d.id_owner=docs_flow.id_user and d.is_closed=1 AND d.is_deleted=0)>0) AND 
                    (SELECT COUNT(*) FROM docs_flow d WHERE d.id_parent=docs_flow.id AND d.id_owner=docs_flow.id_user AND d.is_deleted=0)=0) AND docs_flow.is_closed=0) 
                    OR (docs_flow.id_user=".$user." AND docs_flow.is_changed2=1) OR (EXISTS(SELECT fw.id FROM flow_watchers fw WHERE fw.is_deleted=0 AND 
                    fw.id_flow=docs_flow.id AND fw.id_user=".$user.") AND docs_flow.is_changed3=1) OR (docs_flow.id_owner=".$user." AND docs_flow.is_changed1=1) OR 
                    (docs_flow.id_operator=".$user." AND docs_flow.is_changed4=1)) ";
                    break;
                case 'all'://все
                    $text.=" WHERE ".$main;
                    break;
                case 'inwork'://в работе
                    $text.=" WHERE ((".$main." AND docs_flow.is_closed=0) OR 
                    (docs_flow.id_user=".$user." AND (SELECT COUNT(*) FROM docs_flow d WHERE d.id_parent=docs_flow.id AND d.id_owner=docs_flow.id_user AND d.is_closed=0 AND d.is_deleted=0))>0) ";
                    break;
                case 'past'://просроченные
                    $text.=" WHERE (".$main." AND docs_flow.type=0 AND docs_flow.date_end<".time()." AND docs_flow.is_closed=0) ";
                    break;
                case 'soon'://скоро станут просроченными
                    $text.=" WHERE (docs_flow.type in (0,4,10) AND TIMESTAMPDIFF(DAY,from_unixtime(".time().",'%Y-%m-%d'),from_unixtime(docs_flow.date_end,'%Y-%m-%d'))<=".$filters['val']." AND 
                    TIMESTAMPDIFF(DAY,from_unixtime(".time().",'%Y-%m-%d'),from_unixtime(docs_flow.date_end,'%Y-%m-%d'))>=0 AND docs_flow.is_closed=0 
                    AND (SELECT COUNT(*) FROM docs_flow d WHERE d.code=docs_flow.code AND ".$main." AND d.is_deleted=0)>0) ";
                    break;
                case 'done'://исполненные
                    $text.=" WHERE (".$main." AND ((SELECT COUNT(df.id) FROM docs_flow df WHERE df.code=docs_flow.code AND df.is_closed=1 AND df.type in (0,10) AND df.is_deleted=0)>0)) ";
                    break;
            }
        }
        /** Установка фильтров */
        if(!empty($filters['date_start']))
            $text.=" AND (SELECT d.date_create FROM docs_flow d WHERE d.id_parent=0 and d.is_deleted=0 and d.code=docs_flow.code AND NOT d.date_create IS NULL)>=".strtotime($filters['date_start']);
        if(!empty($filters['date_end']))
            $text.=" AND (SELECT d.date_create FROM docs_flow d WHERE d.id_parent=0 and d.is_deleted=0 and d.code=docs_flow.code AND NOT d.date_create IS NULL)<=".strtotime($filters['date_end']);
        if(!empty($filters['find'])) $text.=" AND (text.name LIKE '%".$filters['find']."%' OR text.text LIKE '%".$filters['find']."%') ";
        if(!empty($filters['own'])) $text.=" AND (CONCAT_WS(' ', own.lastname, own.firstname, own.secondname) LIKE '%".$filters['own']."%') ";
        if(!empty($filters['us'])) $text.=" AND (CONCAT_WS(' ', us.lastname, us.firstname, us.secondname) LIKE '%".$filters['us']."%') ";

        $start=array();
        $sql="SELECT docs_flow.code FROM docs_flow
              JOIN text ON text.id=docs_flow.id_text
              JOIN esia.user own ON own.id=docs_flow.id_owner
              JOIN esia.user us ON us.id=docs_flow.id_user
              join docs_types dt ON dt.id=docs_flow.id_type
              ".$text." AND dt.is_show=1 AND docs_flow.is_deleted=0 GROUP BY docs_flow.code ORDER BY docs_flow.date_create DESC 
              LIMIT ".$filters['limit']." OFFSET ".(($filters['page']-1)*$filters['limit']);
        //Все потоки работ где есть пользователь
        $items=$this->db_edms->getListData($sql, ['code']);
        //выборка конкретных задач по потокам работ
        while(count($items)!=0&&$items[0]){
            $arr=array_shift($items);
            $this->_tree= $this->_getTree($arr['code']);
            array_push($start,$this->outTree(0,0,"", $user,false,false)['text']);
        }
        $sql="SELECT COUNT(DISTINCT docs_flow.code) cn FROM docs_flow
              JOIN text ON text.id=docs_flow.id_text
              JOIN esia.user own ON own.id=docs_flow.id_owner
              JOIN esia.user us ON us.id=docs_flow.id_user
              join docs_types dt ON dt.id=docs_flow.id_type
              ".$text." AND dt.is_show=1 AND docs_flow.is_deleted=0";
        $start["count"]=$this->db_edms->getOneData($sql, ['cn'])['cn'];
        return $start;
    }

    function makeDepartmentTree($user,$inptText=null,$select=null){
        $right=new RightsHome();
        if($inptText!=null||$inptText!=""){
            $temp="";
            $items=$right->searchUsers($inptText,$user);
            foreach ($items as $item){
                if($select==$item['id']) $t='selected';
                $temp.="<option value='".$item['id']."' ".$t.">".$item['name']."</option>";
            }
        }else{
            PrintHTMLHome::set($right->getDepartmentTree($user,$inptText));
            $t = UserHome::getUserDepartment(intval($user));
            $temp = "";
            foreach ($t as $t1)
                $temp .= $this->outDepartmentTree($t1['id_parent'],  "",intval($user),$inptText);
        }
        return $temp;
    }

    /**
     * Генерация option
     * @param $array - Массив значений
     * @param $id - Выбранный ИД
     * @return string
     */
    function makeOption($array,$id){
        $result="";
        foreach ($array as $a){
            $text="";
            if($id!=null&&intval($a['id'])==$id) $text=" selected ";
            if($a['id']!=-1)//Неактивный option, заголовок раздела select
                $result.="<option value='".$a['id']."' ".$text.">".$a['name']."</option>";
            else//все остальные
                $result.=!$a['level']?"<option class='group-result' disabled>".$a['name']."</option>":"<option class='group-result'  ".$text.
                    " disabled style='padding-left: ".($a['level']*30+20)."'>".$a['name']."</option>";
        }
        return $result;
    }

    /**
     * Генерация истории выполнения задачи
     * @param $id - ИД задачи
     * @param $user - ИД пользователя
     * @return string
     */
    public function makeHistoryList($id,$user){
        $code=$this->getOneDocById($id)['code'];
        $sql = "SELECT docs_flow.id, docs_flow.id_parent, docs_flow.id_owner, docs_flow.id_operator, docs_flow.id_user, 
                docs_flow.code, docs_flow.type,text.id as id_text, text.text, text.name, docs_flow.date_show, docs_flow.date_create,docs_flow.date_end_fact,
                docs_flow.date_end, docs_flow.is_closed , docs_flow.is_changed1, docs_flow.is_changed2, docs_flow.is_changed3, docs_flow.is_changed4 
                FROM docs_flow JOIN text ON text.id=docs_flow.id_text WHERE (docs_flow.id_owner=".$user." OR docs_flow.id_operator=".$user." OR 
                docs_flow.id_user=".$user." OR  EXISTS(SELECT fw.id FROM flow_watchers fw WHERE fw.is_deleted=0 AND fw.id_flow=docs_flow.id AND fw.id_user=".$user.")) AND docs_flow.code=".$code." AND docs_flow.is_deleted=0 
                ORDER BY docs_flow.date_create ASC";
        $items = $this->db_edms->getListData($sql, ['id','id_parent', 'id_operator','id_owner','id_user','code','type','id_text','text','name','date_show','date_create','date_end','date_end_fact','is_closed','is_changed1','is_changed2','is_changed3','is_changed4']);
        $result="";
        foreach ($items as &$item){
            $item['id_watcher']=DocumentationHome::getWatchers($item['id']);
            $end=isset($item['date_show'])?UtilHome::getDateFromTimestamp($item['date_show'],'d.m.Y H:m'):"-";
            $style=!isset($item['date_show'])?"style='background: #e2e3e5;":"style='background: white;";
            $style.=$item['is_changed1']==1?"font-weight: bold;'":"'";
            if(isset($item['id_operator'])&&$item['type']!=3)
                $from="<a href='/portfolio/" . $item['id_operator'] ."' >".UserHome::getFIO($item['id_operator'])."</a> за <a href='/portfolio/" . $item['id_owner'] ."' >".UserHome::getFIO($item['id_owner'])."</a>";
            elseif(isset($item['id_owner']))
                $from="<a href='/portfolio/" . $item['id_owner'] ."' >".UserHome::getFIO($item['id_owner'])."</a>";
            $result.="<tr ".$style."><td><a href='/edms/" . $user . "/errand/" . $item['id'] . "' >".$item['name']."</a></td><td>".$from."</td><td><a href='/portfolio/" . $item['id_user'] ."' >".UserHome::getFIO($item['id_user'])."</a></td><td>".$item['date_create']."</td><td>".$end."</td></tr>";
        }
        return $result;
    }

    /**
     * Список поручений по документу
     * @param $id - ИД документа
     * @param $userId - ИД пользователя
     * @return string
     */
    public function makeErrandForDoc($id,$userId){
        $sql = "SELECT docs_flow.id, docs_flow.id_parent, docs_flow.id_owner, docs_flow.id_operator, docs_flow.id_user, docs_flow.code, docs_flow.type,text.id as id_text, text.text, text.name, 
                docs_flow.date_show, docs_flow.date_create,docs_flow.date_end_fact,docs_flow.date_end, docs_flow.is_closed , docs_flow.is_changed1, docs_flow.is_changed2, docs_flow.is_changed3, 
                docs_flow.is_changed4 FROM docs_flow
                JOIN text ON text.id=docs_flow.id_text WHERE docs_flow.id_parent=".$id." AND docs_flow.is_deleted=0 ORDER BY docs_flow.date_create ASC";
        $items = $this->db_edms->getListData($sql, ['id','id_parent', 'id_operator','id_owner','id_user','code','type','id_text','text','name','date_show','date_create','date_end','date_end_fact','is_closed','is_changed1','is_changed2','is_changed3','is_changed4']);
        $result="";
        foreach ($items as $item){
            if($item['id_user']==null||$item['id_user']==""){
                $sql = "SELECT docs_flow.id, docs_flow.id_parent, docs_flow.id_owner, docs_flow.id_operator, docs_flow.id_user, docs_flow.code, docs_flow.type,text.id as id_text, 
                text.text, text.name, docs_flow.date_show, docs_flow.date_create,docs_flow.date_end_fact,docs_flow.date_end, docs_flow.is_closed , docs_flow.is_changed1, 
                docs_flow.is_changed2, docs_flow.is_changed3, docs_flow.is_changed4 FROM docs_flow
                JOIN text ON text.id=docs_flow.id_text WHERE docs_flow.id_parent=".$item['id']." AND docs_flow.is_deleted=0 ORDER BY docs_flow.date_create ASC";
                $temp = $this->db_edms->getListData($sql, ['id','id_parent', 'id_operator','id_owner','id_user','code','type','id_text','text','name','date_show','date_create','date_end','date_end_fact','is_closed','is_changed1','is_changed2','is_changed3','is_changed4']);
                foreach ($temp as $t){
                    $end=isset($t['date_show'])?date($this->bigDateFormat, $t['date_show']):"-";
                    $style=!isset($t['date_show'])?"style='background: #e2e3e5;":"style='background: white;";
                    $style.=$t['is_changed1'] == 1?"font-weight: bold;'":"'";
                    if (isset($t['id_operator']) && $t['type'] != 3)
                        $from = UserHome::getFIO($t['id_operator']) . " за " . UserHome::getFIO($t['id_owner']);
                    elseif (isset($t['id_owner']))
                        $from = UserHome::getFIO($t['id_owner']);
                    $result.="<tr ".$style."><td><a href='/edms/" . $userId . "/errand/" . $t['id'] . "' >".$t['name']."</a></td><td>".$from."</td><td>".UserHome::getFIO($t['id_user'])."</td><td>".date($this->bigDateFormat, strtotime($t['date_create']))."</td><td>".$end."</td></tr>";
                }
            }else {
                $end=isset($item['date_show'])?date($this->bigDateFormat, $item['date_show']):"-";
                $style=!isset($item['date_show'])?"style='background: #e2e3e5;":"style='background: white;";
                $style .=$item['is_changed1'] == 1?"font-weight: bold;'":"'";
                if (isset($item['id_operator']) && $item['type'] != 3)
                    $from = UserHome::getFIO($item['id_operator']) . " за " . UserHome::getFIO($item['id_owner']);
                elseif (isset($item['id_owner']))
                    $from = UserHome::getFIO($item['id_owner']);
                $result .= "<tr " . $style . "><td><a href='/edms/" . $userId . "/errand/" . $item['id'] . "' >" . $item['name'] . "</a></td><td>" . $from . "</td><td>" . UserHome::getFIO($item['id_user']) . "</td><td>" . date($this->bigDateFormat, strtotime($item['date_create'])) . "</td><td>" . $end . "</td></tr>";
            }
        }
        return $result;
    }



    /**
     * Получение списка всех внесенных документов и добавление кнопок управления
     * @param $user - ИД пользователя
     * @param $filters - Массив фильтров
     * @return mixed
     */
    public function makeDocumentsList($user,$filters=[]){
        $result=$this->getDocumentList($user,$filters);
        if($user!=null)
            foreach ($result as $key=>$value){
                $result[$key]['name']="<a href='/edms/".$user."/document/".$value['id']."'; >".$value['name']."</a>";
                if($value['is_signed']==1)
                    $result[$key]['name'].="<i class=\"fa fa-certificate\" title='Подписан: ".$this->getMainSignTitle($value['id'])."' style=\"float: right;\" aria-hidden=\"true\"></i>";
                $result[$key]['btn'].="<div class=\"btn-group\" role=\"group\"><button class='btn btn-sm btn-outline-primary'
                                        title='Просмотр поручений на основе документа' onclick='window.open(\"/edms/".$user."/document/".$value['id']."/errands\");'><i class=\"far fa-list-alt\"></i></button>";
                $result[$key]['btn'].="<button class='btn btn-sm btn-outline-primary' 
                                        title='Отправить на подпись' onclick='window.open(\"/edms/".$user."/create/signing/".$value['id']."\");'><i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i></button>";
                $result[$key]['btn'].="<button class='btn btn-sm btn-outline-success' title='Создать поручение' 
                                        onclick='window.open(\"/edms/".$user."/create/errand/".$value['id']."\");'; ><i class=\"fa fa-plus\" aria-hidden=\"true\"></i></button></div>";
            }
        return $result;
    }

    /**
     * @param $id - ИД задачи
     * @param $mode - true-без возможности удаления, false-с возможностью удаления
     * @return string
     */
    public function getListOfFiles($id,$mode){
        $main=$this->haveDoc($this->getCode($id));
        $text="";
        if($main!=null) $text.=" OR docs_flow.id=".$main['id'];
        $sql="SELECT files.name, files.real_name FROM files
              LEFT JOIN text ON text.id=files.id_text
              LEFT JOIN docs_flow on docs_flow.id_text=text.id
              WHERE (docs_flow.id=".$id." ".$text.") AND files.is_deleted=0 ";
        $files=$this->db_edms->getListData($sql,['name','real_name']);
        $result="";
        if(count($files)!=0){
            $result="<label class=\"control-label small\">Прикрепленные файлы</label>";
            $arr=array();
            foreach ($files as $file){
                $temp=glob(self::$docsDir.$file['name']);
                array_push($arr,['name'=>$temp[0],'real_name'=>$file['real_name']]);
                //array_push($arr,$temp[0]);
            }
            if(count($arr)!=0){
                foreach($arr as $file)
                    $result.="<div class='row'><div class='col-lg-12'><div class=\"alert alert-info\" role=\"alert\"><a class=\"btn btn-link\" href=\"/edms/doc_files/?file=".
                        basename($file['name'])."\" traget=\"_blank\" >".$file['real_name']."</a>".(!$mode?"<input class=\"float-right\" type=\"checkbox\" id=\"scans[]\" 
                        name=\"scans[]\" value=".$file['name'].">":"")."</div></div></div>";
            }else
                $result.="<div class='row'><div class='col-lg-12'><div class=\"alert alert-secondary\" role=\"alert\">Отсутствуют</div></div></div>";
        }
        return $result;
    }
}