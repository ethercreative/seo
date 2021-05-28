/* globals Craft */

/**
 * SEO Settings
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import Redirects from "./settings/Redirects";
import Settings from "./settings/Settings";

class SeoSettings {
	
	// Variables
	// =========================================================================
	
	namespace = null;
	csrf = null;
	
	// Variables: Static
	// -------------------------------------------------------------------------
	
	static rowCb = null;
	static table = null;
	static row = null;
	
	// SeoSettings
	// =========================================================================
	
	constructor (
		namespace,
		run,
		csrf = [Craft.csrfTokenName, Craft.csrfTokenValue]
	) {
		this.namespace = namespace;
		this.csrf = {
			name: csrf[0],
			token: csrf[1],
		};
		
		switch (run) {
			case "sitemap":
				SeoSettings.EditableTable(
					`${this.namespace}-customUrls`,
					`${this.namespace}-addCustomUrl`
				);
				break;
			case "redirects":
				new Redirects(this.namespace, this.csrf);
				break;
			case "settings":
				new Settings(this.namespace, this.csrf);
				break;
		}
	}
	
	// Helpers
	// =========================================================================
	
	static EditableTable (tableId, addButtonId, rowCb) {
		const noop = () => {};
		SeoSettings.rowCb = typeof rowCb === typeof noop ? rowCb : noop;
		SeoSettings.table =
			document.getElementById(tableId).getElementsByTagName("tbody")[0];
		SeoSettings.row = SeoSettings.table.firstElementChild.cloneNode(true);
		SeoSettings.table.firstElementChild.remove();
		
		SeoSettings.row.classList.remove("hidden");
		
		const deleteButtons = SeoSettings.table.getElementsByClassName("delete");
		let i = deleteButtons.length;
		while (i--) {
			const btn = deleteButtons[i];
			btn.addEventListener("click", () => {
				btn.parentNode.parentNode.remove();
				SeoSettings.rowCb();
			});
		}
		
		document.getElementById(addButtonId).addEventListener("click", () => {
			SeoSettings.addRowToEditableTable();
		});
	}
	
	static addRowToEditableTable () {
		const newRow = SeoSettings.row.cloneNode(true);
		
		newRow.innerHTML =
			newRow.innerHTML.replace(
				/{i}/g,
				SeoSettings.table.childNodes.length - 2
			);
		
		newRow.getElementsByClassName("delete")[0].addEventListener("click", () => {
			newRow.remove();
			SeoSettings.rowCb();
		});
		
		SeoSettings.table.appendChild(newRow);
		
		SeoSettings.rowCb();
	}
	
}

window.SeoSettings = SeoSettings;