/* globals Craft */
/**
 * SEO A/B Testing
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import LayoutDesigner from "./seoAB/LayoutDesigner";

class SeoAB {
	
	// Variables
	// =========================================================================
	
	allEnabledFieldIds = [];
	
	// SeoAb
	// =========================================================================
	
	constructor (allEnabledFieldIds) {
		this.allEnabledFieldIds = allEnabledFieldIds;
		
		Craft.FieldLayoutDesigner && this.hookFieldLayoutDesigner();
		// Craft.initUiElements && console.log(Craft.initUiElements);
		// Craft.ElementEditor && console.log(Craft.ElementEditor);
	}
	
	// Hooks
	// =========================================================================
	
	/**
	 * Hook into Crafts Field Layout Designer
	 */
	hookFieldLayoutDesigner () {
		const SEO = this;
		
		const fieldLayoutDesigner = Craft.FieldLayoutDesigner;
		
		const init = fieldLayoutDesigner.prototype.init
			, initField = fieldLayoutDesigner.prototype.initField
			, onFieldOptionSelect = fieldLayoutDesigner.prototype.onFieldOptionSelect;
		
		fieldLayoutDesigner.prototype.init = function () {
			init.apply(this, arguments);
			
			// Initialize our Layout Designer
			/*this.seoAB = */new LayoutDesigner(SEO, this);
		};
		
		fieldLayoutDesigner.prototype.initField = function (field) {
			initField.apply(this, arguments);
			
			// Add our "Enable A/B" menu item
			LayoutDesigner.addMenuItem(SEO, field);
		};
		
		fieldLayoutDesigner.prototype.onFieldOptionSelect = function (opt) {
			onFieldOptionSelect.apply(this, arguments);
			
			if (opt.dataset.action !== "seo-ab") return;
			
			// Fire our on enable select event
			LayoutDesigner.onEnableOptionSelected(opt);
		};
	}
	
}

window.SeoAB = SeoAB;