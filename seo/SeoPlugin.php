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

	// Variables
	// =========================================================================

	public static $commerceInstalled = false;

	// Details
	// =========================================================================

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
		return '2.0.0-beta';
	}

	public function getSchemaVersion()
	{
		return '0.1.1';
	}

	public function getDeveloper()
	{
		return 'Ether Creative';
	}

	public function getDeveloperUrl()
	{
		return 'http://ethercreative.co.uk';
	}

	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/ethercreative/seo/master/releases.json';
	}

	// Routes
	// =========================================================================

	public function hasCpSection()
	{
		return !craft()->isConsole() && (
			craft()->userSession->isAdmin() ||
			craft()->userSession->checkPermission('accessPlugin-seo')
		);
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
			$this->getSettings()->sitemapName . '.xml' =>
				['action' => 'seo/sitemap/index'],
			$this->getSettings()->sitemapName . '_custom.xml' =>
				['action' => 'seo/sitemap/custom'],
			$this->getSettings()->sitemapName . '_(?P<section>\w*)_(?P<id>\d*)_(?P<page>\d*)\.xml' =>
				["action" => "seo/sitemap/sitemap"],
		);
	}

	// Settings
	// =========================================================================

	protected function defineSettings()
	{
		return array(
			// Sitemap Settings
			"sitemapName"  => [AttributeType::String, "default" => "sitemap"],
			"sitemapLimit" => [AttributeType::Number, "default" => 1000],

			// Redirect Settings
			"publicPath"   => [AttributeType::String],

			// Fieldtype Settings
			"titleSuffix"  => [AttributeType::String],
			"metaTemplate" => [AttributeType::String],
		);
	}

	public function getSettingsUrl()
	{
		return 'seo/settings';
	}

	public function prepSettings($settings)
	{
		return parent::prepSettings($settings);
	}

	// Initializer
	// =========================================================================

	public function init()
	{
		// Check if commerce is installed
		SeoPlugin::$commerceInstalled =
			craft()->plugins->getPlugin("commerce") != null;

		// TODO: On category / section update, update sitemap

		if (
			craft()->request->isSiteRequest()
			&& !craft()->request->isLivePreview()
		) {
			// If request 404s, try to redirect
			craft()->onException = function(\CExceptionEvent $event) {
				craft()->seo_redirect->onException($event);
			};

			// Include Meta Markup in head via `{% hook "seo" %}`
			craft()->templates->hook("seo", function(&$context) {
				return craft()->seo->hook($context);
			});

			// Inject A/B
			craft()->on("elements.onPopulateElements", function (Event $event) {
				craft()->seo_ab->inject($event->params["elements"]);
			});
		}

		if (
			craft()->request->isCpRequest()
			&& !craft()->request->isAjaxRequest()
		) {
			// Load in SEO A/B JS
			craft()->templates->includeJsResource("seo/js/SeoAB.min.js");
			craft()->templates->includeJs("new SeoAB();");
		}
	}

	// Hooks
	// =========================================================================

	public function registerUserPermissions()
	{
		return array(
			'manageSitemap' => array('label' => Craft::t('Manage Sitemap')),
			'manageRedirects' => array('label' => Craft::t('Manage Redirects')),
		);
	}

}