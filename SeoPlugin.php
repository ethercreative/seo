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
		return '0.0.6';
	}

	public function getSchemaVersion()
	{
		return '0.0.7';
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

	public function hasCpSection()
	{
		return (craft()->userSession->isAdmin() || craft()->userSession->checkPermission('accessPlugin-seo'));
	}

	public function registerCpRoutes ()
	{
		return [
			'seo' => array('action' => 'seo/index'),
			'seo/sitemap' => array('action' => 'seo/sitemapPage'),
			'seo/redirects' => array('action' => 'seo/redirectsPage'),
			'seo/settings' => array('action' => 'seo/settings'),
		];
	}

	public function registerSiteRoutes ()
	{
		return array(
			$this->getSettings()->sitemapName . '.xml' => array('action' => 'seo/sitemap/generate')
		);
	}

	protected function defineSettings()
	{
		return array(
			// Sitemap Settings
			'sitemapName' => array(AttributeType::String, 'default' => 'sitemap'),
			'sections' => array(AttributeType::Mixed),
			'customUrls' => array(AttributeType::Mixed),

			// Fieldtype Settings
			'titleSuffix' => array(AttributeType::String),
			'readability' => array(AttributeType::Mixed),
			'fieldTemplates' => array(AttributeType::Mixed)
		);
	}

	public function registerUserPermissions()
	{
		return array(
			'manageSitemap' => array('label' => Craft::t('Manage Sitemap')),
			'manageRedirects' => array('label' => Craft::t('Manage Redirects')),
		);
	}

}