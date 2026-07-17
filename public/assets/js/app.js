/**
 * JointaSoft - Application JavaScript
 * ES2025
 */

'use strict';

/**
 * Format a number as currency.
 */
const formatCurrency = (amount, currency = 'TZS') => {
    const num = parseFloat(amount) || 0;
    return `${currency} ${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

/**
 * Calculate contribution amount based on sale amount and receipt status.
 */
const calculateContribution = (saleAmount, hasReceipt = false) => {
    const rate = hasReceipt ? 3 : 10;
    const amount = parseFloat(saleAmount) || 0;
    const contribution = (amount * rate) / 100;
    return { rate, contribution };
};

/**
 * Show a toast notification.
 */
const showToast = (message, type = 'success', duration = 5000) => {
    const container = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');

    const bgColors = {
        success: 'bg-emerald-50 border-emerald-200 text-emerald-700',
        error: 'bg-red-50 border-red-200 text-red-700',
        warning: 'bg-amber-50 border-amber-200 text-amber-700',
        info: 'bg-blue-50 border-blue-200 text-blue-700',
    };

    toast.className = `flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-medium border shadow-lg animate-slide-in ${bgColors[type] || bgColors.info}`;
    toast.innerHTML = `<span>${message}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
};

const createToastContainer = () => {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'fixed top-4 right-4 z-50 space-y-2 max-w-sm';
    document.body.appendChild(container);
    return container;
};

/**
 * Confirm action dialog replacement.
 */
const confirmAction = (message = 'Are you sure?') => {
    return window.confirm(message);
};

/**
 * Format file size in human readable form.
 */
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
