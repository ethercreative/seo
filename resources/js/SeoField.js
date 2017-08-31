/**
 * SEO for Craft CMS
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     1.5.0
 */

import Tabs from "./seo/Tabs";
import FocusKeywords from "./seo/FocusKeywords";

class SeoField {
	
	constructor (namespace/*, hasSectionString*/) {
		this.namespace = namespace;
		
		new Tabs(namespace);
		new FocusKeywords(namespace);
	}
	
}

window.SeoField = SeoField;