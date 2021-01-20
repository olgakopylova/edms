<?php


class Paginate
{
    /**
     * Шаблон ссылки навигации
     * @var string
     */
    public $tpl = 'page/{page}/';

    /**
     * Обертка кнопок
     * @var string
     */
    public $wrap = "<div class=\"btn-group\" role=\"group\">{pages}</div>";

    /**
     * Сколько показывать кнопок страниц до и после актуальной
     * @var integer
     */
    public $spread = 1;

    /**
     * Разрыв между номерами страниц
     * @var string
     */
    public $separator = "<button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" disabled>...</button>";

    /**
     * Имя класса активной страницы
     * @var string
     */
    public $activeClass = 'link_active';

    /**
     * Номер просматриваемой страницы
     * @var integer
     */
    private $currentPage = 0;

    /**
     * Показывать кнопки "Вперед" и "Назад"
     * @var bool
     */
    public $nextPrev = true;

    /**
     * Текст кнопки "Назад"
     * @var string
     */
    public $prevTitle = '<<';

    /**
     * Текст кнопки "Вперед"
     * @var string
     */
    public $nextTitle = '>>';

    public function __construct(){
    }

    /**
     * Строим навигации и формируем шаблон
     * @param integer $limit количество записей на 1 страницу
     * @param integer $count_all общее количество всех записей
     * @param integer $currentPage номер просматриваемой страницы
     * @return mixed Сформированный шаблон навигации готовый к выводу
     */
    public function build($limit, $count_all, $currentPage = 1){
        if( $limit < 1 OR $count_all <= $limit ) return;
        $count_pages = ceil( $count_all / $limit );
        $this->currentPage = intval( $currentPage );
        if( $this->currentPage < 1 ) $this->currentPage = 1;

        $shift_start = max( $this->currentPage - $this->spread, 2 );
        $shift_end = min( $this->currentPage + $this->spread, $count_pages-1 );
        if( $shift_end < $this->spread*2 ) {
            $shift_end = min( $this->spread*2, $count_pages-1 );
        }
        if( $shift_end == $count_pages - 1 AND $shift_start > 3 ) {
            $shift_start = max( 3, min( $count_pages - $this->spread*2 + 1, $shift_start ) );
        }

        $list = $this->getItem( 1 );

        if ($shift_start == 3) {
            $list .= $this->getItem( 2 );
        } elseif ( $shift_start > 3 ) {
            $list .= $this->separator;
        }

        for( $i = $shift_start; $i <= $shift_end; $i++ ) {
            $list .= $this->getItem( $i );
        }

        $last_page = $count_pages - 1;
        if( $shift_end == $last_page-1 ){
            $list .= $this->getItem( $last_page );
        } elseif( $shift_end < $last_page ) {
            $list .= $this->separator;
        }

        $list .= $this->getItem( $count_pages );

        if( $this->nextPrev ) {
            $list = $this->getItem(
                    $this->currentPage > 1 ? $this->currentPage - 1 : 1,
                    $this->prevTitle,
                    true )
                . $list
                . $this->getItem(
                    $this->currentPage < $count_pages ? $this->currentPage + 1 : $count_pages,
                    $this->nextTitle,
                    true
                );
        }

        return $list;
    }

    /**
     * Формирование кнопки/ссылки
     * @param int $page_num номер страницы
     * @param string $page_name если указано, будет выводиться текст вместо номера страницы
     * @param bool $noclass
     * @return - span блок с активной страницей или ссылку.
     */
    private function getItem( $page_num, $page_name = '', $noclass = false ){
        $page_name = $page_name ?: $page_num;
        return $this->currentPage == $page_num&&$page_name!=$this->nextTitle&&$page_name!=$this->prevTitle?"<button type=\"button\" class=\"btn btn-sm btn-secondary active-page\" id='{$page_num}' disabled>{$page_name}</button>":
            "<button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"Click({$page_num})\">{$page_name}</button>";
    }
}