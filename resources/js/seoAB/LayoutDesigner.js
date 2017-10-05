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

import { t } from "../helpers";

export default class LayoutDesigner {
	
	// Variables
	// =========================================================================
	
	SEO = null;
	fieldLayoutDesigner = null;
	
	// Layout Designer
	// =========================================================================
	
	/**
	 * Create our Layout Designer
	 *
	 * @param {SeoAB} SEO
	 * @param {Craft.FieldLayoutDesigner} fieldLayoutDesigner
	 * @constructor
	 */
	constructor (SEO, fieldLayoutDesigner) {
		this.SEO = SEO;
		this.fieldLayoutDesigner = fieldLayoutDesigner;
	}
	
	// Actions
	// =========================================================================
	
	/**
	 * Adds our Enable A/B menu item to the fields settings menu
	 *
	 * @param {SeoAB} SEO
	 * @param $field
	 */
	static addMenuItem (SEO, $field) {
		const $editBtn = $field.find(".settings");
		const menuBtn = $editBtn.data("menubtn");
		const menu = menuBtn.menu;
		const $menu = menu.$container;
		const $ul = $menu.children("ul");
		const abItem =
			$('<li><a data-action="seo-ab">Enable A/B</a></li>')
			.appendTo($ul);
		
		const option = abItem.children("a")
			, fieldId = $field[0].dataset.id;
		
		if (SEO.allEnabledFieldIds.indexOf(fieldId) > -1)
			LayoutDesigner.onEnableOptionSelected(option);
		
		menu.addOptions(option);
	}
	
	// Events
	// =========================================================================
	
	static onEnableOptionSelected (option) {
		const field = $(option).data("menu").$anchor.parent()[0];
		
		if (field.classList.contains("seo-ab-enabled")) {
			field.classList.remove("seo-ab-enabled");
			field.removeChild(field.querySelector("input[name='seoAB[]']"));
			
			setTimeout(() => {
				option.textContent = "Enable A/B";
			});
			
			return;
		}
		
		field.classList.add("seo-ab-enabled");
		field.appendChild(t("input", {
			type: "hidden",
			name: "seoAB[]",
			value: field.dataset.id,
		}));
		
		setTimeout(() => {
			option.textContent = "Disable A/B";
		});
	}
	
}