<?php

namespace Craft;

class SeoController extends BaseController
{

	public function actionSettings ()
	{
		$namespace = 'settings';

		$settings = craft()->plugins->getPlugin('seo')->getSettings();
		$fieldsRaw = craft()->fields->getAllFields();
		$fields = [];

		foreach ($fieldsRaw as $field) {
			$fields[$field->handle] = array(
				'label' => $field->name,
				'value' => $field->handle,
				'type' => $field->fieldType->name
			);
		}

		$unsetFields = $fields;

		if ($settings->readability !== null) {
			foreach ($settings->readability as $field) {
				if ($unsetFields[$field])
					unset($unsetFields[$field]);
			}
		}

		craft()->templates->includeJsResource('seo/js/seo-settings.js');
		craft()->templates->includeJs("new SeoSettings('{$namespace}');");

		$this->renderTemplate('seo/settings', array(
			// Global
			'namespace' => $namespace,
			'settings' => $settings,

			// Misc
			'tabs' => [
				['label' => 'Sitemap', 'url' => "#{$namespace}-tab1", 'class' => null],
				['label' => 'Redirects', 'url' => "#{$namespace}-tab2", 'class' => null],
				['label' => 'Fieldtype', 'url' => "#{$namespace}-tab3", 'class' => null],
			],
			'crumbs' => [
				['label' => 'Settings', 'url' => 'settings'],
				['label' => 'Plugins', 'url' => 'settings/plugins'],
			],

			// Sitemap
			'sections' => craft()->seo_sitemap->getValidSections(),

			// Fieldtype
			'fields' => $fields,
			'unsetFields' => $unsetFields,
		));
	}

}