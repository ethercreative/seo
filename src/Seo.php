<?php

namespace ether\seo;

use craft\base\Plugin;
use craft\events\ExceptionEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\TemplateEvent;
use craft\services\Fields;
use craft\web\ErrorHandler;
use craft\web\Request;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
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

		// Variable
		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			[$this, 'onRegisterVariable']
		);

		\Craft::$app->view->hook(
			'seo',
			[$this, 'onRegisterSeoHook']
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