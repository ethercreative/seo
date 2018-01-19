<?php

namespace ether\seo;

use craft\base\Plugin;
use craft\events\ExceptionEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Fields;
use craft\web\ErrorHandler;
use craft\web\Request;
use craft\web\UrlManager;
use ether\seo\fields\SeoField;
use ether\seo\models\Settings;
use ether\seo\services\RedirectsService;
use yii\base\Event;

/**
 * Class Seo
 *
 * @package ether\seo
 *
 * @property RedirectsService $redirects
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

	// Craft
	// =========================================================================

	public function init ()
	{
		parent::init();
		self::$i = self::getInstance();

		// Components
		// ---------------------------------------------------------------------

		$this->setComponents([
			'redirects' => RedirectsService::class,
		]);

		// Events
		// ---------------------------------------------------------------------

		Event::on(
			UrlManager::className(),
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			[$this, 'onRegisterCPUrlRules']
		);

		Event::on(
			ErrorHandler::className(),
			ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION,
			[$this, 'onBeforeHandleException']
		);

		Event::on(
			Fields::className(),
			Fields::EVENT_REGISTER_FIELD_TYPES,
			[$this, 'onRegisterFieldTypes']
		);
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

	// Events
	// =========================================================================

	public function onRegisterCPUrlRules (RegisterUrlRulesEvent $event)
	{
		$event->rules['seo']           = 'seo/seo/index';
//		$event->rules['seo/sitemap']   = 'seo/sitemap/index';

		// Redirects
		// ---------------------------------------------------------------------
		$event->rules['seo/redirects'] = 'seo/redirects/index';
		$event->rules['PUT seo/redirects'] = 'seo/redirects/save';
		$event->rules['DELETE seo/redirects'] = 'seo/redirects/delete';
	}

	/**
	 * @param ExceptionEvent $event
	 *
	 * @throws \yii\base\Exception
	 */
	public function onBeforeHandleException (ExceptionEvent $event)
	{
		$this->redirects->onException($event);
	}

	public function onRegisterFieldTypes (RegisterComponentTypesEvent $event)
	{
		$event->types[] = SeoField::class;
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