<?php

namespace ether\seo\models;

use craft\base\Model;

class Sitemap extends Model
{

	// Props
	// =========================================================================

	/** @var string */
	public $group;

	/** @var string */
	public $url;

	/** @var string */
	public $frequency;

	/** @var float */
	public $priority;

	/** @var bool */
	public $enabled = false;

	// Methods
	// =========================================================================

	public function rules (): array
	{
		$rules = parent::rules();

		$rules[] = [
			['group', 'url', 'frequency', 'priority'],
			'required',
		];

		$rules[] = [
			['group', 'url', 'frequency'],
			'string',
		];

		$rules[] = [
			['priority'],
			'double',
			'min' => 0,
			'max' => 1,
		];

		$rules[] = [
			['enabled'],
			'boolean',
		];

		$rules[] = [
			['group'],
			'in',
			'range' => [
				'sections',
				'categories',
				'customUrls',
			],
		];

		return $rules;
	}

}