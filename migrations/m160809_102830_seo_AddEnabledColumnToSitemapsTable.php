<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160809_102830_seo_AddEnabledColumnToSitemapsTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		craft()->db->createCommand()->addColumn('seo_sitemaps', 'enabled', AttributeType::Bool);

		$this->_transferData();

		return true;
	}

	private function _transferData()
	{

		// Transfer Redirects
		$redirects = craft()->seo->getData('redirects') ? craft()->seo->getData('redirects')['redirects'] : array();
		if (is_string($redirects)) $redirects = json_decode($redirects, true);

		foreach ($redirects as $redirect)
		{
			$record = new Seo_RedirectRecord();
			$record->setAttribute('uri', $redirect['uri']);
			$record->setAttribute('to', $redirect['to']);
			$record->setAttribute('type', $redirect['type']);
			$record->save();
		}

		// Transfer Sitemap
		$sitemap = craft()->seo->getData('sitemap');

		// Sections
		if (array_key_exists('sections', $sitemap) && !empty($sitemap['sections'])) {
			foreach ($sitemap['sections'] as $sectionId => $section)
			{
				$record = new Seo_SitemapRecord();
				$record->setAttribute('group', 'sections');
				$record->setAttribute('url', $sectionId);
				$record->setAttribute('frequency', $section['frequency']);
				$record->setAttribute('priority', $section['priority']);
				$record->setAttribute('enabled', $section['enabled']);
				$record->save();
			}
		}

		// Categories
		if (array_key_exists('categories', $sitemap) && !empty($sitemap['categories'])) {
			foreach ($sitemap['categories'] as $sectionId => $section)
			{
				$record = new Seo_SitemapRecord();
				$record->setAttribute('group', 'categories');
				$record->setAttribute('url', $sectionId);
				$record->setAttribute('frequency', $section['frequency']);
				$record->setAttribute('priority', $section['priority']);
				$record->setAttribute('enabled', $section['enabled']);
				$record->save();
			}
		}

		// Custom URLS
		if (array_key_exists('customUrls', $sitemap) && !empty($sitemap['customUrls'])) {
			foreach ($sitemap['customUrls'] as $sectionId => $section)
			{
				$record = new Seo_SitemapRecord();
				$record->setAttribute('group', 'customUrls');
				$record->setAttribute('url', $section['url']);
				$record->setAttribute('frequency', $section['frequency']);
				$record->setAttribute('priority', $section['priority']);
				$record->setAttribute('enabled', $section['enabled']);
				$record->save();
			}
		}

	}
}
