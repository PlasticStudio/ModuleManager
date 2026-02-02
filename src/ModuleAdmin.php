<?php

namespace PlasticStudio\ModuleManager;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;

class Admin extends ModelAdmin {

    private static $url_segment = 'modules';
    private static $menu_title = 'Modules';
    private static $menu_icon_class = 'font-icon-edit-list';

    private static $managed_models = array(
		Module::class
    );

    public function getEditForm($id = null, $fields = null){
        $form = parent::getEditForm($id, $fields);

        $gridFieldName = $this->sanitiseClassName(Module::class);
        $gridField = $form->Fields()->fieldByName($gridFieldName);

        // Swap out our "Add" button for the multiclass Add
        // $gridField->getConfig()->addComponent(new GridFieldAddNewMultiClass());

        $gridFieldName = $this->sanitiseClassName(Module::class);
        $gridField = $form->Fields()->fieldByName($gridFieldName);

        if (!$gridField) {
            return $form;
        }

        $config = $gridField->getConfig();
        
        // remove default add new button
		$gridField->getConfig()->removeComponentsByType(GridFieldAddNewButton::class);
        
        // set up to class dropdown add
        $multiClass = new GridFieldAddNewMultiClass();
        $classes = [];

        foreach (ClassInfo::subclassesFor(Module::class) as $class) {
            // Skip the abstract/base class itself
            if ($class === Module::class) {
                continue;
            }

            // Optional: skip abstract classes
            if ((new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            $classes[$class] = singleton($class)->i18n_singular_name();
        }

        // Important: must be called before adding to config
        $multiClass->setClasses($classes);

        // add the component to the gridfield
        $config->addComponent($multiClass);

        return $form;
    }
}
