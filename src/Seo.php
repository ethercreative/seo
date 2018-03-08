<?php

namespace ether\seo;

use craft\base\Element;
use craft\base\Field;
use craft\base\Plugin;
use craft\events\ExceptionEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\web\Application;
use craft\web\ErrorHandler;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use ether\seo\fields\SeoField;
use ether\seo\listeners\GetCraftQLSchema;
use ether\seo\models\Settings;
use ether\seo\services\RedirectsService;
use ether\seo\services\SitemapService;
use yii\base\Event;

/**
 * Class Seo
 *
 * @package ether\seo
 *
 * @property SitemapService     $sitemap
 * @property RedirectsService   $redirects
 */
class Seo extends Plugin
{

	// Variables
	// =========================================================================

	/** @var Seo */
	public static $i;

	public $controllerNamespace = 'ether\\seo\\controllers';
	public $hasCpSection        = true;
	public $hasCpSettings       = true;

	public $changelogUrl =
		'https://raw.githubusercontent.com/ethercreative/seo/v3/CHANGELOG.md';
	public $downloadUrl  =
		'https://github.com/ethercreative/seo/archive/v3.zip';
	public $documentationUrl =
		'https://github.com/ethercreative/seo/blob/v3/README.md';

	// Craft
	// =========================================================================

	public function init ()
	{
		parent::init();
		self::$i = self::getInstance();

		$craft = \Craft::$app;

		// Components
		// ---------------------------------------------------------------------

		$this->setComponents([
			'sitemap' => SitemapService::class,
			'redirects' => RedirectsService::class,
		]);

		// Events
		// ---------------------------------------------------------------------

		// User Permissions
		if ($craft->getEdition() !== \Craft::Personal) {
			Event::on(
				UserPermissions::class,
				UserPermissions::EVENT_REGISTER_PERMISSIONS,
				[$this, 'onRegisterPermissions']
			);
		}

		// CP URLs
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			[$this, 'onRegisterCPUrlRules']
		);

		// Site URLs
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_SITE_URL_RULES,
			[$this, 'onRegisterSiteUrlRules']
		);

		// Field type
		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			[$this, 'onRegisterFieldTypes']
		);

		// Variable
		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			[$this, 'onRegisterVariable']
		);

		// 404 Exceptions
		if (
			$craft->request->isSiteRequest
			&& !$craft->request->isConsoleRequest
			&& !$craft->request->isLivePreview
		) {
			Event::on(
				ErrorHandler::class,
				ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION,
				[$this, 'onBeforeHandleException']
			);
		}

		// Request Headers
		if ($craft->request->isSiteRequest)
		{
			Event::on(
				Application::class,
				Application::EVENT_AFTER_REQUEST,
				[$this, 'onAfterRequest']
			);
		}

		// Template Hook
		\Craft::$app->view->hook(
			'seo',
			[$this, 'onRegisterSeoHook']
		);

		// CraftQL Support
		/** @noinspection PhpUndefinedNamespaceInspection */
		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		if (class_exists(\markhuot\CraftQL\CraftQL::class)) {
			Event::on(
				SeoField::class,
				'craftQlGetFieldSchema',
				[new GetCraftQLSchema, 'handle']
			);
		}
	}

	public function getCpNavItem ()
	{
		$item = parent::getCpNavItem();
		$currentUser = \Craft::$app->user;

		$subNav = [
			'dashboard' => ['label' => 'Dashboard', 'url' => 'seo'],
		];

		if ($currentUser->getIsAdmin() || $currentUser->can('manageSitemap'))
			$subNav['sitemap'] =
				['label' => 'Sitemap', 'url' => 'seo/sitemap'];

		if ($currentUser->getIsAdmin() || $currentUser->can('manageRedirects'))
			$subNav['redirects'] =
				['label' => 'Redirects', 'url' => 'seo/redirects'];

		/*if ($currentUser->getIsAdmin() || $currentUser->can('manageSchema'))
			$subNav['schema'] =
				['label' => 'Schema', 'url' => 'seo/schema'];*/

		if ($currentUser->getIsAdmin())
			$subNav['settings'] =
				['label' => 'Settings', 'url' => 'settings/plugins/seo'];

		$item['subnav'] = $subNav;

		return $item;
	}

	// Craft: Settings
	// -------------------------------------------------------------------------

	protected function createSettingsModel (): Settings
	{
		return new Settings();
	}

	/**
	 * @return string
	 * @throws \Twig_Error_Loader
	 * @throws \yii\base\Exception
	 */
	protected function settingsHtml (): string
	{
		$settings = $this->getSettings();
		$settings->validate();

		$tabs = [
			[
				'label' => 'Fieldtype',
				'url'   => "#settings-tab1",
				'class' => null,
			],
			[
				'label' => 'Sitemap',
				'url'   => "#settings-tab2",
				'class' => null,
			],
		];

		return \Craft::$app->view->renderTemplate(
			'seo/settings',
			array_merge(
				compact('settings', 'tabs'),
				self::getFieldTypeSettingsVariables()
			)
		);
	}

	// Components
	// =========================================================================

	public function getRedirects (): RedirectsService
	{
		return $this->redirects;
	}

	public function getSitemap (): SitemapService
	{
		return $this->sitemap;
	}

	// Events
	// =========================================================================

	public function onRegisterPermissions (RegisterUserPermissionsEvent $event)
	{
		$event->permissions['SEO'] = [
			'manageSitemap' => [
				'label' => \Craft::t('seo', 'Manage Sitemap'),
			],
			'manageRedirects' => [
				'label' => \Craft::t('seo', 'Manage Redirects'),
			],
//			'manageSchema' => [
//				'label' => \Craft::t('seo', 'Manage Schema'),
//			],
		];
	}

	public function onRegisterCPUrlRules (RegisterUrlRulesEvent $event)
	{
		$event->rules['seo'] = 'seo/seo/index';

		// Sitemap
		// ---------------------------------------------------------------------
		$event->rules['POST seo/sitemap'] = 'seo/sitemap/save';
		$event->rules['seo/sitemap'] = 'seo/sitemap/index';

		// Redirects
		// ---------------------------------------------------------------------
		$event->rules['DELETE seo/redirects'] = 'seo/redirects/delete';
		$event->rules['POST seo/redirects'] = 'seo/redirects/save';
		$event->rules['PUT seo/redirects'] = 'seo/redirects/bulk';
		$event->rules['seo/redirects'] = 'seo/redirects/index';

		// Schema
		// ---------------------------------------------------------------------
//		$event->rules['seo/schema'] = 'seo/schema/index';
	}

	public function onRegisterSiteUrlRules (RegisterUrlRulesEvent $event)
	{
		$sitemapName = $this->getSettings()->sitemapName;

		$event->rules[$sitemapName . '.xml'] = 'seo/sitemap/xml/index';
		$event->rules[$sitemapName . '_<section:\w*>_<id:\d*>_<page:\d*>.xml'] = 'seo/sitemap/xml/core';
		$event->rules[$sitemapName . '_custom.xml'] = 'seo/sitemap/xml/custom';
	}

	public function onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = SeoField::class;
	}

	/**
	 * @param Event $event
	 *
	 * @throws \yii\base\InvalidConfigException
	 */
	public function onRegisterVariable (Event $event)
	{
		/** @var CraftVariable $variable */
		$variable = $event->sender;
		$variable->set('seo', Variable::class);
	}

	/**
	 * @param ExceptionEvent $event
	 *
	 * @throws \yii\base\Exception
	 * @throws \yii\base\ExitException
	 */
	public function onBeforeHandleException (ExceptionEvent $event)
	{
		$this->redirects->onException($event);
	}

	public function onAfterRequest ()
	{
		$headers = \Craft::$app->getResponse()->getHeaders();
		$resolve = \Craft::$app->request->resolve()[1];
		$variables = array_key_exists('variables', $resolve)
			? $resolve['variables']
			: [];

		// If devMode always noindex
		if (\Craft::$app->config->general->devMode)
		{
			$headers->set('x-robots-tag', 'none, noimageindex');
			return;
		}

		$robots = [];
		$expiry = null;

		// Get all available "top-level" SEO fields
		foreach ($variables as $variable)
		{
			if (!is_subclass_of($variable, Element::class))
				continue;

			/** @var Element $variable */

			/** @var Field $field */
			foreach ($variable->fieldLayout->getFields() as $field)
				if (get_class($field) === SeoField::class)
					$robots = array_merge(
						$robots,
						$variable->{$field->handle}['advanced']['robots']
					);

			/** @var \DateTime $expiry */
			if ($expiry = $variable->expiryDate)
				$expiry = $expiry->format(\DATE_RFC850);
		}

		// If we don't have any variables (i.e. when just rendering a template)
		// fallback to the site-wide robots settings
		if (empty($variables))
			$robots = $this->getSettings()->robots;

		// Remove empties and duplicates (on the off-chance)
		$robots = array_filter(array_unique($robots));

		// If we've got robots, add the header
		if (!empty($robots))
			$headers->set('x-robots-tag', implode(', ', $robots));

		// If we've got an expiry time, add an additional header
		if ($expiry)
			$headers->add('x-robots-tag', 'unavailable_after: ' . $expiry);
	}

	/**
	 * @param $context
	 *
	 * @return string
	 * @throws \Twig_Error_Loader
	 * @throws \yii\base\Exception
	 */
	public function onRegisterSeoHook (&$context)
	{
		$craft = \Craft::$app;
		$metaTemplateName = $this->getSettings()['metaTemplate'];

		if ($metaTemplateName)
			return $craft->view->renderTemplate(
				$metaTemplateName,
				$context
			);

		$oldTemplateMode = $craft->view->getTemplateMode();
		$craft->view->setTemplateMode(View::TEMPLATE_MODE_CP);
		$rendered = $craft->view->renderTemplate(
			'seo/_seo/meta',
			$context
		);
		$craft->view->setTemplateMode($oldTemplateMode);
		return $rendered;
	}

	// Misc
	// =========================================================================

	public static function getFieldTypeSettingsVariables ()
	{
		$volumes = \Craft::$app->volumes->getPublicVolumes();

		return compact(
			'volumes'
		);
	}

}