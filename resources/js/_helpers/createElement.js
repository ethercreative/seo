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
export default function createElement (
	tag = 'div',
	attributes = {},
	children = []
) {
	const elem = document.createElement(tag);
	
	for (let [key, value] of Object.entries(attributes)) {
		if (!value) continue;
		
		if (typeof value === typeof (() => {})) {
			if (key === 'ref') value(elem);
			else elem.addEventListener(key, value);
			continue;
		}
		
		if (key === 'style')
			value = value.replace(/(?:\r\n|\r|\n|\t|\s+)/g, ' ').trim();
		
		elem.setAttribute(key, value);
	}
	
	if (!Array.isArray(children))
		children = [children];
	
	children.map(child => {
		if (!child) return;
		
		try {
			elem.appendChild(child);
		} catch (_) {
			elem.appendChild(document.createTextNode(child));
		}
	});
	
	return elem;
}