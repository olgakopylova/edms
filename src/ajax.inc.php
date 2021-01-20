<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
require_once($modules_root."edms/class/SaveHome.class.php");
require_once($modules_root."edms/class/UserHome.class.php");
require_once($modules_root."edms/class/PrintHTMLHome.class.php");
require_once($modules_root."edms/class/RightsHome.class.php");
require_once($modules_root."edms/class/ValidationHome.class.php");


require_once($modules_root . "edms/class/strategy/DocumentController.class.php");
require_once($modules_root . "edms/class/strategy/DocFlowHome.class.php");
require_once($modules_root . "edms/class/strategy/Errand.class.php");
require_once($modules_root . "edms/class/strategy/Sign.class.php");
require_once($modules_root . "edms/class/strategy/Document.class.php");
require_once($modules_root . "edms/class/strategy/Flow.class.php");
require_once($modules_root."edms/class/Paginate.php");
$DEBUG=true;
if(!$docHome) $docHome = new DocumentationHome();
if(!$docSave) $docSave = new SaveHome();
if(!$userHome) $userHome = new UserHome();
if(!$printHtml) $printHtml = new PrintHTMLHome();
if(!$rights) $rights = new RightsHome();
if(!$check) $check = new  ValidationHome();
if(!$action) $action = new  DocFlowHome();
if(!$controller) $controller = new DocumentController(new Errand());
if(!$navi) $navi = new Paginate( "" );

$method = $_SERVER['REQUEST_METHOD'];
if ($loggedIn) {
    if($GLOBALS["current_user"]['id'] == 517649557){
        $DEBUG = true;
    }
    /** Поиск по фильтру*/
    if($request->hasValue('filter')){
        $items=$_POST;
        $user=$request->getValue('user');
        switch ($items['type']){
            case 'e'://по поручениям
                $items['limit']=10;
                $arr['table'] = $printHtml->getAllWithFilter($user, $items);
                $arr['data']="data:{";
                foreach ($items as $key=>$item)
                    $arr['data'].="'".$key."':'".$item."',";
                $arr['data']=substr($arr['data'],0,strlen($arr['data'])-1);
                $arr['data'].="},";
                $arr=array_merge($items,$arr);
                $arr['user']=$user;
                $result['tree'] = $templateHome->parse($modules_root . "edms/tpl/tree.tpl", $arr);
                break;
            case 'd'://по документам
                $arr['table']=$printHtml->makeTable($printHtml->makeDocumentsList($user,$items),'documents/row.tpl');
                $result['docs']=$templateHome->parse($modules_root . "edms/tpl/documents/table.tpl", $arr);
                break;
        }
        echo json_encode($result);
    }

    /** Посимвольный поиск пользователей из select */
    if($request->hasValue('search')){
        $temp['rows']="";
       if($request->getValue('users')!=0){
           foreach ($request->getValue('users') as $id)
               $users.=$id.",";
           $users=substr($users, 0, -1);
           $temp['rows']=$userHome->getListUsers($users,$request->getValue('search'));
       }
       $temp['rows'] .= $printHtml->makeDepartmentTree($request->getValue('search'),$request->getValue('id_user'));
       echo json_encode($temp);
    }

    /** Формирование select с ответственныйми пользователями
     * (запрос выполняется после каждого выбора пользователя в поле исполнитель)
     */
    if ($request->hasValue('id_resp')&&!$request->hasValue('doc')){
        $result['option'] = $printHtml->outResponsible($request->getValue('id_resp'));
        if($request->getValue('id_resp')!=0){
            foreach ($request->getValue('id_resp') as $id)
                $users.=$id.",";
            $users=substr($users, 0, -1);
            $result['rows']=$userHome->getListUsers($users,$request->getValue('user'));
        }
        $result['rows'].= $printHtml->makeDepartmentTree($request->getValue('user'));
        echo json_encode($result);
    }

    if($request->hasValue('assignment')){
        /** Удаление замещения*/
        if($request->hasValue('delete')){
            $docSave->deleteAssignment($request->getValue('assignment'));
            echo json_encode('');
        }

        /** Сохранение нового и редактирование существующего замещения */
        if ($request->hasValue('rights')){
            $items = $_POST;
            $items['type']=$items['mode'];
            $items['user']=$request->getValue('user');
            if($items['id_alternate']==null||$items['id_alternate']=="")
                unset($items['id_alternate']);
            $items=array_merge($rights->save($items),$items);
            if(!isset($items['errors_none']))
                $items=$printHtml->showAssignment($items);
            echo json_encode($items);
        }
    }

    if ($request->hasValue('documentation')) {

        /** Предпросмотр*/
        if($request->hasValue('preview')){//предпросмотр
            $controller->setStrategy($request->getValue('id'));
            echo json_encode($controller->showPreview($request->getValue('id'),$request->getValue('preview')));
        }

        /** Построение пагинации*/
        if($request->hasValue('view')){
            $items=['status'=>$request->getValue('view'), 'user'=>$request->getValue('user'), 'limit'=>10, 'page'=>$request->getValue('page')];
            if($request->hasValue('val'))
                $items['val']=$request->getValue('val');
            $temp=$printHtml->getAllWithFilter($items['user'],$items);
            $count=$temp['count'];
            unset($temp['count']);
            $res['tree']="<ul>";
            foreach ($temp as $key=>$value)
                $res['tree'].="<li>".$value."</li>";
            $res['tree'].="</ul>";
            echo json_encode(['tree'=>$templateHome->parse($modules_root . "edms/tpl/tree.tpl", $res), "pagination"=>$navi->build( 10, $count, $items['page'] )]);
        }

        /** Сохранение любого документа*/
        if ($request->hasValue('doc')) {
            $items = $_POST;

            $type=$items['id']!=""?$docHome->getDocType($items['id']):$items['id_type'];
            if($request->hasValue('ernd')&&$type==5)
                $type=1;
            switch ($type) {
                case 1:
                    if(isset($items['new']) || (isset($items['ernd'])&&$items['id']!=''))//поручение или перепоречение
                        $controller->set(new Errand());
                    elseif($items['id']!=''&&!isset($items['edit']))//действия с поручениями
                        $controller->set(new Flow());
                    else//редактирование
                        echo json_encode($controller->edit($request->getValue('user'), $items, $_FILES));
                    break;
                case 5:
                    $items['COOKIE']=$_COOKIE;
                    if($items['id']=="")
                        $controller->set(new Sign());
                    elseif (isset($items['sign'])&&$items['id']!="")
                        echo json_encode($controller->edit($request->getValue('user'), $items, $_FILES));
                    else
                        $controller->set(new Flow());
                    break;
                default:
                    if($docHome->getShowType($type)==0)
                        $controller->set(new Document());
                    break;
            }
           if(!isset($items['edit'])&&!isset($params['sign']))
               echo json_encode($controller->saveDocument($request->getValue('user'), $items, $_FILES));
        }

        /** Ответ на закрытие */
        if($request->hasValue('close')){
            echo json_encode($controller->closeDocument($request->getValue('id'),$request->getValue('user'),$_GET,$request->getValue('mode')));
        }

        /**  */
        if($request->hasValue('change')){
            $result['option']=$printHtml->makeDepartmentTree($request->getValue('user'));
            echo json_encode($result);
        }

        /** Отмена выполнения*/
        if($request->hasValue('delete')){
            $docSave->deleteTree($request->getValue('id'));
            echo json_encode("");
        }

        /** Ответ на запрос продления */
        if($request->hasValue('extension')){
            $userId=$request->getValue('user');
            $docId=$request->getValue('id');
            switch ($request->getValue('type')){
                case 0://отклонение продления
                    $action->answerExtension($docId,0,$userId,$request->getValue('date_end'));
                    break;
                case 1://подтверждение продления
                    $action->answerExtension($docId,1,$userId,$request->getValue('date_end'));
                    break;
                case 2://отмена
                    $docSave->setDeleted('docs_flow',$docId);
                    break;
            }
            echo json_encode(["err"=>0]);
        }

        /** Перепоручение */
        if($request->hasValue('errand')){
            $userId=$request->getValue('user');
            $docId=$request->getValue('id');
            if($docHome->getShowType($docHome->getDocType($request->getValue('id')))!=0){
                $result=$action->showErrand($docId, $userId);
            }else{
                /*$controller->set(new Errand());
                $items=$docHome->getOneDocById($request->getValue('id'));
                $doc=$docHome->getText($request->getValue('id'));
                $temp['rows0'] = $printHtml->makeOption([0=>$doc],$doc['id']);
                $sel=$printHtml->makeOption($docHome->getOneDocType($items['id_type']), $items['id_type']);
                $temp['rows1'] = $printHtml->makeOption($rights->getAssignment($userId), $userId);
                $temp['rows3'] = $temp['rows2'] = $printHtml->makeDepartmentTree($userId);
                $temp['addFile'] = $templateHome->parse($modules_root . "edms/tpl/docs_card/add_file.tpl", []);
                $temp['type'] = 'new';
                $temp['user']=$userId;
                $modal['body'] = $templateHome->parse($modules_root . "edms/tpl/select.tpl", ['body' => $templateHome->parse($modules_root . "edms/tpl/docs/" . $controller->getTpl(), $temp),
                    'rows1' => $sel, 'user' => $result['user'],'close'=>true]);
                $modal['title']="Поручение";
                $result['content'] = $templateHome->parse($modules_root . "edms/tpl/modal.tpl", $modal);*/
                $result=$action->showErrand($docId,$userId,['id_prot'=>$docId]);
            }
            echo json_encode($result);
        }

        /** Дополнение задачи (текст) */
        if($request->hasValue('supplement')){
            echo json_encode($action->showSupplement($request->getValue('id')));
        }
    }

    if($request->hasValue('modal')){
        /** Модальные окна закрытия */
        if($request->hasValue('signing')){
            echo json_encode($action->showSigning($request->getValue('id'),$request->getValue('mode')));
        }

        /** Модальные окна продления */
        if($request->hasValue('extension')){
            echo json_encode($action->showExtension($request->getValue('id'),$request->getValue('mode')));
        }

        /** Модальное окно для отклонения закрытия */
        if($request->hasValue('revision')){
            echo json_encode($action->showRevision($request->getValue('id')));
        }
    }
}
