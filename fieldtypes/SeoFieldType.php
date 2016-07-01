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
			'titleSuffix' => array(AttributeType::String)
		);
	}

	public function getInputHtml($name, $value)
	{
		$id = craft()->templates->formatInputId($name);
		$namespaceId = craft()->templates->namespaceInputId($id);

		$settings = $this->getSettings();
		$settingsGlobal = craft()->plugins->getPlugin('seo')->getSettings();
		$hasSection = false;
		$isEntry = false;
		if (!empty($this->element)) {
			switch ($this->element->getElementType()) {
				case "Entry":
					$isEntry = true;
					$hasSection = craft()->sections->isSectionTemplateValid($this->element->section);
					break;
				case "Commerce_Product":
					$hasSection = craft()->commerce_productTypes->isProductTypeTemplateValid($this->element->type);
					break;
			}
			$hasSectionString = $hasSection ? 'true' : 'false';

			craft()->templates->includeCssResource('seo/css/seo.css');
			craft()->templates->includeJsResource('seo/js/seo-field.js');
			craft()->templates->includeJs("new SeoField('{$namespaceId}', {$hasSectionString});");

			$url = $this->element->getUrl();

			if ($hasSection && $isEntry && $this->element->uri != '__home__' && $this->element->section->type != 'single')
				$url = substr($url, 0, strrpos( $url, '/')) . '/';

			return craft()->templates->render('seo/_seo-fieldtype', array(
				'id' => $id,
				'name' => $name,
				'value' => $value,
				'titleSuffix' => $settings->titleSuffix ?: $settingsGlobal->titleSuffix,
				'hasSection' => $hasSection,
				'isNew' => $this->element->title === null,
				'isHome' => $this->element->uri == '__home__',
				'url' => $url,
			));
		}
	}

	public function getSettingsHtml()
	{
		$settings = craft()->plugins->getPlugin('seo')->getSettings();

		$namespaceId = craft()->templates->namespaceInputId(craft()->templates->formatInputId('readability'));

		craft()->templates->includeJsResource('seo/js/seo-settings.js');
		craft()->templates->includeJs("new SeoSettings.SortableList('#{$namespaceId}');");

		return craft()->templates->render('seo/_seo-fieldtype-settings', array(
			'settings' => $settings
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
