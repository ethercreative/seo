<?php

namespace Seo\db;

use Seo\Models\Schema;

class SchemaDb {

	// Variables
	// =========================================================================

	private static $_db;

	// Public
	// =========================================================================

	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Gets the first result from the given query.
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function first ($query)
	{
		return self::db()->query($query)->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * Gets all available results from the given query.
	 *
	 * @param $query
	 *
	 * @return array
	 */
	public static function all ($query)
	{
		$results = self::db()->query($query);
		$data = [];

		while ($row = $results->fetchArray(SQLITE3_ASSOC))
			$data[] = $row;

		return $data;
	}

	// Getters
	// -------------------------------------------------------------------------

	/**
	 * Gets The THING!
	 *
	 * @return Schema
	 */
	public static function getThing ()
	{
		return new Schema(
			self::first(
				'SELECT * FROM ld WHERE id LIKE \'http://schema.org/Thing\''
			)
		);
	}

	public static function getSubClassOf ($parent)
	{
		return array_map(function ($row) {
			return new Schema($row);
		}, self::all(
			'SELECT * FROM ld WHERE ld.subClassOf LIKE \'%' . $parent . '%\''
		));
	}

	public static function getPropertiesOf ($parent)
	{
		return array_map(function ($row) {
			return new Schema($row);
		}, self::all(
			'SELECT * FROM ld WHERE ld.domainIncludes LIKE \'%' . $parent . '%\''
		));
	}

	// Private
	// =========================================================================

	private static function db ()
	{
		if (self::$_db == null)
			self::$_db = new \SQLite3(
				CRAFT_PLUGINS_PATH . 'seo/resources/schema.sqlite'
			);

		return self::$_db;
	}

}