/**
 * SEO Settings
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2018
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     3.5.0
 */

export default class FieldType {

	constructor (namespace) {
		this.namespace = namespace;

		this.initSeoTitle();
	}

	// SEO Title
	// =========================================================================

	tokenList = null;
	tokenTemplate = null;

	initSeoTitle () {
		this.tokenList = document.getElementById("settings-seoMetaTitle");
		this.tokenTemplate =
			document.getElementById("settings-seoMetaToken").content;

		const existingTokens =
			this.tokenList.querySelectorAll("li:not([data-static])");
		let i = existingTokens.length;
		if (i > 0) while (i--)
			this.setupToken(existingTokens[i]);

		window.addEventListener("focus", this.onFocusChange, true);
		window.addEventListener("blur", this.onFocusChange, true);

		document.getElementById("settings-seoMetaAdd").addEventListener(
			"click",
			this.onAddClick
		);
	}

	setupToken (token) {
		token.querySelector("[data-template]").addEventListener(
			"input",
			FieldType.onInputInput
		);

		token.querySelector("[data-lock]").addEventListener(
			"click",
			FieldType.onLockClick
		);

		token.querySelector("[data-delete]").addEventListener(
			"click",
			this.onDeleteClick
		);
	}

	// SEO Title: Events
	// -------------------------------------------------------------------------

	onFocusChange = () => {
		if (this.tokenList.contains(document.activeElement))
			this.tokenList.classList.add("focus");
		else
			this.tokenList.classList.remove("focus");
	};

	onAddClick = e => {
		e.preventDefault();
		const li = document.importNode(this.tokenTemplate, true);
		const indexReplace = li.querySelectorAll("[name*='__LOOP_INDEX__']")
			, index = this.tokenList.children.length - 1;

		let i = indexReplace.length,
			t = null;
		while (i--) {
			const el = indexReplace[i];
			const name = el.getAttribute("name");
			el.setAttribute(
				"name",
				name.replace("__LOOP_INDEX__", index)
			);

			if (~name.indexOf("key"))
				el.value = Math.random().toString(36).substr(2, 3);

			if (~name.indexOf("template"))
				t = el;
		}

		this.setupToken(li);
		this.tokenList.insertBefore(li, this.tokenList.lastElementChild);
		t.focus();
	};

	static onInputInput (e) {
		const value = e.target.value;
		e.target.style.width = `calc(${value.length}ch + 10px)`;
	}

	static onLockClick (e) {
		e.preventDefault();
		const target = e.target;
		const input = target.previousElementSibling;

		if (input.value === "1") {
			input.value = "0";
			target.setAttribute("title", "Lock Token");
			target.parentNode.classList.remove("locked");
		} else {
			input.value = "1";
			target.setAttribute("title", "Unlock Token");
			target.parentNode.classList.add("locked");
		}
	}

	onDeleteClick = e => {
		e.preventDefault();
		this.tokenList.removeChild(e.target.parentNode);
		this.reIndexTokens();
	};

	// Helpers
	// =========================================================================

	reIndexTokens () {
		const tokens = this.tokenList.children;
		let i = tokens.length - 1;
		if (i > 0) while (i--) {
			const token = tokens[i]
				, rpl = FieldType.replaceKey.bind(this, i);
			const inputs = token.getElementsByTagName("input");
			let x = inputs.length;
			while (x--) {
				inputs[x].setAttribute(
					"name",
					inputs[x].getAttribute("name").replace(
						/settings\[title]\[(\d)].*/g,
						rpl
					)
				);
			}
		}
	}

	static replaceKey (i, a, b) {
		return a.replace(b, i);
	}

}