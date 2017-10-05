<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m171005_123900_seo_AddLocaleColumnToABDataTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$this->addColumn(
			"seo_ab_data",
			"locale",
			ColumnType::Locale
		);

		return true;
	}

}
