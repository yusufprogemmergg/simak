/**
 * Format angka ke format Rupiah Indonesia
 * @param {number} num
 * @returns {string}
 */
export const formatRupiah = (num) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(num || 0);
};

/**
 * Format angka ke string dengan titik ribuan (untuk input number)
 * @param {number|string} value
 * @returns {string}
 */
export const formatNumberInput = (value) => {
    if (!value) return '';
    const number = value.toString().replace(/[^0-9]/g, '');
    if (number === '') return '';
    return new Intl.NumberFormat('id-ID').format(parseInt(number));
};

/**
 * Format tanggal ke format Indonesia
 * @param {string} dateStr
 * @param {Intl.DateTimeFormatOptions} options
 * @returns {string}
 */
export const formatDate = (dateStr, options = { day: 'numeric', month: 'long', year: 'numeric' }) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', options);
};

/**
 * Format tanggal singkat (mis: Jan 2024)
 * @param {string} dateStr
 * @returns {string}
 */
export const formatDateShort = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
};
