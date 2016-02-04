<?php

namespace Craft;

class SeoController extends BaseController
{

	public $subNav = [];

	public function init()
	{
		$this->subNav = [
			'index' => ['label' => 'Statistics', 'url'=>'seo'],
		];

		if (craft()->userSession->isAdmin() || craft()->userSession->checkPermission('manageSitemap'))
			$this->subNav['sitemap'] = ['label' => 'Sitemap', 'url' => 'seo/sitemap'];

		if (craft()->userSession->isAdmin() || craft()->userSession->checkPermission('manageRedirects'))
			$this->subNav['redirects'] = ['label' => 'Redirects', 'url' => 'seo/redirects'];

		if (craft()->userSession->isAdmin())
			$this->subNav['settings'] = ['label' => 'Settings', 'url' => 'seo/settings'];

		parent::init();
	}


	// DB
	public function actionSaveData ()
	{
		$this->requirePostRequest();

		if (craft()->seo->saveData(craft()->request->getRequiredPost('name'), craft()->request->getRequiredPost('data'))) {
			craft()->userSession->setNotice(Craft::t('Updated.'));
		} else {
			craft()->userSession->setError(Craft::t('Couldn’t update.'));
		}

		$this->redirectToPostedUrl();
	}


	// PAGES
	public function actionIndex ()
	{
		$this->renderTemplate('seo/index', [
			'subnav' => $this->subNav,
			'selectedSubnavItem' => 'index',
		]);
	}

	public function actionSitemapPage ()
	{
		craft()->userSession->requirePermission('manageSitemap');

		$namespace = 'data';

		craft()->templates->includeJsResource('seo/js/seo-settings.js');
		craft()->templates->includeJs("new SeoSettings('{$namespace}', 'sitemap');");

		$this->renderTemplate('seo/sitemap', array(
			// Global
			'namespace' => $namespace,
			'sitemap' => craft()->seo->getData('sitemap'),
			'subnav' => $this->subNav,
			'selectedSubnavItem' => 'sitemap',

			// Misc
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],

			// Sitemap
			'sections' => craft()->seo_sitemap->getValidSections(),
		));
	}

	public function actionRedirectsPage ()
	{
		craft()->userSession->requirePermission('manageRedirects');

		$namespace = 'settings';

		$settings = craft()->seo->settings();

		craft()->templates->includeJsResource('seo/js/seo-settings.js');
		craft()->templates->includeJs("new SeoSettings('{$namespace}', 'redirects');");

		$this->renderTemplate('seo/redirects', array(
			// Global
			'namespace' => $namespace,
			'settings' => $settings,
			'subnav' => $this->subNav,
			'selectedSubnavItem' => 'redirects',

			// Misc
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],
		));
	}

	public function actionSettings ()
	{
		$namespace = 'settings';

		$settings = craft()->seo->settings();
		$fieldsRaw = craft()->fields->getAllFields();
		$fields = [];

		foreach ($fieldsRaw as $field) {
			if ($field->fieldType->name !== 'SEO') {
				$fields[$field->handle] = array(
					'label' => $field->name,
					'value' => $field->handle,
					'type' => $field->fieldType->name
				);
			}
		}

		$unsetFields = $fields;

		if ($settings->readability !== null) {
			foreach ($settings->readability as $field) {
				if ($unsetFields[$field])
					unset($unsetFields[$field]);
			}
		}

		craft()->templates->includeJsResource('seo/js/seo-settings.js');
		craft()->templates->includeJs("new SeoSettings('{$namespace}', 'settings');");

		$this->renderTemplate('seo/settings', array(
			// Global
			'namespace' => $namespace,
			'settings' => $settings,
			'subnav' => $this->subNav,
			'selectedSubnavItem' => 'settings',

			// Misc
			'tabs' => [
				['label' => 'Fieldtype', 'url' => "#{$namespace}-tab1", 'class' => null],
				['label' => 'General', 'url' => "#{$namespace}-tab2", 'class' => null],
			],
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],

			// Fieldtype
			'fields' => $fields,
			'unsetFields' => $unsetFields,
		));
	}

}