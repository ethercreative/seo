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
		return '0.0.1';
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
			'titleSuffix' => array(AttributeType::String),
			'readability' => array(AttributeType::Mixed)
		);
	}

	public function getSettingsHtml()
	{
		$fieldsRaw = craft()->fields->getAllFields();
		$fields = [];

		foreach ($fieldsRaw as $field) {
			$fields[] = array(
				'label' => $field->name,
				'value' => $field->handle
			);
		}

		return craft()->templates->render('seo/settings', array(
			'settings' => $this->getSettings(),
			'fields' => $fields
		));
	}

}