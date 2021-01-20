<?php
interface IStrategy
{
    /**
     * Получение названия TPL
     * @return mixed
     */
    public function tpl();

    /**
     * Проверка параметров перед созранением/редактированием
     * @param $items - Параметры для проверки
     * @param $user - ИД пользователя
     * @return mixed
     */
    public function check($items,$user);
    public function show($id,$user);
    /**
     * Сохранение поручения или перепоручения
     * @param $user - ИД пользователя, создающего задачу
     * @param $params - параметры (текст, тип, дата окончания)
     * @param $files - массив, прикрепленных файлов
     * @throws Exception
     */
    public function save($user, $params, $files);
    /**
     * Получение кнопок управления документом
     * @param $doc - массив параметров задачи
     * @param $user - ИД пользователя
     * @return string
     */
    public function buttons($doc,$user);
    /**
     * Генерация формы создания
     * @param $id
     * @param $user
     * @return mixed
     */
    public function showCreate($id,$user);
    /**
     * Генерация предпросмотра документа
     * @param $id - ИД документа
     * @param $user - ид пользователя
     * @return mixed
     * @throws Exception
     */
    public function preview($id,$user);
}