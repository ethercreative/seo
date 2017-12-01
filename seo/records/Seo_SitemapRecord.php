<?php

namespace Craft;

class Seo_SitemapRecord extends BaseRecord
{
	/**
	 * Returns the name of the associated database table.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'seo_sitemaps';
	}

	public function primaryKey()
	{
		return 'id';
	}

	public function defineAttributes()
	{
		return [
			'group' => array(AttributeType::Enum, 'values' => 'sections,categories,customUrls,productTypes', 'required' => true),
			'url' => array(AttributeType::String, 'required' => true),
			'frequency' => array(AttributeType::Enum, 'values' => 'always,hourly,daily,weekly,monthly,yearly,never', 'required' => true),
			'priority' => array(AttributeType::Number, 'required' => true, 'decimals' => 1),
			'enabled' => array(AttributeType::Bool, 'default' => true),
		];
	}
}