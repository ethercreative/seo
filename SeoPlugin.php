<?php

namespace Craft;

/**
 * SEO for Craft CMS
 *
 * @author    Ether Creative <hello@ethercreative.co.uk>
 * @copyright Copyright (c) 2016, Ether Creative
 * @license   http://ether.mit-license.org/
 * @since     1.0
 */
class SeoPlugin extends BasePlugin {

	public function getName()
	{
		return 'SEO';
	}

	public function getDescription()
	{
		return 'Search engine optimization utilities';
	}

	public function getVersion()
	{
		return '0.0.3';
	}

	public function getDeveloper()
	{
		return 'Ether Creative';
	}

	public function getDeveloperUrl()
	{
		return 'http://ethercreative.co.uk';
	}

	protected function defineSettings()
	{
		return array(
			// Fieldtype Settings
			'titleSuffix' => array(AttributeType::String),
			'readability' => array(AttributeType::Mixed),
			'fieldTemplates' => array(AttributeType::Mixed)
		);
	}

	public function getSettingsHtml()
	{
		return $this->generateSettingsHtml('seo/settings', $this->getSettings());
	}

	public function generateSettingsHtml ($template, $settings) {
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

		return craft()->templates->render($template, array(
			'settings' => $settings,
			'fields' => $fields,
			'unsetFields' => $unsetFields
		));
	}

}