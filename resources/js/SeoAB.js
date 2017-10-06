/**
 * SEO A/B
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import { c } from "./helpers";

class SeoAB {
	
	// Variables
	// =========================================================================
	
	fieldSwitches = [];
	globalDirection = 0;
	
	// SeoAB
	// =========================================================================
	
	constructor () {
		this.enabledSwitch = document.getElementById('seo_AbEnabled');
		this.enabledSwitch.addEventListener('click', this.onEnableClick);
		
		this.craftFieldsContainer = document.getElementById('fields');
		this.fieldsContainer = document.getElementById('seoBFields');
		
		this.fieldIds = this.getFieldIds();
		
		this.onEnableClick();
	}
	
	// Actions
	// =========================================================================
	
	// Switches
	// -------------------------------------------------------------------------
	
	appendSwitches () {
		let i = this.fieldIds.length;
		while (i--) {
			const field = document.getElementById(`fields-${this.fieldIds[i]}`);
			const swtch = c("button", {
				style: `
					position: absolute;
					top: 0;
					right: 0;
					font-weight: bold;
				`,
				click: this.onToggleField.bind(this, this.fieldIds[i]),
			}, "AB");
			
			this.fieldSwitches.push(swtch);
			field.appendChild(swtch);
		}
		
		document.getElementById('tabs').appendChild(
			c("button", {
				style: `
					position: absolute;
					top: 10px;
					right: 24px;
					font-weight: bold;
				`,
				click: this.onToggleAllClick,
			}, "AB")
		);
	}
	
	removeSwitches () {
		let i = this.fieldSwitches.length;
		while (i--) {
			this.fieldSwitches[i].parentNode.removeChild(
				this.fieldSwitches[i]
			);
		}
		
		this.fieldSwitches = [];
	}
	
	// Fields
	// -------------------------------------------------------------------------
	
	/**
	 * Toggle the fields A/B
	 *
	 * @param {string} fieldId
	 * @param {number} direction - 0 === B, 1 === A
	 */
	toggleField (fieldId, direction = 0) {
		const craftField = document.getElementById(`fields-${fieldId}`)
			, seoAbField = document.getElementById(`seoAb-${fieldId}`);
		
		const currentDirection =
			(craftField.parentNode.getAttribute('id') !== 'seoBFields')|0;
		
		if (direction === currentDirection) return;
		
		const craftFieldTarget = document.createComment('craftFieldTarget')
			, seoAbFieldTarget = document.createComment('seoAbFieldTarget');
		
		// TODO: replace the btn w/ a switch
		const btn = currentDirection
			? craftField.lastElementChild
			: seoAbField.lastElementChild;
		
		// Toggle the "switch"
		btn.textContent = direction ? 'AB' : 'BA';
		
		// Move the switch to the visible field
		direction ? craftField.appendChild(btn) : seoAbField.appendChild(btn);
		
		craftField.parentNode.insertBefore(
			seoAbFieldTarget,
			craftField
		);
		seoAbField.parentNode.insertBefore(
			craftFieldTarget,
			seoAbField
		);
		
		// Change places!
		craftFieldTarget.parentNode.insertBefore(
			craftField,
			craftFieldTarget
		);
		craftFieldTarget.parentNode.removeChild(craftFieldTarget);
		
		seoAbFieldTarget.parentNode.insertBefore(
			seoAbField,
			seoAbFieldTarget
		);
		seoAbFieldTarget.parentNode.removeChild(seoAbFieldTarget);
	}
	
	toggleAllFields (direction = 0) {
		this.fieldIds.forEach(id => {
			this.toggleField(id, direction);
		});
	}
	
	// Events
	// =========================================================================
	
	/**
	 * Fired when the A/B Enable switch is toggled
	 */
	onEnableClick = () => {
		const isEnabled = !!this.enabledSwitch.lastElementChild.value;
		isEnabled ? this.appendSwitches() : this.removeSwitches();
	};
	
	onToggleField = (fieldId, e) => {
		e.preventDefault();
		
		const btn = e.target;
		// TODO: Make this check better
		this.toggleField(fieldId, (btn.textContent === 'BA')|0);
	};
	
	onToggleAllClick = e => {
		e.preventDefault();
		this.globalDirection = (!this.globalDirection)|0;
		e.target.textContent = this.globalDirection ? 'AB' : 'BA';
		this.toggleAllFields(this.globalDirection);
	};
	
	// Helpers
	// =========================================================================
	
	/**
	 * Gets the non-namespaced IDs of A/B-able fields
	 *
	 * @return {Array}
	 */
	getFieldIds () {
		const ids = [];
		
		let i = this.fieldsContainer.children.length;
		while (i--) {
			let id = this.fieldsContainer.children[i].getAttribute('id');
			id = id.replace('seoAb-', '');
			ids.push(id);
		}
		
		return ids;
	}
	
}

window.SeoAB = SeoAB;