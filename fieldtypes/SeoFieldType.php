<?php

namespace Craft;

class SeoFieldType extends BaseFieldType implements IPreviewableFieldType {

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

		$url = $this->element->getUrl();

		if ($this->element->uri != '__home__' && $this->element->section->type != 'single')
			$url = substr($url, 0, strrpos( $url, '/')) . '/';

		return craft()->templates->render('seo/_seo-fieldtype', array(
			'id' => $id,
			'name' => $name,
			'value' => $value,
			'titleSuffix' => $settings->titleSuffix ?: $settingsGlobal->titleSuffix,
			'isEntry' => $this->element->elementType === 'Entry',
			'isNew' => $this->element->title === null,
			'isHome' => $this->element->uri == '__home__',
			'url' => $url,
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

	public function getTableAttributeHtml($value)
	{
		$ret = '';

		switch ($value['score']) {
			case '':
				$ret = '<span class="status active" style="margin-top:5px;background:#ccc;" title="Unranked"></span>';
				break;
			case 'bad':
				$ret = '<span class="status active" style="margin-top:5px;background:#ff4750;" title="Bad"></span>';
				break;
			case 'ok':
				$ret = '<span class="status active" style="margin-top:5px;background:#ffab47;" title="Okay"></span>';
				break;
			case 'good':
				$ret = '<span class="status active" style="margin-top:5px;background:#3eda80;" title="Good"></span>';
				break;
		}

		return $ret;
	}

}