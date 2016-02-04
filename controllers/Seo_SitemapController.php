<?php

namespace Craft;

class Seo_SitemapController extends BaseController
{
	protected $allowAnonymous = true;

	private $sitemap;

	public function init()
	{
		$this->sitemap = craft()->seo->getData('sitemap');
		parent::init();
	}

	public function actionGenerate ()
	{
		$sectionUrls = $this->_generateSections();

		HeaderHelper::setContentTypeByExtension('xml');
		HeaderHelper::setHeader(array('charset' => 'utf-8'));

		$path = craft()->path->getPluginsPath() . 'seo/templates';
		craft()->path->setTemplatesPath($path);

		$this->renderTemplate('_sitemap', array(
			'sectionUrls' => $sectionUrls,
			'customUrls' => $this->sitemap['customUrls'],
		));
	}

	private function _generateSections ()
	{
		$urls = [];

		foreach ($this->sitemap['sections'] as $sectionId => $section)
		{
			if ($section['enabled'])
				$urls = array_merge($urls, $this->_generateUrls($sectionId, $section));
		}

		return $urls;
	}

	private function _generateUrls ($id, $section)
	{
		$urls = [];

		$sect = craft()->elements->getCriteria(ElementType::Entry);
		$sect->sectionId = $id;

		foreach ($sect->find() as $elem) {
			$urls[] = [
				'url' => $elem->url,
				'lastmod' => $elem->dateUpdated,
				'frequency' => $section['frequency'],
				'priority' => $section['priority'],
			];
		}

		return $urls;
	}
}