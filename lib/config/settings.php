<?php
/**
 * @package shop.plugins.reugroup
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 1.0.0
 * @copyright (c) 2016, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
return array(
    'category_id' => array(
        'title'            => 'Категория',
        'description'      => 'Категория, которая будет назначена пользователю при регистрации. <a href="?action=customers">Добавить категорию</a>',
        'control_type'     => waHtmlControl::SELECT,
        'value'            => '',
        'options_callback' => array('shopReugroupPlugin', 'listGroups')
    )
);
