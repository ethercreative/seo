/**
 * SEO Settings
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2018
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     3.0.0
 */

import codemirror from "codemirror";
import "codemirror/addon/edit/closebrackets";
import "codemirror/mode/twig/twig";

export default class Settings {
	
	constructor (namespace, csrf) {
		this.namespace = namespace;
		this.csrf = csrf;

		this.initSitemap();
		this.initRobots();
	}
	
	// Sitemap
	// =========================================================================
	
	initSitemap () {
		const eg = document.getElementById(`${this.namespace}-sitemapNameExample`);
		document.getElementById(
			`${this.namespace}-sitemapName`
		).addEventListener("input", ({ target }) => {
			eg.textContent = `${target.value}.xml`;
		});
	}
	
	// Robots.txt
	// =========================================================================
	
	initRobots () {
		const ta = document.getElementById(`${this.namespace}-robotsTxt`);
		const cm = codemirror.fromTextArea(ta, {
			lineNumbers: true,
			mode: "twig",
			theme: "monokai",
			indentWithTabs: true,
			autoCloseBrackets: true,
			htmlMode: false,
		});
		
		const robotTab = document.querySelector("a[href='#settings-robots']");
		robotTab && robotTab.addEventListener("click", () => {
			cm.refresh();
		});
	}
	
}