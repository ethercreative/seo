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
				new SeoSettings.EditableTable(
					`${this.namespace}-customUrls`,
					`${this.namespace}-addCustomUrl`
				);
				break;
			case "redirects":
				new Redirects(this.namespace, this.csrf);
				break;
			case "settings":
				this.sitemapName();
				break;
		}
	}
	
	// Sitemap
	// =========================================================================
	
	sitemapName () {
		const eg = document.getElementById(`${this.namespace}-sitemapNameExample`);
		document.getElementById(
			`${this.namespace}-sitemapName`
		).addEventListener("input", ({ target }) => {
			eg.textContent = `${target.value}.xml`;
		});
	}
	
	// Helpers
	// =========================================================================
	
	static EditableTable (tableId, addButtonId, rowCb) {
		const noop = () => {};
		this.rowCb = typeof rowCb === typeof noop ? rowCb : noop;
		this.table =
			document.getElementById(tableId).getElementsByTagName("tbody")[0];
		this.row = this.table.firstElementChild.cloneNode(true);
		this.table.firstElementChild.remove();
		
		this.row.classList.remove("hidden");
		
		const deleteButtons = this.table.getElementsByClassName("delete");
		let i = deleteButtons.length;
		while (i--) {
			const btn = deleteButtons[i];
			btn.addEventListener("click", () => {
				btn.parentNode.parentNode.remove();
				this.rowCb();
			});
		}
		
		document.getElementById(addButtonId).addEventListener("click", () => {
			this.addRowToEditableTable();
		});
	}
	
	static addRowToEditableTable () {
		const newRow = this.row.cloneNode(true);
		
		newRow.innerHTML =
			newRow.innerHTML.replace(/{i}/g, this.table.childNodes.length - 2);
		
		newRow.getElementsByClassName("delete")[0].addEventListener("click", () => {
			newRow.remove();
			this.rowCb();
		});
		
		this.table.appendChild(newRow);
		
		this.rowCb();
	}
	
}

window.SeoSettings = SeoSettings;