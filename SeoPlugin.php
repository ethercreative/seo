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

	public function getSettingsUrl()
	{
		return 'seo/settings';
	}

	public function registerCpRoutes ()
	{
		return array(
			'seo/settings' => array('action' => 'seo/settings')
		);
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

}