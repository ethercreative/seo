<?php

namespace Seo\Models;

use Seo\db\SchemaDb;

class Schema {

	// Variables
	// =========================================================================

	public $id;
	public $type;
	public $comment;
	public $label;
	public $accepts;

	// Constructor
	// =========================================================================

	public function __construct (array $raw)
	{
		$this->id = $raw['id'];
		$this->type = $raw['type'];
		$this->comment = $raw['comment'];
		$this->label = $raw['label'];
		$this->accepts = explode(', ', $raw['rangeIncludes']);
	}

	// Getters
	// =========================================================================

	/**
	 * Gets the sub-classes of this schema
	 *
	 * @return Schema[]
	 */
	public function getChildren ()
	{
		return SchemaDb::getSubClassOf($this->id);
	}

	/**
	 * Gets the properties of this schema
	 *
	 * @return Schema[]
	 */
	public function getProperties ()
	{
		return SchemaDb::getPropertiesOf($this->id);
	}

}