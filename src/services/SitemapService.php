<?php

namespace ether\seo\services;

use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\db\Query;
use craft\elements\Category;
use craft\elements\db\CategoryQuery;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use craft\models\CategoryGroup;
use craft\models\Section;
use DateTime;
use DOMDocument;
use DOMElement;
use ether\seo\fields\SeoField;
use ether\seo\models\data\SeoData;
use ether\seo\records\SitemapRecord;
use ether\seo\Seo;
use yii\db\Exception;

class SitemapService extends Component
{

	// Get & Save Sitemap
	// =========================================================================

	public function getSitemap ()
	{
		$sitemapRaw = SitemapRecord::find()->all();
		$sitemap = [];

		/** @var SitemapRecord $row */
		foreach ($sitemapRaw as $row)
		{
			if (!array_key_exists($row->group, $sitemap))
				$sitemap[$row->group] = [];

			if ($row->group === 'customUrls')
				$sitemap[$row->group][] = $row;
			else
				$sitemap[$row->group][$row->url] = $row;
		}

		return $sitemap;
	}

	public function saveSitemap ($data)
	{
		$oldSitemap = $this->getSitemap();
		$newSitemap = $data;

		// Delete removed rows
		// ---------------------------------------------------------------------
		$newById = [];
		$oldById = [];

		$newRecordsRaw = [];

		foreach ($newSitemap as $group => $rows)
		{
			foreach ((array)$rows as $new)
			{
				if (!is_array($new))
					continue;

				$new['group'] = $group;

				if (!array_key_exists('id', $new))
					continue;

				if ($new['id'] !== '-1') $newById[$new['id']] = $new;
				else $newRecordsRaw[] = $new;
			}
		}

		$idsToDelete = [];

		foreach ($oldSitemap as $group => $rows)
		{
			foreach ($rows as $old)
			{
				if (array_key_exists($old['id'], $newById))
					$oldById[$old['id']] = $old;
				else
					$idsToDelete[] = $old['id'];
			}
		}

		if (!empty($idsToDelete))
		{
			try {
				Craft::$app->db->createCommand()->delete(
					SitemapRecord::$tableName,
					['in', 'id', $idsToDelete]
				)->execute();
			} catch (Exception $e) {
				Craft::$app->log->logger->log(
					$e->getMessage(),
					LOG_ERR,
					'seo'
				);
				return false;
			}
		}

		// Update current rows
		// ---------------------------------------------------------------------
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
		// ---------------------------------------------------------------------
		foreach ($newRecordsRaw as $new)
		{
			$record = new SitemapRecord();
			$record->setAttribute('url', $new['url']);
			$record->setAttribute('frequency', $new['frequency']);
			$record->setAttribute('priority', $new['priority']);
			$record->setAttribute('enabled', !!$new['enabled']);
			$record->setAttribute('group', $new['group']);
			$record->save();
		}

		return true;
	}

	// Get Sections
	// =========================================================================

	public function getValidSections ()
	{
		return array_filter(
			Craft::$app->sections->getAllSections(),
			[$this, '_filterOutNoUrls']
		);
	}

	public function getValidCategories ()
	{
		return array_filter(
			Craft::$app->categories->getAllGroups(),
			[$this, '_filterOutNoUrls']
		);
	}

	public function getValidProductTypes ()
	{
		if (!Seo::$commerceInstalled) return [];

		return array_filter(
			\craft\commerce\Plugin::getInstance()->productTypes->getAllProductTypes(),
			[$this, '_filterOutNoUrls']
		);
	}

	// Sitemap XML
	// =========================================================================

	/** @var DOMDocument */
	private $_document;

	/** @var DOMElement */
	private $_urlSet;

	// Index
	// -------------------------------------------------------------------------

	/** @var DOMElement */
	private $_index;

	/**
	 * @throws \yii\base\Exception
	 */
	public function index ()
	{
		$this->_createDocument(false);

		// Add the Sitemap Index
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
			'sections',
			$this->getValidSections(),
			$sitemapData
		);

		// Generate Loop: Categories
		$this->_generateLoop(
			'categories',
			$this->getValidCategories(),
			$sitemapData
		);

		// Generate Loop: Product Types
		$this->_generateLoop(
			'productTypes',
			$this->getValidProductTypes(),
			$sitemapData
		);

		// Generate: Custom
		if (array_key_exists('customUrls', $sitemapData))
			$this->_generateIndex('custom', 0);

		return $this->_document->saveXML();
	}

	// Core
	// -------------------------------------------------------------------------

	/**
	 * @param array $variables
	 *
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public function core (array $variables)
	{
		$this->_createDocument();
		$sitemapData = $this->getSitemap();
		$craft = Craft::$app;

		if (!array_key_exists($variables['section'], $sitemapData))
			goto out;

		$sitemapSection = $sitemapData[$variables['section']];

		if (!array_key_exists($variables['id'], $sitemapSection))
			goto out;

		$sitemapSectionById = $sitemapSection[$variables['id']];

		if (!$sitemapSectionById['enabled'])
			goto out;

		/** @var Element|null $type */
		$type = null;
		$idHandle = null;

		switch ($variables['section'])
		{
			case 'sections':
				$type = Entry::instance();
				$idHandle = 'sectionId';
				break;

			case 'categories':
				$type = Category::instance();
				$idHandle = 'groupId';
				break;
		
			case 'productTypes':
				$type = \craft\commerce\elements\Product::instance();
				$idHandle = 'typeId';
				break;

			default:
				goto out;
		}

		$settings = Seo::$i->getSettings();

		$elements = $type::find();
		$elements->{$idHandle} = $variables['id'];
		$elements->siteId = Craft::$app->sites->currentSite->id;
		$elements->limit = $settings->sitemapLimit;
		$elements->offset = $settings->sitemapLimit * $variables['page'];

		$currentLocale = $craft->locale->id;
		$availableLocales = $craft->i18n->getSiteLocaleIds();

		if (($key = array_search($currentLocale, $availableLocales)) !== false)
			unset($availableLocales[$key]);

		$seoFieldHandle = null;
		if ($first = $elements->one())
		{
			$fieldLayout =
				$variables['section'] === 'categories' || $variables['section'] === 'productTypes'
					? $first->fieldLayout
					: $first->type->fieldLayout;

			foreach ($fieldLayout->getCustomFields() as $field)
				if (get_class($field) === SeoField::class)
					$seoFieldHandle = $field->handle;
		}

		foreach ($elements->all() as $item)
		{
			if ($item->url === null)
				continue;

			if ($seoFieldHandle !== null) {
				/** @var SeoData $seoField */
				$seoField = $item->$seoFieldHandle;
				if (is_object($seoField) && property_exists($seoField, 'advanced') && $robots = $seoField->advanced['robots'])
					if (in_array('noindex', $robots))
						continue;
			}

			$url = $this->_document->createElement('url');
			$this->_urlSet->appendChild($url);

			$loc = $this->_document->createElement(
				'loc',
				$item->url
			);

			$mod = $this->_document->createElement(
				'lastmod',
				DateTimeHelper::toIso8601($item->dateUpdated)
			);

			$freq = $this->_document->createElement(
				'changefreq',
				$sitemapSectionById['frequency']
			);

			$priority = $this->_document->createElement(
				'priority',
				$sitemapSectionById['priority']
			);

			$url->appendChild($loc);
			$url->appendChild($mod);
			$url->appendChild($freq);
			$url->appendChild($priority);

			$enabledLookup =
				(new Query())->select(['siteId', 'uri'])
				             ->from('{{%elements_sites}}')
				             ->where('[[elementId]] = ' . $item->id)
				             ->andWhere('enabled = true')
				             ->all();

			$enabledLookup = array_reduce(
				$enabledLookup,
				function ($a, $b) {
					$uri = $b['uri'];
					$a[$b['siteId']] = $uri === '__home__' ? '' : $uri;
					return $a;
				},
				[]
			);

			if (!$settings->removeAlternateUrls)
			{
				foreach ($item->supportedSites as $siteId)
				{
					$id = is_numeric($siteId) ? $siteId : $siteId['siteId'];
					$site = $id ? $craft->sites->getSiteById($id) : Craft::$app->sites->currentSite;

					if (empty($site))
						continue;

					$lang = $site->language;

					if (!in_array($lang, $availableLocales))
						continue;

					if (!array_key_exists($id, $enabledLookup))
						continue;

					$link = UrlHelper::siteUrl(
						$enabledLookup[$id], null, null, $id
					);

					$alt = $this->_document->createElement('xhtml:link');
					$alt->setAttribute('rel', 'alternate');
					$alt->setAttribute(
						'hreflang',
						str_replace('_', '-', $lang)
					);
					$alt->setAttribute('href', $link);

					$url->appendChild($alt);
				}
			}
		}

		out:
		return $this->_document->saveXML();
	}

	// Custom
	// -------------------------------------------------------------------------

	public function custom ()
	{
		$this->_createDocument();
		$sitemapData = $this->getSitemap();

		if (!array_key_exists('customUrls', $sitemapData))
			return $this->_document->saveXML();

		foreach ($sitemapData['customUrls'] as $custom) if ($custom->enabled)
		{
			$url = $this->_document->createElement('url');
			$loc = $this->_document->createElement(
				'loc',
				UrlHelper::url($custom->url)
			);
			$frequency = $this->_document->createElement(
				'changefreq',
				$custom->frequency
			);
			$priority = $this->_document->createElement(
				'priority',
				$custom->priority
			);

			$url->appendChild($loc);
			$url->appendChild($frequency);
			$url->appendChild($priority);

			$this->_urlSet->appendChild($url);
		}

		return $this->_document->saveXML();
	}

	// Helpers
	// =========================================================================

	/**
	 * @param Section|CategoryGroup|ProductType $thing
	 *
	 * @return bool
	 */
	private function _filterOutNoUrls ($thing)
	{
		foreach ($thing->getSiteSettings() as $siteSettings)
			if ($siteSettings->hasUrls)
				return true;

		return false;
	}

	// Helpers: XML
	// -------------------------------------------------------------------------

	/**
	 * Creates the XML document
	 *
	 * @param bool $withUrlSet - Will append the URLSet if true
	 */
	private function _createDocument ($withUrlSet = true)
	{
		// Create the XML Document
		$document = new DOMDocument('1.0', 'utf-8');

		// Pretty print for debugging
		if (Craft::$app->config->general->devMode)
			$document->formatOutput = true;

		if ($withUrlSet)
		{
			$urlSet = $document->createElement('urlset');
			$urlSet->setAttribute(
				'xmlns',
				'http://www.sitemaps.org/schemas/sitemap/0.9'
			);
			$urlSet->setAttribute(
				'xmlns:xhtml',
				'http://www.w3.org/1999/xhtml'
			);
			$document->appendChild($urlSet);
			$this->_urlSet = $urlSet;
		}

		$this->_document = $document;
	}

	/**
	 * Get's the latest updated element date for the given element type and
	 * group / section ID
	 *
	 * @param Element $type - The Element Type
	 * @param int     $id - The section or group ID
	 *
	 * @return DateTime|string
	 */
	private function _getUpdated (Element $type, $id)
	{
		/** @var EntryQuery|CategoryQuery|ProductQuery $criteria */
		$criteria = $type::find();

		$this->_setCriteriaIdByType($criteria, $type, $id);

		$criteria->limit = 1;

		$element = $criteria->one();
		return $element ? $element->dateUpdated->format('c') : '';
	}

	/**
	 * Get's the page count for the given element type and group / section ID
	 *
	 * @param Element $type - The Element Type
	 * @param int     $id - The section or group ID
	 *
	 * @return float
	 */
	private function _getPageCount (Element $type, $id)
	{
		/** @var EntryQuery|CategoryQuery|ProductQuery $criteria */
		$criteria = $type::find();
		$this->_setCriteriaIdByType($criteria, $type, $id);

		$sitemapLimit = Seo::$i->getSettings()->sitemapLimit;

		return ceil($criteria->count() / $sitemapLimit);
	}

	/**
	 * Sets the section or group ID on the criteria according to the
	 * given element type
	 *
	 * @param EntryQuery|CategoryQuery|ProductQuery $criteria - The criteria
	 * @param Element                  $type - The element type
	 * @param int                      $id - The section or group ID
	 */
	private function _setCriteriaIdByType ($criteria, Element $type, $id)
	{
		switch ($type::class) {
			case 'craft\\elements\\Entry':
				$criteria->sectionId = $id;
				break;
			case 'craft\\elements\\Category':
				$criteria->groupId = $id;
				break;
			case 'craft\\elements\\Product':
				$criteria->typeId = $id;
				break;
		}
	}

	// Helpers: XML Index
	// -------------------------------------------------------------------------

	/**
	 * @param $handle
	 * @param $data
	 * @param $sitemapData
	 *
	 * @throws \yii\base\Exception
	 */
	private function _generateLoop ($handle, $data, $sitemapData)
	{
		if (!array_key_exists($handle, $sitemapData))
			return;

		foreach ($data as $item)
			if (array_key_exists($item['id'], $sitemapData[$handle]))
				if ($sitemapData[$handle][$item['id']]->enabled)
					$this->_generateIndex($handle, $item['id']);
	}

	/**
	 * @param $group
	 * @param $id
	 *
	 * @throws \yii\base\Exception
	 */
	private function _generateIndex ($group, $id)
	{
		switch ($group) {
			case 'custom':
				$last = DateTimeHelper::toIso8601(
					SitemapRecord::find()->one()->dateUpdated
				);
				$pages = 1;
				break;

			case 'sections':
				$last = $this->_getUpdated(Entry::instance(), $id);
				$pages = $this->_getPageCount(Entry::instance(), $id);
				break;

			case 'categories':
				$last = $this->_getUpdated(Category::instance(), $id);
				$pages = $this->_getPageCount(Category::instance(), $id);
				break;

			case 'productTypes':
				$last = $this->_getUpdated(\craft\commerce\elements\Product::instance(), $id);
				$pages = $this->_getPageCount(\craft\commerce\elements\Product::instance(), $id);
				break;				

			default:
				$last = DateTimeHelper::currentUTCDateTime()->format('c');
				$pages = 1;
		}

		for ($i = 0; $i < $pages; ++$i)
		{
			$sitemap = $this->_document->createElement('sitemap');
			$this->_index->appendChild($sitemap);

			$loc = $this->_document->createElement(
				'loc',
				$this->_indexUrl($group, $id, $i)
			);
			$sitemap->appendChild($loc);

			$lastMod = $this->_document->createElement('lastmod', $last);
			$sitemap->appendChild($lastMod);
		}
	}

	/**
	 * @param $group
	 * @param $id
	 * @param $page
	 *
	 * @return string
	 * @throws \yii\base\Exception
	 */
	private function _indexUrl ($group, $id, $page)
	{
		$sitemapName = Seo::$i->getSettings()->sitemapName;

		return UrlHelper::siteUrl(
			$sitemapName . '_' . $group
			. ($id > 0 ? '_' . $id : '')
			. ($id > 0 ? '_' . $page : '')
			. '.xml'
		);
	}

}
