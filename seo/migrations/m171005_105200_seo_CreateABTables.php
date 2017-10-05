<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m171005_105200_seo_CreateABTables extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// Create the craft_seo_ab_fields table
		// A row in this table, means fieldId in layoutId is enabled for A/B
		$this->createTable("seo_ab_fields", [
			"layoutId"  => ["required" => true, "column" => ColumnType::Int],
			"fieldId"   => ["required" => true, "column" => ColumnType::Int],
		], null, false, false);

		// Add indexes to ab_fields
		$this->createIndex("seo_ab_fields", "layoutId");
		$this->createIndex("seo_ab_fields", "fieldId");

		// Create the craft_seo_ab_data table
		$this->createTable("seo_ab_data", [
			"elementId" => ["required" => true, "column" => ColumnType::Int],
			"fieldId"   => ["required" => true, "column" => ColumnType::Int],
			"data"      => ["required" => true, "column" => ColumnType::LongText],
		], null, true, false);

		// Add indexes to ab_data
		$this->createIndex("seo_ab_data", "elementId");
		$this->createIndex("seo_ab_data", "fieldId");

		return true;
	}

}
