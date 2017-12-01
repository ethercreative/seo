<?php

namespace Craft;

class Seo_RedirectRecord extends BaseRecord
{
	/**
	 * Returns the name of the associated database table.
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'seo_redirects';
	}

	public function primaryKey()
	{
		return 'id';
	}

	public function defineAttributes()
	{
		return [
			'uri' => array(AttributeType::String, 'required' => true),
			'to' => array(AttributeType::String, 'required' => true),
			'type' => array(AttributeType::Enum, 'values' => '301,302', 'required' => true),
		];
	}
}