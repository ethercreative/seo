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

		return craft()->templates->render('seo/seo-fieldtype', array(
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
		return craft()->plugins->getPlugin('seo')->generateSettingsHtml('seo/seo-fieldtype-settings', $this->getSettings());
	}

}