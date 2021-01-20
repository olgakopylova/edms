<?php
require_once($modules_root."edms/class/DocumentationHome.class.php");
require_once($modules_root."edms/class/SaveHome.class.php");
require_once($modules_root."edms/class/strategy//IStrategy.php");
require_once($modules_root."edms/class/strategy/DocFlowHome.class.php");
/**
 * Контекст определяет интерфейс, представляющий интерес для клиентов.
 */
class DocumentController
{
    /**
     * @var Strategy Контекст хранит ссылку на один из объектов Стратегии.
     * Контекст не знает конкретного класса стратегии. Он должен работать со
     * всеми стратегиями через интерфейс Стратегии.
     */
    private $strategy;
    private $templateHome;
    private $modules_root;
    private $docHome;
    private $docSave;
    private $action;
    /**
     * Обычно Контекст принимает стратегию через конструктор, а также
     * предоставляет сеттер для её изменения во время выполнения.
     */
    public function __construct(IStrategy $strategy)
    {
        $this->strategy = $strategy;
        $this->templateHome=$GLOBALS['templateHome'];
        $this->modules_root=$GLOBALS['modules_root'];
        $this->docHome=new DocumentationHome();
        $this->docSave=new SaveHome();
        $this->action=new DocFlowHome();
    }

    /**
     * Обычно Контекст позволяет заменить объект Стратегии во время выполнения.
     */
    public function set(IStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy($id){
        if(!$this->docHome->isFlow($id)){
            switch ($this->docHome->getDocType($id)){
                case 1:
                    $this->set(new Errand());
                    break;
                case 5:
                    $this->set(new Sign());
                    break;
                default:
                    $this->set(new Document());
                    break;
            }
        }else
            $this->set(new Flow());
    }

    public function getTpl(){
        return $this->strategy->tpl();
    }

    /**
     * Вместо того, чтобы самостоятельно реализовывать множественные версии
     * алгоритма, Контекст делегирует некоторую работу объекту Стратегии.
     */
    public function checkDocument($items,$user)
    {
        return $this->strategy->check($items,$user);
    }

    public function showDocument($id,$user){
        return $this->strategy->show($id,$user);
    }

    public function saveDocument($user, $items, $files){
        $result=$this->checkDocument($items,$user);
        if(isset($result['errors_none'])){
            $this->strategy->save($user, $items, $files);
            $result["errors_none"]="true";
        }
        return $result;
    }

    public function closeDocument($id,$user,$items,$mode){
        /** Если у разных типов предполагается разное закрытие
         * то вынести этот код в функцию close в классе Errand
         * и для разных документов реализовать разное закрытие
         */
        if($mode==1||$mode==3){//1 - просто закрытие, 2 - принятие с отчетом
            return $this->action->showClose($id,$mode);
        }elseif($mode==2){//принудительное завершение
            $this->docSave->setAllClose($id);
            return '';
        }
        else{//просто принятие
            $this->docSave->closing($id,$user,$items,null);
            return '';
        }
    }

    public function edit($user, $items, $files){
        $this->docSave->edit($user, $items, $files);
        return ['errors_none'=>true];
    }

    public function showNewDocument($id,$user){
        return $this->strategy->showCreate($id,$user);
    }

    public function showPreview($id,$user){
        return $this->strategy->preview($id,$user);
    }
}