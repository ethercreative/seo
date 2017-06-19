/**
 * ## Create Element
 * Quick and easy DOM element creation
 *
 * @param {string=} tag - The element tag
 * @param {object=} attributes - The attributes to add, mapping the key as
 *     the attribute name, and the value as its value. If the value is a
 *     function, it will be added as an event.
 * @param {(Array|*)=} children - An array of children (can be a mixture of
 *     Nodes to append, or other values to be stringified and appended
 *     as text).
 * @return {Element} - The created element
 */
export function createElement (
	tag = "div",
	attributes = {},
	children = []
) {
	const elem = document.createElement(tag);
	
	for (let [key, value] of Object.entries(attributes)) {
		if (typeof value === typeof (() => {})) {
			elem.addEventListener(key, value);
			continue;
		}
		
		elem.setAttribute(key, value);
	}
	
	if (!Array.isArray(children))
		children = [children];
	
	children.map(child => {
		try {
			elem.appendChild(child);
		} catch (_) {
			elem.appendChild(document.createTextNode(child));
		}
	});
	
	return elem;
}