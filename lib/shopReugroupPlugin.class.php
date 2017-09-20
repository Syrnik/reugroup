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
    public function getControls($params = array())
    {
        waHtmlControl::registerControl('ByStorefront', array($this, 'settingByStorefront'));
        return parent::getControls($params);
    }

    /**
     * Обработчик хука signup
     *
     * @param waContact $contact
     */
    public function handlerSignup($contact)
    {
        if (!($contact instanceof waContact) || !$contact->getId()) {
            return;
        }

        $ContactCategory = new waContactCategoryModel();

        $categories = array();
        $by_storefront = $this->getSettings('by_storefront');
        $is_frontend = wa()->getEnv() == 'frontend';

        if ($is_frontend && (bool)ifempty($by_storefront['enabled'], false)) {
            $rules = (array)ifempty($by_storefront['table'], array());
            $storefront = wa()->getRouting()->getDomain() . '/' . wa()->getRouting()->getRoute('url');
            $storefront = rtrim($storefront, '*');

            foreach ($rules as $rule) {
                if (!is_array($rule) || !ifempty($rule['storefront']) || !isset($rule['category_id'])) {
                    continue;
                }
                if ($rule['storefront'] != $storefront) {
                    continue;
                }
                if (!($category_id = $ContactCategory->select('id')->where('id=:id', array('id' => $rule['category_id']))->fetchField())) {
                    continue;
                }

                $categories[] = $category_id;
            }
        }

        if (!$categories) {
            $category_id = $this->getSettings('category_id');
            if ($category_id) {
                // проверим на всякий случай есть-ли еще такая категория
                // а то вдруг ее какой-нибудь дурак удалил, а в настройке плагина она осталась
                // ресурсов на проверку нужно мало, а дураков на свете много
                $category_id = $ContactCategory->select('id')->where('id=:id', array('id' => $category_id))->fetchField();

                if ($category_id) {
                    $categories[] = $category_id;
                }
            }
        }


        if ($categories) {
            $ContactCategories = new waContactCategoriesModel();
            $ContactCategories->add($contact->getId(), $categories);
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

    /**
     * @param $name
     * @param array $params
     * @return string
     * @throws waException
     */
    public function settingByStorefront($name, $params = array())
    {
        $default_params = array(
            'title'         => '',
            'title_wrapper' => false,
            'description'   => '',
        );

        foreach ($params as $field => $param) {
            if (strpos($field, 'wrapper')) {
                unset($params[$field]);
            }
        }

        if (!isset($params['value']) || !is_array($params['value'])) {
            $params['value'] = array(array('storefront' => 'pvz/', 'category_id' => 2));
        }

        $params = array_merge($params, $default_params);
        waHtmlControl::addNamespace($params, $name);
        unset($name);

        $checkbox_params = $params;

        $checkbox_params['value'] = ifset($params['value']['enabled'], 0);
        $checkbox_params['title'] = 'Включить';
        $checkbox_params['description'] = '';
        $checkbox_params['title_wrapper'] = '%s';
        $checkbox_params['control_wrapper'] = '<p>%2$s %1$s</p>';
        waHtmlControl::makeId($checkbox_params, 'enabled');
        $checkbox_id = $checkbox_params['id'];
        $checkbox_params['id'] = $params['id'];

        $html = '';

        $html .= waHtmlControl::getControl(waHtmlControl::CHECKBOX, 'enabled', $checkbox_params);
        $style_hide = (bool)$checkbox_params['value'] ? '' : 'display:none';

        $table_params = $params;
        waHtmlControl::addNamespace($table_params, 'table');
        waHtmlControl::makeId($table_params);

        $html .= "<table id=\"{$table_params['id']}\" class=\"zebra\" style=\"max-width: 650px;$style_hide\">";
        $storefronts_options = $this->optionsStorefronts();
        $categories_options = $this->listGroups();
        $l10n = array(
            'Storefront' => _wp('Storefront'),
            'Category'   => _wp('Category'),
            'Add rule'   => _wp('Add rule'),
//            'Add help' => _wp('Add')
        );

        $html .= <<<HTML
<thead><tr><th>{$l10n['Storefront']}</th><th></th><th>{$l10n['Category']}</th><th></th></tr></thead>
<tfoot><tr class="white">
    <td><a href="javascript:void(0);" class="js-add-rule"><i class="icon16 add"></i>{$l10n['Add rule']}</a></td>
    <td colspan="3"></td>
</tr></tfoot>
HTML;

        foreach ($params['value']['table'] as $i => $p) {
            $html .= '<tr class="js-rule">';
            $row_params = $table_params;
            unset($row_params['value']);
            waHtmlControl::addNamespace($row_params, $i);
            $row_params['control_wrapper'] = '<td>%2$s</td>';
            $html .= waHtmlControl::getControl(waHtmlControl::SELECT, 'storefront', $row_params + array('value' => $p['storefront'], 'options' => $storefronts_options));
            $html .= '<td class="min-width">⇒</td>';
            $html .= waHtmlControl::getControl(waHtmlControl::SELECT, 'category_id', $row_params + array('value' => $p['category_id'], 'options' => $categories_options));
            $html .= '<td class="min-width"><a href="javascript:void(0);" class="js-delete-rule" title="' . _wp('Delete rule') . '"><i class="icon16 delete"></i></a></td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $html .= <<<HTML
<script type="text/javascript">
$(function(){
    var table=$("#{$table_params['id']}");
    var checkbox=$("#{$checkbox_id}");
    checkbox
        .off()
        .on('change', function(){
            table.toggle(!!$(this).is(':checked'));
        });

    table
        .off()
        .on('click', '.js-delete-rule', function(){
            if($('tr.js-rule').length > 1) $(this).closest('tr.js-rule').remove();
            return false;
        })
        .on('click', '.js-add-rule', function(){
            var clone = $('tr.js-rule:last', table).clone();
            $(':input', clone).each(function(){
                var input=$(this);
                input.attr('name', input.attr('name').replace(/\[table]\[(\d+)]/, function(str, p1){
                    return '[table][' + (parseInt(p1, 10)+1) + ']';
                }));
            });
            $('tbody', table).append(clone);
        })

});
</script>
HTML;


        return $html;
    }

    /**
     * @return array
     */
    public function optionsStorefronts()
    {
        $storefronts = $this->getStorefornts();
        $options = array();
        foreach ($storefronts as $s) {
            $options[] = array('title' => $s, 'value' => $s);
        }

        return $options;
    }

    /**
     * Копия метода из shopHelper от Shop-Script 7.
     * Чтобы работало и на более ранних
     *
     * @param bool|false $verbose
     * @return array
     */
    protected function getStorefornts($verbose = false)
    {
        $storefronts = array();
        foreach (wa()->getRouting()->getByApp('shop') as $domain => $domain_routes) {
            foreach ($domain_routes as $route) {
                $url = rtrim($domain . '/' . $route['url'], '/*') . '/';
                if ($verbose) {
                    $storefronts[] = array(
                        'domain' => $domain,
                        'route'  => $route,
                        'url'    => $url
                    );
                } else {
                    $storefronts[] = $url;
                }
            }
        }

        return $storefronts;
    }
}
