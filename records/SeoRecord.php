<?php

namespace Craft;

class SeoRecord extends BaseRecord
{
	/**
	 * Returns the name of the associated database table.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'seo';
	}

	public function primaryKey()
	{
		return 'name';
	}

	public function defineAttributes()
	{
		return [
			'name' => array(AttributeType::String, 'required' => true, 'unique' => true),
			'data' => array(AttributeType::Mixed),
		];
	}
}