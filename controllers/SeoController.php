<?php

namespace Craft;

class SeoController extends BaseController
{

	public function actionSettings ()
	{
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

		$namespaceId = craft()->templates->namespaceInputId(craft()->templates->formatInputId('readability'));

		craft()->templates->includeJsResource('seo/js/seo-settings.js');
		craft()->templates->includeJs("new ReadabilitySorter('#{$namespaceId}');");

		$this->renderTemplate('seo/settings', array(
			'settings' => $settings,
			'fields' => $fields,
			'unsetFields' => $unsetFields,
			'tabs' => [
				['label' => 'Sitemap', 'url' => '#tab1', 'class' => null],
				['label' => 'Redirect', 'url' => '#tab2', 'class' => null],
				['label' => 'Fieldtype', 'url' => '#tab3', 'class' => null],
			],
			'crumbs' => [
				['label' => 'SEO', 'url' => 'seo'],
			]
		));
	}

}