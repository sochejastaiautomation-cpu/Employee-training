/**
 * KMP Algorithm for pattern matching in search
 * Time Complexity: O(n + m) where n is text length and m is pattern length
 */
function buildKMPTable(pattern) {
    const m = pattern.length;
    const table = new Array(m).fill(0);
    let j = 0;

    for (let i = 1; i < m; i++) {
        while (j > 0 && pattern[i] !== pattern[j]) {
            j = table[j - 1];
        }
        if (pattern[i] === pattern[j]) {
            j++;
        }
        table[i] = j;
    }
    return table;
}

function kmpSearch(text, pattern) {
    const n = text.length;
    const m = pattern.length;
    if (m === 0) return true;

    const table = buildKMPTable(pattern);
    let j = 0;

    for (let i = 0; i < n; i++) {
        while (j > 0 && text[i] !== pattern[j]) {
            j = table[j - 1];
        }
        if (text[i] === pattern[j]) {
            j++;
        }
        if (j === m) {
            return true;
        }
    }
    return false;
}

/**
 * Merge Sort Algorithm for sorting
 * Time Complexity: O(n log n)
 * Space Complexity: O(n)
 */
function mergeSort(arr, compareFn) {
    if (arr.length <= 1) return arr;

    const mid = Math.floor(arr.length / 2);
    const left = mergeSort(arr.slice(0, mid), compareFn);
    const right = mergeSort(arr.slice(mid), compareFn);

    return merge(left, right, compareFn);
}

function merge(left, right, compareFn) {
    const result = [];
    let i = 0, j = 0;

    while (i < left.length && j < right.length) {
        if (compareFn(left[i], right[j]) <= 0) {
            result.push(left[i++]);
        } else {
            result.push(right[j++]);
        }
    }

    return result.concat(left.slice(i)).concat(right.slice(j));
}

/**
 * Utility function to escape HTML characters
 * Prevents XSS attacks
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
