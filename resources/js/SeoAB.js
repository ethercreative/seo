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
	
	constructor () {
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
		const fieldLayoutDesigner = Craft.FieldLayoutDesigner;
		
		const init = fieldLayoutDesigner.prototype.init
			, initField = fieldLayoutDesigner.prototype.initField
			, onFieldOptionSelect = fieldLayoutDesigner.prototype.onFieldOptionSelect;
		
		fieldLayoutDesigner.prototype.init = function () {
			init.apply(this, arguments);
			
			// Initialize our Layout Designer
			this.seoAB = new LayoutDesigner(this);
		};
		
		fieldLayoutDesigner.prototype.initField = function (field) {
			initField.apply(this, arguments);
			
			// Add our "Enable A/B" menu item
			LayoutDesigner.addMenuItem(field);
		};
		
		fieldLayoutDesigner.prototype.onFieldOptionSelect = function (opt) {
			onFieldOptionSelect.apply(this, arguments);
			
			if (opt.dataset.action !== "seo-ab") return;
			
			// Fire our on enable select event
			this.seoAB.onEnableOptionSelected(opt);
		};
	}
	
}

window.SeoAB = SeoAB;