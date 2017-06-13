<?php

namespace Craft;

class Seo_SitemapService extends BaseApplicationComponent
{

	// Settings
	// =========================================================================

	private $_settings;

	public function settings ()
	{
		if ($this->_settings)
			return $this->_settings;

		$this->_settings = craft()->plugins->getPlugin('seo')->getSettings();
		return $this->_settings;
	}

	// Get & Save sitemap
	// =========================================================================

	public function getSitemap ()
	{
		$sitemapRaw = Seo_SitemapRecord::model()->findAll();
		$sitemap = [];

		foreach ($sitemapRaw as $row)
		{
			if (!array_key_exists($row['group'], $sitemap)) $sitemap[$row['group']] = [];

			if ($row['group'] == 'customUrls') {
				$sitemap[$row['group']][] = $row;
			} else {
				$sitemap[$row['group']][$row['url']] = $row;
			}
		}

		return $sitemap;
	}

	public function saveSitemap ($data)
	{
		$oldSitemap = $this->getSitemap();
		$newSitemap = $data;

		// Delete removed rows
		$newById = [];
		$oldById = [];

		$newRecordsRaw = [];

		foreach ($newSitemap as $group => $rows)
		{
			foreach ((array)$rows as $new)
			{
				if (!is_array($new)) continue;
				$new['group'] = $group;

				if (!array_key_exists('id', $new)) continue;

				if ($new['id'] != "-1") $newById[$new['id']] = $new;
				else $newRecordsRaw[] = $new;
			}
		}

		$idsToDelete = [];
		foreach ($oldSitemap as $group => $rows)
		{
			foreach ($rows as $old)
			{
				if (array_key_exists($old['id'], $newById)) {
					$oldById[$old['id']] = $old;
				} else {
					$idsToDelete[] = $old['id'];
				}
			}
		}

		if (!empty($idsToDelete)) {
			craft()->db->createCommand()->delete('seo_sitemaps', array('in', 'id', $idsToDelete));
		}

		// Update current rows
		foreach ($newById as $new)
		{
			$old = $oldById[$new['id']];

			if (
				$old['url'] !== $new['url'] ||
				$old['frequency'] !== $new['frequency'] ||
				$old['priority'] !== $new['priority'] ||
				$old['enabled'] !== !!$new['enabled']
			) {
				$old->setAttribute('url', $new['url']);
				$old->setAttribute('frequency', $new['frequency']);
				$old->setAttribute('priority', $new['priority']);
				$old->setAttribute('enabled', !!$new['enabled']);
				$old->save();
			}
		}

		// Add new rows
		foreach ($newRecordsRaw as $new)
		{
			$record = new Seo_SitemapRecord();
			$record->setAttribute('url', $new['url']);
			$record->setAttribute('frequency', $new['frequency']);
			$record->setAttribute('priority', $new['priority']);
			$record->setAttribute('enabled', !!$new['enabled']);
			$record->setAttribute('group', $new['group']);
			$record->save();
		}

		return true;
	}

	public function getValidSections ()
	{
		return array_filter(craft()->sections->allSections, function ($section) {
			return $section->urlFormat || $section->isHomepage();
		});
	}

	public function getValidCategories ()
	{
		return array_filter(craft()->categories->allGroups, function ($category) {
			return $category->hasUrls;
		});
	}

	public function getValidProductTypes ()
	{
		if (!SeoPlugin::$commerceInstalled) return array();

		return array_filter(craft()->commerce_productTypes->getAllProductTypes(), function ($productType) {
			return $productType->hasUrls;
		});
	}

	// Sitemap Generation
	// =========================================================================

	/** @var \DOMDocument */
	private $_document;

	/** @var \DOMElement */
	private $_urlSet;

	// Index
	// -------------------------------------------------------------------------

	/** @var \DOMElement */
	private $_index;

	public function index ()
	{
		$this->_createDocument(false);

		// Add Sitemap Index
		$this->_index = $this->_document->createElement('sitemapindex');
		$this->_index->setAttribute(
			'xmlns',
			'http://www.sitemaps.org/schemas/sitemap/0.9'
		);
		$this->_index->setAttribute(
			'xmlns:xhtml',
			'http://www.w3.org/1999/xhtml'
		);
		$this->_document->appendChild($this->_index);

		// Get the saved sitemap data
		$sitemapData = $this->getSitemap();

		// Generate Loop: Sections
		$this->_generateLoop(
			"sections",
			$this->getValidSections(),
			$sitemapData
		);

		// Generate Loop: Categories
		$this->_generateLoop(
			"categories",
			$this->getValidCategories(),
			$sitemapData
		);

		// Generate Loop: Product Types
		$this->_generateLoop(
			"productTypes",
			$this->getValidProductTypes(),
			$sitemapData
		);

		// Generate: Custom
		if (array_key_exists("customUrls", $sitemapData))
			$this->_generateIndex("custom", 0);

		return $this->_document->saveXML();
	}

	private function _generateLoop ($handle, $data, $sitemapData)
	{
		if (!array_key_exists($handle, $sitemapData))
			return;

		foreach ($data as $item)
			if (array_key_exists($item["id"], $sitemapData[$handle]))
				if ($sitemapData[$handle][$item["id"]]["enabled"])
					$this->_generateIndex($handle, $item["id"]);
	}

	private function _generateIndex ($group, $id)
	{
		switch ($group) {
			case "custom":
				$last = Seo_SitemapRecord::model()->find()->dateUpdated;
				$pages = 1;
				break;
			case "sections":
				$last = $this->_getUpdated(ElementType::Entry, $id);
				$pages = $this->_getPageCount(ElementType::Entry, $id);
				break;
			case "categories":
				$last = $this->_getUpdated(ElementType::Category, $id);
				$pages = $this->_getPageCount(ElementType::Category, $id);
				break;
			case "productTypes":
				$last = $this->_getUpdated("Commerce_Product", $id);
				$pages = $this->_getPageCount("Commerce_Product", $id);
				break;
			default:
				$last = DateTimeHelper::currentTimeForDb();
				$pages = 1;
		}

		for ($i = 0; $i < $pages; $i++)
		{
			$sitemap = $this->_document->createElement("sitemap");
			$this->_index->appendChild($sitemap);

			$loc = $this->_document->createElement(
				"loc",
				$this->_indexUrl($group, $id, $i)
			);
			$sitemap->appendChild($loc);

			$lastMod = $this->_document->createElement(
				"lastmod",
				$last
			);
			$sitemap->appendChild($lastMod);
		}
	}

	private function _indexUrl ($group, $id, $page)
	{
		return UrlHelper::getUrl(
			$this->settings()->sitemapName . "_" . $group .
			($id > 0 ? "_" . $id : "") .
			($id > 0 ? "_" . $page : "") . ".xml"
		);
	}

	// Custom
	// -------------------------------------------------------------------------

	public function custom ()
	{
		$this->_createDocument();
		$sitemapData = $this->getSitemap();

		if (!array_key_exists("customUrls", $sitemapData))
			return $this->_document->saveXML();

		foreach ($sitemapData["customUrls"] as $custom) if ($custom["enabled"])
		{
			$url = $this->_document->createElement("url");
			$loc = $this->_document->createElement(
				"loc",
				UrlHelper::getUrl($custom["url"])
			);
			$frequency = $this->_document->createElement(
				"changefreq",
				$custom["frequency"]
			);
			$priority = $this->_document->createElement(
				"priority",
				$custom["priority"]
			);

			$url->appendChild($loc);
			$url->appendChild($frequency);
			$url->appendChild($priority);

			$this->_urlSet->appendChild($url);
		}

		return $this->_document->saveXML();
	}

	// Sitemap
	// -------------------------------------------------------------------------

	public function sitemap (array $variables)
	{
		$this->_createDocument();
		$sitemapData = $this->getSitemap();

		if (!array_key_exists($variables["section"], $sitemapData))
			goto out;

		$sitemapSection = $sitemapData[$variables["section"]];

		if (!array_key_exists($variables["id"], $sitemapSection))
			goto out;

		$sitemapSectionById = $sitemapSection[$variables["id"]];

		if (!$sitemapSectionById["enabled"])
			goto out;

		$type = null;
		$idHandle = null;

		switch ($variables["section"]) {
			case "sections":
				$type = ElementType::Entry;
				$idHandle = "sectionId";
				break;
			case "categories":
				$type = ElementType::Category;
				$idHandle = "groupId";
				break;
			case "productTypes":
				$type = "Commerce_Product";
				$idHandle = "typeId";
				break;
			default:
				goto out;
		}

		$elements = craft()->elements->getCriteria($type);
		$elements->{$idHandle} = $variables["id"];
		$elements->limit = $this->settings()->sitemapLimit;
		$elements->offset = $this->settings()->sitemapLimit * $variables["page"];

		// TODO: Paginate
		foreach ($elements->find() as $item)
		{
			if ($item->url == null)
				continue;

			$url = $this->_document->createElement("url");
			$this->_urlSet->appendChild($url);

			$loc = $this->_document->createElement(
				"loc",
				UrlHelper::getUrl($item->url)
			);
			$mod = $this->_document->createElement(
				"lastmod",
				$item->dateUpdated
			);
			$freq = $this->_document->createElement(
				"changefreq",
				$sitemapSectionById["frequency"]
			);
			$priority = $this->_document->createElement(
				"priority",
				$sitemapSectionById["priority"]
			);

			$url->appendChild($loc);
			$url->appendChild($mod);
			$url->appendChild($freq);
			$url->appendChild($priority);

			if (is_array($item->locales) && count($item->locales) > 1) {
				foreach ($item->locales as $locale => $settings) {
					$locale = $type == ElementType::Category || $type == "Commerce_Product" ? $settings : $locale;

					if ($locale !== craft()->language) {
						$alt = $this->_document->createElement("xhtml:link");
						$alt->setAttribute("rel", "alternate");
						$alt->setAttribute(
							"hreflang",
							str_replace('_', '-', $locale)
						);
						$alt->setAttribute(
							"href",
							UrlHelper::getSiteUrl(
								($item->uri == '__home__') ? '' : $item->uri,
								null,
								null,
								$locale
							)
						);
						$url->appendChild($alt);
					}
				}
			}
		}

		out:
		return $this->_document->saveXML();
	}

	// Helpers
	// -------------------------------------------------------------------------

	private function _createDocument ($withUrlSet = true)
	{
		// Create XML document
		$document = new \DOMDocument('1.0', 'utf-8');

		// Pretty print for debugging
		if (craft()->config->get('devMode'))
			$document->formatOutput = true;

		if ($withUrlSet)
		{
			$urlSet = $document->createElement("urlset");
			$urlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
			$urlSet->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
			$document->appendChild($urlSet);
			$this->_urlSet = $urlSet;
		}

		$this->_document = $document;
	}

	private function _getUpdated ($type, $id)
	{
		$criteria = craft()->elements->getCriteria($type);
		$criteria->sectionId = $id;
		$criteria->limit = 1;
		$criteria->order = "dateUpdated";
		return $criteria->first()->dateUpdated;
	}

	private function _getPageCount ($type, $id)
	{
		$criteria = craft()->elements->getCriteria($type);
		$criteria->sectionId = $id;
		return ceil($criteria->total() / $this->settings()->sitemapLimit);
	}

}