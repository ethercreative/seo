<?php

namespace ether\seo\models;

use craft\base\Model;

class Settings extends Model
{

	// Variables
	// =========================================================================

	// Variables: Sitemap
	// -------------------------------------------------------------------------

	/** @var string */
	public $sitemapName = 'sitemap';

	/** @var int */
	public $sitemapLimit = 1000;

	// Variables: Field Type
	// -------------------------------------------------------------------------

	/** @var string */
	public $titleSuffix;

	/** @var array */
	public $socialImage;

	/** @var string */
	public $metaTemplate;

	// Methods
	// =========================================================================

	/**
	 * @return array
	 */
	public function rules (): array
	{
		return [
			[
				['sitemapName', 'sitemapLimit'],
				'required'
			],
			[
				['sitemapName', 'titleSuffix', 'metaTemplate'],
				'string'
			],
			[
				['sitemapLimit'],
				'number'
			],
			[
				['socialImage'],
				'each', 'rule' => ['integer']
			],
		];
	}

}