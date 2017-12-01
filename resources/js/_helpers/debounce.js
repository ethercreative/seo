/**
 * ## Debounce
 *
 * A function, that, as long as it continues to be invoked, will not
 * be triggered. The function will be called after it stops being called for
 * N milliseconds.
 *
 * If `immediate` is passed, trigger the function on the leading edge,
 * instead of the trailing.
 *
 * ```jsx
 *
 * // ...
 *
 * <input onInput={this.handleInput}>
 *
 * // ...
 *
 * handleInput = debounce(e => { /* ... *\/ });
 *
 * ```
 *
 * @param {function} func - The function to debounce
 * @param {number=} wait - How long, in milliseconds, to delay between attempts
 * @param {boolean=} immediate - Fire on the leading edge
 * @returns {Function}
 */
export default function debounce (func, wait = 300, immediate = false) {
	let timeout;
	
	if (wait === 0) {
		return function () {
			func.apply(this, arguments);
		};
	}
	
	return function () {
		const context = this
			, args = arguments;
		
		if (args[0].constructor.name === 'SyntheticEvent')
			args[0].persist();
		
		const later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		const callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
}