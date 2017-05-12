<?php

namespace Craft;

class Seo_SitemapService extends BaseApplicationComponent
{

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

}