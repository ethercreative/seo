/**
 * Counts the number of times a string appears in an array
 *
 * @param {Array} arr
 * @param {string|Array} word - Can be a string with a single word, or an array
 *     of words that would be space separated.
 * @return {number}
 */
export default function countInArray (arr, word) {
	let c = 0,
		i = arr.length;
	
	if (Array.isArray(word)) {
		const l = arr.length
			, w = word.length;
		
		while (i--) {
			let x = w,
				a = 0;
			
			while (x-- && i + x < l)
				if (arr[i + x].toLowerCase() === word[x]) a++;
			
			if (a === w) c++;
		}
	} else {
		while (i--) if (arr[i].toLowerCase() === word) c++;
	}
	
	return c;
}