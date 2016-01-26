<?php
/**
 * @package shop.plugins.reugroup
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 1.0.0
 * @copyright (c) 2016, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
return array(
    'name'     => 'Назначение категории при регистрации',
    'img'      => 'img/reugroup.png',
    'version'  => '1.0.0',
    'vendor'   => '670917',
    'handlers' =>
        array(
            'signup' => 'handlerSignup'
        ),
);
