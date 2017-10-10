<?php

namespace Craft;

class m171006_125600_seo_AddLocaleColumnToABEnabledTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$this->addColumn(
			'seo_ab_enabled',
			'locale',
			ColumnType::Locale
		);

		return true;
	}

}