<?php
/**
 * @package shop.plugins.reugroup
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright (c) 2016, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
return array(
    'category_id'   => array(
        'title'            => 'Категория',
        'description'      => 'Категория, которая будет назначена пользователю при регистрации. <a href="?action=customers">Добавить категорию</a>',
        'control_type'     => waHtmlControl::SELECT,
        'value'            => '',
        'options_callback' => array('shopReugroupPlugin', 'listGroups')
    ),
    'by_storefront' => array(
        'title'        => 'Условия для витрин',
        'description'  => 'Если нужно, здесь можно определить условия для отдельных витрин. Также можно создать ' .
            'несколько правил для одной витрины и пользователь будет добавлен в несколько категорий.<br>' .
            '<i class="icon10 exclamation" style="vertical-align:middle"></i><b>Внимание!</b>. Настроенные здесь правила <b>замещают</b> основную настройку!',
        'control_type' => 'ByStorefront',
        'value'        => array('enabled' => 0, 'table' => array(array('storefront' => '', 'category_id' => '')))
    )
);
