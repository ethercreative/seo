<?php

namespace Craft;

class SeoController extends BaseController
{

	public $subNav = [];

	public function init()
	{
		$this->subNav = [
			'index' => ['label' => 'Dashboard', 'url'=>'seo'],
		];

		if (craft()->userSession->isAdmin() || craft()->userSession->checkPermission('manageSitemap'))
			$this->subNav['sitemap'] = ['label' => 'Sitemap', 'url' => 'seo/sitemap'];

		if (craft()->userSession->isAdmin() || craft()->userSession->checkPermission('manageRedirects'))
			$this->subNav['redirects'] = ['label' => 'Redirects', 'url' => 'seo/redirects'];

		if (craft()->userSession->isAdmin())
			$this->subNav['settings'] = ['label' => 'Settings', 'url' => 'seo/settings'];

		parent::init();
	}


	// DATA
	public function actionSaveRedirects ()
	{
		$this->actionSaveData();
	}

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

		craft()->templates->includeJsResource('seo/js/seo-settings.min.js');
		craft()->templates->includeJs("new SeoSettings('{$namespace}', 'sitemap');");

		$this->renderTemplate('seo/sitemap', array(
			// Global
			'namespace' => $namespace,
			'subnav' => $this->subNav,
			'selectedSubnavItem' => 'sitemap',

			// Misc
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],

			// Sitemap
			'sitemap' => craft()->seo_sitemap->getSitemap(),
			'sections' => craft()->seo_sitemap->getValidSections(),
			'categories' => craft()->seo_sitemap->getValidCategories(),
			'productTypes' => craft()->seo_sitemap->getValidProductTypes(),
		));
	}

	public function actionRedirectsPage ()
	{
		craft()->userSession->requirePermission('manageRedirects');

		$namespace = 'data';

		craft()->templates->includeCssResource('seo/css/redirects.css');
		craft()->templates->includeJsResource('seo/js/seo-settings.min.js');
		craft()->templates->includeJs("new SeoSettings('{$namespace}', 'redirects');");

		$this->renderTemplate('seo/redirects', array(
			// Global
			'namespace' => $namespace,
			'subnav' => $this->subNav,
			'selectedSubnavItem' => 'redirects',

			// Misc
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],

			// Redirecs
			'redirects' => craft()->seo_redirect->getAllRedirects(),
		));
	}

	public function actionSettings ()
	{
		$namespace = 'settings';

		$settings = craft()->seo->settings();

		craft()->templates->includeJsResource('seo/js/seo-settings.min.js');
		craft()->templates->includeJs("new SeoSettings('{$namespace}', 'settings');");

		// Get all SEO fields
		$seoFieldIds = [];
		$seoFieldsById = [];
		foreach (craft()->fields->getAllFields() as $field) {
			if ($field->getFieldType()->getClassHandle() == "Seo") {
				$id = $field->getAttribute('id');
				$seoFieldIds[] = $id;
				$seoFieldsById[$id] = $field;
			}
		}

		// Get all entry types that have an SEO field
		$allSections = craft()->sections->getAllSections();
		$allEntryTypes = [];
		foreach ($allSections as $section) {
			/** @var EntryTypeModel $entryType */
			foreach ($section->getEntryTypes() as $entryType) {
				$fieldLayout = craft()->fields->getLayoutById($entryType->getAttribute('fieldLayoutId'));
				$fieldIds = array_intersect($seoFieldIds, $fieldLayout->getFieldIds());

				if (count($fieldIds) < 1) continue;

				$allEntryTypes[] = [
					'section' => $section,
					'entryType' => $entryType,
					'seoFieldIds' => $fieldIds,
				];
			}
		}

		// TODO: All category groups
		// TODO: All global sets
		// TODO: All product types
		// TODO: Hooks for custom element types

//		$elementTypes = craft()->elements->getAllElementTypes();
//		$seoFields = [];
//
//		$elementIndex = 0;
//		$fieldIndex = 0;
//		foreach ($elementTypes as $elementType) {
//			if (!$elementType->hasTitles()) continue;
//
//			$fields = craft()->fields->getFieldsByElementType($elementType->getClassHandle());
//			foreach ($fields as $field) {
//				if ($field->getFieldType()->getClassHandle() == "Seo") {
//					$seoFields[] = [
//						'field' => $field,
//						'elementType' => $elementType,
//						'elementTypeClassName' => get_class($elementType),
//
//						'elementIndex' => $elementIndex,
//						'fieldIndex' => $fieldIndex,
//					];
//					$fieldIndex++;
//				}
//			}
//
//			$elementIndex++;
//		}

		$this->renderTemplate('seo/settings', array(
			// Global
			'namespace' => $namespace,
			'settings' => $settings,
			'subnav' => $this->subNav,
			'selectedSubnavItem' => 'settings',

			// Misc
			'tabs' => [
				['label' => 'Fieldtype', 'url' => "#{$namespace}-tab1", 'class' => null],
				['label' => 'Sitemap', 'url' => "#{$namespace}-tab2", 'class' => null],
			],
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],

			// Populate Fields
			'seoFields' => $seoFieldsById,
			'entries' => $allEntryTypes,
		));
	}

}
