<?php
/**
 * SEO
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\seo\jobs;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\App;
use craft\queue\BaseJob;
use ether\seo\models\data\SeoData;

/**
 * Class UpgradeSeoDataJob
 *
 * @author  Ether Creative
 * @package ether\seo\jobs
 */
class UpgradeSeoDataJob extends BaseJob
{

	// Properties
	// =========================================================================

	/**
	 * @var string|ElementInterface|null The element type that should be resaved
	 */
	public $elementType;

	/**
	 * @var array|null The element criteria that determines which elements should be resaved
	 */
	public $criteria;

	/**
	 * @var string The SEO field handle
	 */
	public $handle;

	/** @var string The rendered suffix of the field */
	public $suffix;

	// Public Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public function execute ($queue): void
	{
		$class = $this->elementType;

		\Craft::$app->getTemplateCaches()->deleteCachesByElementType($class);

		/** @var ElementQuery $query */
		$query = $class::find();
		if (!empty($this->criteria))
			\Craft::configure($query, $this->criteria);

		$query
			->offset(null)
			->limit(null)
			->orderBy(null);

		$totalElements  = $query->count();
		$currentElement = 0;

		try
		{
			foreach ($query->each() as $element)
			{
				$this->setProgress($queue, $currentElement++ / $totalElements);

				/** @var Element $element */
				$element->setScenario(Element::SCENARIO_ESSENTIALS);

				// Remove suffix from first editable
				$keys = array_keys($element->{$this->handle}->titleRaw);
				if (!empty($keys))
				{
					$element->{$this->handle}->titleRaw[$keys[0]] = str_replace(
						$this->suffix,
						'',
						$element->{$this->handle}->titleRaw[$keys[0]]
					);
				}

				if (!\Craft::$app->getElements()->saveElement($element))
				{
					throw new \Exception(
						'Couldn’t save element ' . $element->id .
						' (' . get_class($element) . ') due to validation errors.'
					);
				}
			}
		} catch (QueryAbortedException $e) {}
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	protected function defaultDescription (): ?string
	{
		return \Craft::t(
			'seo',
			'Upgrading {class} elements SEO data',
			[
				'class' => App::humanizeClass($this->elementType)
			]
		);
	}

}