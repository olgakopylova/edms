<?php

class ValidationHome extends DocumentationHome {

    public static function isCorrectString($str) {
        $reg = '/^[A-Za-zА-Яа-яЁё0-9\s\-\,\.\:№"«»\(\)]+$/u';
        return preg_match ($reg,$str)==1;
    }

    /**
     * Проверка перед сохранением закрытия
     * @param $items - Парамерты задачи
     * @return array
     */
    public function checkClosing($items){
        $error=array();
/*        if($this->openClosing($items['id']))
            $error['err'] = 'Уже существует не закрытый запрос на завершение поручения';
        if(!$this->allChildIsClose($items['id']))
            $error['err'] = 'Закрытие невозможно, проверьте завершены ли остальные запросы по данному поручению';*/
        if(empty($error)){
            $error['errors_none']='Документ сохранен';
        }
        return $error;
    }

    /**
     * проверка перед созранением
     * @param $items - Массив параметров
     * @return array
     */
    public function checkRevision($items){
        $error=array();
        if(empty($error)){
            $error['errors_none']='Документ сохранен';
        }
        return $error;
    }

    /**
     * Проверка перед сохранением продления
     * @param $items - Парамерты задачи
     * @return array
     */
    public function checkExtension($items){
        $error=array();
        $doc=self::getOneDocById($items['id']);

        if($this->openExtension($items['id']))
            $error['err'] = 'Уже существует не закрытый запрос на продление поручения';
        if(strtotime($items['date_end'])<=strtotime($doc['date_end'])){
            $error['date_err'] = true;
            $error['date_end_err'] = 'Новая дата окончания должна быть больше текущей установленной';
            return $error;
        }
        if(strtotime($items['date_end'])<strtotime($this->now_date)){
            $error['date_end_err'] = 'Дата окончания не может быть меньше текущей даты';
            return $error;
        }
        if(empty($error)){
            $error['errors_none']='Документ сохранен';
        }
        return $error;
    }

    /**
     * Проверка перед сохранением поручения или перепоручения
     * @param $items - Парамерты задачи
     * @return array
     * @throws Exception
     */
    public function checkNewAndErrand($items){
        $error=array();
        $items['date_end']=UtilHome::getDateFormat($items['date_end'],"Y-m-d");
        if((!isset($items['id_user'])&&!isset($items['id_user1']))||($items['id_user']==""&&$items['id_user1']==""))
            $error['user_err'] = 'Исполнители не выбраны';
        if (self::isCorrectString($items['name'])!= 1) {
            $error['name_err'] = 'Недопустимый символ';
        }
        if(isset($items['ernd'])||isset($items['suppl'])){
            $doc=$this->getOneDocById($items['id']);
            if(strtotime($items['date_end'])>strtotime($doc['date_end'])&&$doc['date_end_unix']!=null) {
                $error['date_end_err'] = 'Дата окончания не может быть больше даты окончания исходного поручения';
                return $error;
            }
        }
        if(strtotime($items['date_end'])<strtotime($this->now_date)){
            $error['date_end_err'] = 'Дата окончания не может быть меньше текущей даты';
            return $error;
        }
        if(empty($error))
            $error['errors_none']='Документ сохранен';
        return $error;
    }

    /**
     * Проверка перед сохранением изменений
     * @param $items - Массив параметров
     * @return array
     * @throws Exception
     */
    public function checkEdit($items){
        $error=array();
        $doc=self::getOneDocById($items['id']);
        $items['date_end']=UtilHome::getDateFormat($items['date_end'], "Y-m-d");
        if (self::isCorrectString($items['name'])!= 1) {
            $error['name_err'] = 'Недопустимый символ';
        }
        if((strtotime($items['date_end'])<strtotime($this->now_date))&&(strtotime($items['date_end'])!=$doc['date_end'])){
            $error['date_end_err'] = 'Дата окончания не может быть меньше текущей даты';
            return $error;
        }
        if(empty($error)){
            $error['errors_none']='Документ сохранен';
        }
        return $error;
    }

    /**
     * Проверка перед сохранением замещения
     * @param $items - Массив параметров
     * @return array
     * @throws Exception
     */
    public function checkRights($items){
        $error=array();
        $items['date_end']=new DateTime($items['date_end']);
        $items['date_end'] = $items['date_end']->format("Y-m-d");
        if(!$items['id_alternate']&&($items['id']==""||$items['id']==null))
            $error['alternate_err'] = 'Заместитель не выбран';
        if(!isset($items['mode'])){
            if(strtotime($items['date_start'])<strtotime($this->now_date)){
                $error['date_err'] ='Дата начала не может быть меньше текущей даты';
                $error['date_start_err'] = true;
                return $error;
            }
            if(strtotime($items['date_end'])<strtotime($this->now_date)){
                $error['date_err'] ='Дата окончания не может быть меньше текущей даты';
                $error['date_end_err'] = true;
                return $error;
            }
            if(strtotime($items['date_end'])<strtotime($items['date_start'])){
                $error['date_err'] = 'Дата окончания не может быть меньше даты начала';
                $error['date_end_err'] = true;
                return $error;
            }
        }
        if(empty($error)){
            $error['errors_none']='Документ сохранен';
        }
        return $error;
    }

    /**
     * Проверка перед сохранением дополнения
     * @param $items - Массив параметров
     * @return array
     * @throws Exception
     */
    public function checkSupplement($items){
        $error['errors_none']='Документ сохранен';
        return $error;
    }
}