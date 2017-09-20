<?php
/**
 * Удаление ненужных файлов и деиректорий, оставшихся со времен, когда требования к продуктам не были такими строгими
 */
$plugin_path = wa('shop')->getConfig()->getPluginPath('reugroup');
waFiles::delete( $plugin_path . '/css');
waFiles::delete( $plugin_path . '/js');
waFiles::delete( $plugin_path . '/templates');
