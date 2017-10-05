/* globals $ */
/**
 * Layout Designer
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */
export default class LayoutDesigner {
	
	// Variables
	// =========================================================================
	
	fieldLayoutDesigner = null;
	
	// Layout Designer
	// =========================================================================
	
	/**
	 * Create our Layout Designer
	 *
	 * @param {Craft.FieldLayoutDesigner} fieldLayoutDesigner
	 * @constructor
	 */
	constructor (fieldLayoutDesigner) {
		this.fieldLayoutDesigner = fieldLayoutDesigner;
	}
	
	// Actions
	// =========================================================================
	
	/**
	 * Adds our Enable A/B menu item to the fields settings menu
	 *
	 * @param field
	 */
	static addMenuItem (field) {
		const $editBtn = field.find(".settings");
		const menuBtn = $editBtn.data("menubtn");
		const menu = menuBtn.menu;
		const $menu = menu.$container;
		const $ul = $menu.children("ul");
		const abItem =
			$('<li><a data-action="seo-ab">Enable A/B</a></li>')
			.appendTo($ul);
		
		menu.addOptions(abItem.children("a"));
	}
	
	// Events
	// =========================================================================
	
	onEnableOptionSelected (option) {
		const $field = $(option).data("menu").$anchor.parent()[0];
		
		// TODO
	}
	
}