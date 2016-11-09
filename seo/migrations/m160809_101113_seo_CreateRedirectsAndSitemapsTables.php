<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160809_101113_seo_CreateRedirectsAndSitemapsTables extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// Create the craft_seo_redirects table
		craft()->db->createCommand()->createTable('seo_redirects', array(
			'uri'  => array('required' => true),
			'to'   => array('required' => true),
			'type' => array('values' => '301,302', 'column' => 'enum', 'required' => true),
		), null, true);

		// Create the craft_seo_sitemaps table
		craft()->db->createCommand()->createTable('seo_sitemaps', array(
			'group'     => array('values' => 'sections,categories,customUrls', 'column' => 'enum', 'required' => true),
			'url'       => array('required' => true),
			'frequency' => array('values' => 'always,hourly,daily,weekly,monthly,yearly,never', 'column' => 'enum', 'required' => true),
			'priority'  => array('maxLength' => 10, 'decimals' => 1, 'required' => true, 'unsigned' => false, 'length' => 11, 'column' => 'decimal'),
		), null, true);

		return true;
	}
}
