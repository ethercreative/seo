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
import Snippet from "./seo/Snippet";

class SeoField {
	
	// Variables
	// =========================================================================
	
	static isInitialized = false;
	
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
		if (SeoField.watchForSeoField(namespace)) return;
		
		this.options = options;
		
		new Tabs(namespace);
		new Snippet(namespace, this);
		// TODO: Social
		
		if (!this.options.hasPreview) return;
		
		new FocusKeywords(namespace, this);
		// TODO: Keyword report
	}
	
	// Helpers
	// =========================================================================
	
	/**
	 * Ensure we've only got one active SEO field
	 * (particularly useful for SEO fields in quick edit HUDs)
	 *
	 * @param {string} namespace
	 * @returns {boolean}
	 */
	static watchForSeoField (namespace) {
		if (SeoField.isInitialized) return true;
		
		SeoField.isInitialized = true;
		const field = document.getElementById(`${namespace}Field`);
		
		const observer = new MutationObserver(mutations => {
			mutations.forEach(mutation => {
				if (mutation.removedNodes.length) {
					SeoField.isInitialized = false;
					observer.disconnect();
				}
			});
		});
		
		observer.observe(field.parentNode, { childList: true });
		
		return false;
	}
	
}

window.SeoField = SeoField;