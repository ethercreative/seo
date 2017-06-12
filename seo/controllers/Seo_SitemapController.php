<?php

namespace Craft;

class Seo_SitemapController extends BaseController
{
	protected $allowAnonymous = true;

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

	/**
	 * Renders the sitemap Index
	 */
	public function actionIndex ()
	{
		HeaderHelper::setContentTypeByExtension('xml');
		HeaderHelper::setHeader(array('charset' => 'utf-8'));

		echo craft()->seo_sitemap->index();
	}

	/**
	 * Renders the sitemap for custom URLs
	 */
	public function actionCustom ()
	{
		HeaderHelper::setContentTypeByExtension('xml');
		HeaderHelper::setHeader(array('charset' => 'utf-8'));

		echo craft()->seo_sitemap->custom();
	}

	/**
	 * Renders the sitemaps for craft-defined URLs
	 * (i.e. sections, categories, etc.)
	 *
	 * @param array $variables
	 */
	public function actionSitemap (array $variables = [])
	{
		HeaderHelper::setContentTypeByExtension('xml');
		HeaderHelper::setHeader(array('charset' => 'utf-8'));

		echo craft()->seo_sitemap->sitemap($variables);
	}

}