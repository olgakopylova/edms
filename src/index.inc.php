<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
require_once($modules_root."edms/class/PrintHTMLHome.class.php");
require_once($modules_root . "edms/class/strategy/DocumentController.class.php");
require_once($modules_root . "edms/class/strategy/Errand.class.php");
require_once($modules_root . "edms/class/strategy/Sign.class.php");
require_once($modules_root . "edms/class/strategy/Document.class.php");
require_once($modules_root . "edms/class/strategy/Flow.class.php");
require_once($modules_root."edms/class/Paginate.php");

if(!$docHome) $docHome = new DocumentationHome();
if(!$printHtml) $printHtml = new PrintHTMLHome();
if(!$controller) $controller = new DocumentController(new Errand());
if(!$navi) $navi = new Paginate();
/**
 * АДРЕСАЦИЯ
 * /edms - Начальная страница («Поручения»)
 * /edms/{ИД пользователя}/create/errand - Создание поручения
 * /edms/{ИД пользователя}/create/document - Создание документа
 * /edms/{ИД пользователя}/create/assignment - Создание замещения
 * /edms/{ИД пользователя}/documents - Страницы «Документы»
 * /edms/{ИД пользователя}/assignments - Страница «Замещения»
 * /edms/{ИД пользователя}/document/{ИД документа}/errands - Страница со списком поручений к документу
 * /edms/{ИД пользователя}/create/signing/{ИД документа} - Создание подписания для документа
 * /edms/{ИД пользователя}/create/errand/{ИД документа} - Создание поручения на основе документа
 * /edms/{ИД пользователя}/errand/{ИД задачи} - Просмотр поручения/подписания/документа/запроса
 * /edms/{ИД пользователя}/assignment/{ИД замещения } - Просмотр замещения
 */
if($loggedIn) {
    if ($GLOBALS["current_user"]['id'] == $request->getValue('edms')||$GLOBALS["current_user"]['id'] == 517649557)
    {
        /*if($GLOBALS["current_user"]['id'] == 517649557){
            ini_set("display_errors","1");
            ini_set("display_startup_errors","1");
            ini_set('error_reporting', E_ALL);
        }*/
        if ($request->hasValue('create')) {
            if($request->hasValue('errand')){//создание нового поручения
                $controller->set(new Errand());
                $module['text'] .= $controller->showNewDocument($request->getValue('errand'),$request->getValue('edms'));
            }elseif ($request->hasValue('document')){//создание документа
                $controller->set(new Document());
                $module['text'] .= $controller->showNewDocument($request->getValue('document'),$request->getValue('edms'));
            }elseif ($request->hasValue('assignment')){//создание замещения
                $module['text'] .= $printHtml->showAssignment($request->getValue('edms'));
            }elseif($request->hasValue('signing')){//создание согласования
                $controller->set(new Sign());
                $module['text'] .= $controller->showNewDocument($request->getValue('signing'),$request->getValue('edms'));
            }
        }elseif ($request->hasValue('errand')) {
            if ($request->getValue('errand') != null) {
                switch ($docHome->getDocType($request->getValue('errand'))){
                    case 1:
                        if(!$docHome->isFlow($request->getValue('errand')))
                            $controller->set(new Errand());
                        else
                            $controller->set(new Flow());
                        break;
                    case 5:
                        $controller->set(new Sign());
                        break;
                    default:
                        if($docHome->getShowType($docHome->getDocType($request->getValue('errand')))==0)
                            $controller->set(new Document());
                        break;
                }
                $module['text'] .= $controller->showDocument($request->getValue('errand'),$request->getValue('edms'));
            }
        }elseif($request->hasValue('assignments'))
            $module['text'] .= $printHtml->getAllAssignments($request->getValue('edms'));
        elseif($request->hasValue('assignment'))
            $module['text'] .= $printHtml->showAssignment($request->getValue('edms'),$request->getValue('assignment'));
        elseif ($request->hasValue('documents'))
            $module['text'] .=$printHtml->getAllDocuments($request->getValue('edms'));
        elseif ($request->hasValue('document')){
            $controller->setStrategy($request->getValue('document'));
            $module['text'] .=$request->hasValue('errands')?$printHtml->getErrandsOfDocument($request->getValue('edms'),$request->getValue('document')):
                $controller->showDocument($request->getValue('document'),$request->getValue('edms'));
        }elseif ($request->getValue('edms')=="")
            $module['text'] .=$printHtml->getAllErrands($GLOBALS["current_user"]['id']);
        elseif ($GLOBALS["current_user"]['id'] == 517649557)
            $module['text'] .=$printHtml->getAllErrands($request->getValue('edms'));
    }elseif ($request->getValue('edms')=="")
        $module['text'] .=$printHtml->getAllErrands($GLOBALS["current_user"]['id']);
}