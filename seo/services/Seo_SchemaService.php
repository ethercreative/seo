<?php

namespace Craft;

use Seo\db\SchemaDb;

class Seo_SchemaService extends BaseApplicationComponent {

	public function getThing () {
		return SchemaDb::getThing();
	}

}