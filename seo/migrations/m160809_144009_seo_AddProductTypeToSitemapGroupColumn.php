<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160809_144009_seo_AddProductTypeToSitemapGroupColumn extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		craft()->db->createCommand()->alterColumn(
			'seo_sitemaps',
			'group',
			array(
				'values' => 'sections,categories,customUrls,productTypes',
				'column' => 'enum',
				'required' => true
			)
		);

		return true;
	}
}
