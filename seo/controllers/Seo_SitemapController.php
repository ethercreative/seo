<?php

namespace Craft;

class Seo_SitemapController extends BaseController
{
	protected $allowAnonymous = true;

	private $sitemap;

	public function init()
	{
		$this->sitemap = craft()->seo_sitemap->getSitemap();
		parent::init();
	}

	public function actionSaveSitemap ()
	{
		$this->requirePostRequest();

		if (craft()->seo_sitemap->saveSitemap(craft()->request->getRequiredPost('data'))) {
			craft()->userSession->setNotice(Craft::t('Sitemap updated.'));
		} else {
			craft()->userSession->setError(Craft::t('Couldn’t update sitemap.'));
		}

		$this->redirectToPostedUrl();
	}

	public function actionGenerate ()
	{
		$sectionUrls = $this->_generateSections();
		$categoryUrls = $this->_generateCategories();
		$productTypeUrls = $this->_generateProductTypes();

		HeaderHelper::setContentTypeByExtension('xml');
		HeaderHelper::setHeader(array('charset' => 'utf-8'));

		$path = craft()->path->getPluginsPath() . 'seo/templates';
		craft()->templates->setTemplatesPath($path);

		$this->renderTemplate('_sitemap', array(
			'sectionUrls' => $sectionUrls,
			'categoryUrls' => $categoryUrls,
			'productTypeUrls' => $productTypeUrls,
			'customUrls' => array_key_exists('customUrls', $this->sitemap) ? $this->sitemap['customUrls'] : [],
		));
	}

	private function _generateSections ()
	{
		$urls = [];

		if (array_key_exists('sections', $this->sitemap) && !empty($this->sitemap['sections'])) {
			foreach ($this->sitemap['sections'] as $sectionId => $section)
			{
				if ($section['enabled'])
					$urls = array_merge($urls, $this->_generateUrls($sectionId, $section, ElementType::Entry));
			}
		}

		return $urls;
	}

	private function _generateCategories ()
	{
		$urls = [];

		if (array_key_exists('categories', $this->sitemap) && !empty($this->sitemap['categories'])) {
			foreach ($this->sitemap['categories'] as $categoryId => $category)
			{
				if ($category['enabled'])
					$urls = array_merge($urls, $this->_generateUrls($categoryId, $category, ElementType::Category));
			}
		}

		return $urls;
	}

	private function _generateProductTypes ()
	{
		if (!SeoPlugin::$commerceInstalled) return array();

		$urls = [];

		if (array_key_exists('productTypes', $this->sitemap) && !empty($this->sitemap['productTypes'])) {
			foreach ($this->sitemap['productTypes'] as $productTypeId => $productType)
			{
				if ($productType['enabled'])
					$urls = array_merge($urls, $this->_generateUrls($productTypeId, $productType, 'Commerce_Product'));
			}
		}

		return $urls;
	}

	private function _generateUrls ($id, $section, $elemType)
	{
		$urls = [];

		$sect = craft()->elements->getCriteria($elemType);
		$sect->sectionId = $id;
		$sect->limit = null;

		foreach ($sect->find() as $elem) {

			if ($elem->url !== null) {

				$urlAlts = [];

				if (is_array($elem->locales) && count($elem->locales) > 1) {
					foreach ($elem->locales as $locale => $settings) {
						$locale = ($elemType == ElementType::Category) || ($elemType == 'Commerce_Product') ? $settings : $locale;

						if ($locale !== craft()->language) {
							$urlAlts[] = [
								'locale' => str_replace('_', '-', $locale),
								'url' => UrlHelper::getSiteUrl(($elem->uri == '__home__') ? '' : $elem->uri, null, null, $locale)
							];
						}
					}
				}

				$urls[] = [
					'url' => $elem->url,
					'urlAlts' => $urlAlts,
					'lastmod' => $elem->dateUpdated,
					'frequency' => $section['frequency'],
					'priority' => $section['priority'],
				];

			}
		}

		return $urls;
	}
}