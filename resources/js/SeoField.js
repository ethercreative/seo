/**
 * SEO for Craft CMS
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import Tabs from "./seo/Tabs";
import FocusKeywords from "./seo/FocusKeywords";
import Snippet from "./seo/Snippet";

class SeoField {
	
	// Variables
	// =========================================================================
	
	// Set in Snippet
	snippetFields = {
		title: null,
		slug:  null,
		desc:  null,
	};
	
	// Overwritten, but useful for auto-complete
	options = {
		hasPreview: false,
		isNew: false,
	};
	
	// SeoField
	// =========================================================================
	
	/**
	 * Initialize the SEO field
	 *
	 * @param {string} namespace - Field namespace
	 * @param {object} options - The options for the SEO field
	 * @constructor
	 */
	constructor (namespace, options) {
		this.options = options;
		
		new Tabs(namespace);
		new Snippet(namespace, this);
		// TODO: Social
		
		if (!this.options.hasPreview) return;
		// TODO: Disable all preview related functionality
		
		new FocusKeywords(namespace, this);
	}
	
}

window.SeoField = SeoField;
