<?php
require_once($modules_root."edms/class/PrintHTMLHome.class.php");
require_once($modules_root."edms/class/RightsHome.class.php");
class UtilHome{
    /**
     * @var string - Директория сохранения файлов
     */
    public static $docsDir="files/lk/docs/documentation/";

    /**
     * Генерация случайнов последовательности
     * @param $number - Длинна сгенерированной строки
     * @return string
     */
    public static function generate($number) {
        $arr = array('a','b','c','d','e','f',
            'g','h','i','j','k',
            'm','n','o','p','r','s',
            't','u','v','x','y','z',
            'A','B','C','D','E','F',
            'G','H','J','K','L',
            'M','N','O','P','R','S',
            'T','U','V','X','Y','Z',
            '1','2','3','4','5','6',
            '7','8','9','0');
        $pass = "";
        $num = 0;
        $sym = 0;
        for($i = 0; $i < $number; $i++) {
            $index = rand(0, count($arr) - 1);
            if(is_numeric($arr[$index])) $num++;
            if(!is_numeric($arr[$index])) $sym++;
            $pass .= $arr[$index];
        }
        return $pass;
    }

    /**
     * Генерация пути сохранения файла
     * @param $name - Наименование файла
     * @return string
     */
    public static function setScanPath($name){
        return self::$docsDir . $name;
    }

    /**
     * Получение timestamp из строки
     * @param $date - Дата, строка
     * @return int
     * @throws Exception
     */
    public static function getTimeStamp($date){
        $result = new DateTime($date);
        return $result->getTimestamp();
    }

    /**
     * Получение текущей даты в формате "Y-m-d"
     * @return string
     * @throws Exception
     */
    public static function getNowDate(){
        $result = new DateTime("now");
        return $result->format("Y-m-d");
    }

    /**
     * Изменение даты (увеличего/уменьшение)
     * @param $date - Дата в timestanp
     * @param $num - число на которое необходимо увеличить
     * @param $mode - Тип изменения (+/-)
     * @param $param - years/days/etc
     * @return string
     * @throws Exception
     */
    public static function modifyDate($date, $num, $mode, $param){
        $result=new DateTime($date);
        return $result->modify($mode.' '.$num.' '.$param)->format("Y-m-d");
    }

    /**
     * Вычисление разницы дат
     * @param $date - Дата
     * @return mixed
     * @throws Exception
     */
    public static function dateDiff($date){
        return date_diff(new DateTime('now'), new DateTime($date))->days;
    }

    /**
     * Приведение к заданному формату
     * @param $date - Дата строкой
     * @param string $format - Необходимый формат даты
     * @return string
     * @throws Exception
     */
    public static function getDateFormat($date, $format="d.m.Y"){
        $result = new DateTime($date);
        return  $result->format($format);
    }

    /**
     * Приведение timestamp к строке
     * @param $date - Дата timestamp
     * @param string $format
     * @return string
     * @throws Exception
     */
    public static function getDateFromTimestamp($date, $format="Y-m-d"){
        $result= new DateTime();
        $result->setTimestamp($date);
        return $result->format($format);
    }

    /**
     * Транслитерация
     * @param $string - Строка на русском языке
     * @return string
     */
    public static function translit($string) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '',    'ы' => 'y',   'ъ' => '',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya', ' '=>'_'
        );
        return strtr($string, $converter);
    }


}
