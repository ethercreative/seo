<?php

namespace Craft;

class SeoFieldType extends BaseFieldType {

	public function getName()
	{
		return Craft::t('SEO');
	}

	public function defineContentAttribute()
	{
		return AttributeType::Mixed;
	}

	protected function defineSettings()
	{
		return array(
			'titleSuffix' => array(AttributeType::String),
			'readability' => array(AttributeType::Mixed)
		);
	}

	public function getInputHtml($name, $value)
	{
		$id = craft()->templates->formatInputId($name);
		$namespaceId = craft()->templates->namespaceInputId($id);

		$settings = $this->getSettings();
		$settingsGlobal = craft()->plugins->getPlugin('seo')->getSettings();
		$readability = implode("','", $settings->readability ?: $settingsGlobal->readability);

		craft()->templates->includeCssResource('seo/css/seo.css');
		craft()->templates->includeJsResource('seo/js/seo-field.js');
		craft()->templates->includeJs("new SeoField('{$namespaceId}', ['{$readability}']);");

		return craft()->templates->render('seo/_seo-fieldtype', array(
			'id' => $id,
			'name' => $name,
			'value' => $value,
			'titleSuffix' => $settings->titleSuffix ?: $settingsGlobal->titleSuffix,
			'isEntry' => $this->element->elementType === 'Entry',
			'isNew' => $this->element->title === null,
			'ref' => $this->element->getRef()
		));
	}

	public function getSettingsHtml()
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
		craft()->templates->includeJs("new SeoSettings.SortableList('#{$namespaceId}');");

		return craft()->templates->render('seo/_seo-fieldtype-settings', array(
			'settings' => $settings,
			'fields' => $fields,
			'unsetFields' => $unsetFields
		));
	}

}