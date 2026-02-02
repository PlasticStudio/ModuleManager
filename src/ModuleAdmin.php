<?php

namespace PlasticStudio\ModuleManager;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use Symbiote\GridFieldExtensions\GridFieldAddNewMultiClass;
use Symbiote\GridFieldExtensions\Extensions\GridFieldDetailFormItemRequestExtension;

class Admin extends ModelAdmin {

    private static $url_segment = 'modules';
    private static $menu_title = 'Modules';
    private static $menu_icon_class = 'font-icon-edit-list';

    private static $managed_models = array(
		Module::class
    );

    public function getEditForm($id = null, $fields = null)
    {
        // Get the default form
        $form = parent::getEditForm($id, $fields);

        // GridField name (ModelAdmin sanitises the class)
        $gridFieldName = $this->sanitiseClassName(Module::class);

        // Locate the GridField
        $gridField = $form->Fields()->fieldByName("$gridFieldName");
        if (!$gridField) {
            return $form;
        }

        $config = $gridField->getConfig();

        // --- Remove default Add button ---
        $config->removeComponentsByType(GridFieldAddNewButton::class);

        // --- Remove GridFieldExtensions DetailForm hook that breaks MultiClass ---
        $config->removeComponentsByType(
            GridFieldDetailFormItemRequestExtension::class
        );

        // --- Create MultiClass Add component ---
        $multiClass = new GridFieldAddNewMultiClass();

        // --- Get list of classes dynamically ---
        $classes = [];

        foreach (ClassInfo::subclassesFor(Module::class) as $class) {
            if ($class === Module::class) {
                continue;
            }

            // Skip abstract classes
            if ((new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            // Only include classes that can be created
            if (!(singleton($class)->canCreate())) {
                continue;
            }

            $classes[$class] = singleton($class)->i18n_singular_name();
        }

        // Optional: allow project to override/extend via config
        $configClasses = Config::forClass(self::class)->get('slide_classes') ?? [];
        $classes = array_merge($classes, $configClasses);

        $multiClass->setClasses($classes);

        // Add to GridField
        $config->addComponent($multiClass);

        return $form;
    }
}
