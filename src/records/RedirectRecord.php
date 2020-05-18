<?php

namespace ether\seo\records;

use craft\db\ActiveRecord;

/**
 * Class RedirectRecord
 *
 * @property int $id
 * @property int $order
 * @property string $uri
 * @property string $to
 * @property string $type
 * @property int|null $siteId
 *
 * @package ether\seo\records
 */
class RedirectRecord extends ActiveRecord
{

	// Props
	// =========================================================================

	// Props: Public Static
	// -------------------------------------------------------------------------

	/** @var string */
	public static $tableName = '{{%seo_redirects}}';

	// Public Methods
	// =========================================================================

	// Public Methods: Static
	// -------------------------------------------------------------------------

	/**
	 * @return string
	 */
	public static function tableName (): string
	{
		return self::$tableName;
	}

}