<?php

namespace ether\seo\services;

use craft\base\Component;
use craft\models\CategoryGroup;
use craft\models\Section;
use ether\seo\records\SitemapRecord;
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
				\Craft::$app->db->createCommand()->delete(
					SitemapRecord::$tableName,
					['in', 'id', $idsToDelete]
				)->execute();
			} catch (Exception $e) {
				\Craft::$app->log->logger->log(
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
			\Craft::$app->sections->getAllSections(),
			[$this, '_filterOutNoUrls']
		);
	}

	public function getValidCategories ()
	{
		return array_filter(
			\Craft::$app->categories->getAllGroups(),
			[$this, '_filterOutNoUrls']
		);
	}

	// Helpers
	// =========================================================================

	/**
	 * @param Section|CategoryGroup $thing
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

}