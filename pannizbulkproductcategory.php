<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\Type\SubmitBulkAction;

class PannizBulkProductCategory extends Module
{
    public function __construct()
    {
        $this->name = 'pannizbulkproductcategory';
        $this->tab = 'back_office_features';
        $this->version = '1.0.0';
        $this->author = 'Panniz';
        $this->need_instance = 1;

        parent::__construct();

        $this->displayName = $this->trans('Panniz - Bulk Category Assignment', [], 'Modules.Pannizbulkproductcategory.Admin');
        $this->description = $this->trans('Adds a bulk action to assign multiple products to new categories.', [], 'Modules.Pannizbulkproductcategory.Admin');
        $this->ps_versions_compliancy = ['min' => '8.1.0', 'max' => _PS_VERSION_];
    }

    public function install(): bool
    {
        return parent::install() && $this->installTab() && $this->registerHook('actionProductGridDefinitionModifier');
    }

    public function uninstall(): bool
    {
        return parent::uninstall() && $this->uninstallTab();
    }

    public function hookActionProductGridDefinitionModifier(array $params): void
    {
        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];

        // We add our bulk action to the existing action collection
        $definition->getBulkActions()->add(
            // We define our new action
            (new SubmitBulkAction('bulk_add_to_categories'))
                // We set the name that will appear in the dropdown menu
                ->setName($this->trans('Add to categories', [], 'Modules.Pannizbulkproductcategory.Admin'))
                // We set the options, the most important is the route of our controller
                ->setOptions([
                    'submit_route' => 'panniz_bulk_assign_category_form',
                ])
        );
    }

    private function installTab(): bool
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'panniz_bulk_assign_category_form';
        $tab->route_name = 'panniz_bulk_assign_category_form';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->displayName;
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        return $tab->add();
    }

    private function uninstallTab(): bool
    {
        $id_tab = (int)Tab::getIdFromClassName('panniz_bulk_assign_category_form');
        if ($id_tab) {
            return (new Tab($id_tab))->delete();
        }
        return true;
    }
}