/**
 * Counts the number of times a string appears in an array
 *
 * @param {Array} arr
 * @param {string} word
 * @return {number}
 */
export default function countInArray (arr, word) {
	let c = 0,
		i = arr.length;
	
	while (i--) if (arr[i].toLowerCase() === word) c++;
	
	return c;
}