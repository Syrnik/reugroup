<?php
/**
 * @package shop.plugins.reugroup
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @version 1.0.0
 * @copyright (c) 2016, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

/**
 * Main plugin class
 */
class shopReugroupPlugin extends shopPlugin
{
    /**
     * Обработчик хука signup
     *
     * @param waContact $contact
     */
    public function handlerSignup($contact)
    {
        $category_id = $this->getSettings('category_id');
        $ContactCategory = new waContactCategoryModel();

        // проверим на всякий случай есть-ли еще такая категория
        // а то вдруг ее какой-нибудь дурак удалил, а в настройке плагина она осталась
        // ресурсов на проверку нужно мало, а дураков на свете много
        $category_id = $ContactCategory->select('id')->where('id=:id', array('id' => $category_id))->fetchField();

        if (($contact instanceof waContact) && $contact->getId() && $category_id) {
            $ContactCategories = new waContactCategoriesModel();
            $ContactCategories->add($contact->getId(), $category_id);
        }
    }

    public static function listGroups()
    {
        $ContactCategory = new waContactCategoryModel();

        $categories = $ContactCategory->select('*')->where("app_id='shop'")->order('name')->fetchAll();
        $cats = array(array('value' => '', 'title' => ''));
        foreach ($categories as $category) {
            $cats[] = array('value' => $category['id'], 'title' => htmlentities($category['name'], null, 'UTF-8'));
        }

        return $cats;
    }
}
