<?php

namespace ether\seo\models;

use craft\base\Model;

class Redirect extends Model
{

	// Props
	// =========================================================================

	// Props: Public Instance
	// -------------------------------------------------------------------------

	/** @var int */
	public $order;

	/** @var string */
	public $uri;

	/** @var string */
	public $to;

	/** @var string */
	public $type;

	// Public Methods
	// =========================================================================

	// Public Methods: Instance
	// -------------------------------------------------------------------------

	/**
	 * @inheritdoc
	 *
	 * @return array
	 */
	public function rules (): array
	{
		$rules = parent::rules();

		$rules[] = [
			['order'],
			'integer',
			'required',
		];

		$rules[] = [
			['url', 'to', 'type'],
			'string',
			'required',
		];

		$rules[] = [
			['type'],
			'in',
			'range' => ['301','302'],
		];

		return $rules;
	}

}