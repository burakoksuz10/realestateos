import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import Sortable from 'sortablejs';

// Make libraries available globally
window.Alpine = Alpine;
window.Chart = Chart;
window.Sortable = Sortable;

// Initialize Alpine.js
Alpine.start();

// Global utilities
window.ReCRM = {
    // Format currency
    formatCurrency(amount, currency = 'TRY') {
        const symbols = { TRY: '₺', USD: '$', EUR: '€', GBP: '£' };
        const symbol = symbols[currency] || currency;
        return symbol + new Intl.NumberFormat('tr-TR').format(amount);
    },

    // Format date
    formatDate(date, format = 'short') {
        const d = new Date(date);
        const options = format === 'short' 
            ? { day: '2-digit', month: '2-digit', year: 'numeric' }
            : { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' };
        return d.toLocaleDateString('tr-TR', options);
    },

    // Show notification
    notify(type, title, message) {
        const event = new CustomEvent('notify', {
            detail: { type, title, message }
        });
        window.dispatchEvent(event);
    },

    // Confirm dialog
    async confirm(title, message) {
        return new Promise((resolve) => {
            const event = new CustomEvent('confirm', {
                detail: { title, message, resolve }
            });
            window.dispatchEvent(event);
        });
    },

    // Copy to clipboard
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.notify('success', 'Kopyalandı', 'Metin panoya kopyalandı.');
            return true;
        } catch (err) {
            this.notify('error', 'Hata', 'Kopyalama başarısız oldu.');
            return false;
        }
    },

    // Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // API request helper
    async api(endpoint, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
        };

        const response = await fetch(`/api/v1${endpoint}`, {
            ...defaultOptions,
            ...options,
            headers: { ...defaultOptions.headers, ...options.headers },
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'API request failed');
        }

        return response.json();
    },
};

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Cmd/Ctrl + K for search
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        document.dispatchEvent(new CustomEvent('open-search'));
    }

    // Escape to close modals
    if (e.key === 'Escape') {
        document.dispatchEvent(new CustomEvent('close-modal'));
    }
});

// Auto-resize textareas
document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});

// Initialize tooltips
document.querySelectorAll('[data-tooltip]').forEach(el => {
    el.addEventListener('mouseenter', function() {
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded shadow-lg';
        tooltip.textContent = this.dataset.tooltip;
        tooltip.style.top = this.offsetTop - 30 + 'px';
        tooltip.style.left = this.offsetLeft + 'px';
        tooltip.id = 'tooltip';
        document.body.appendChild(tooltip);
    });

    el.addEventListener('mouseleave', function() {
        document.getElementById('tooltip')?.remove();
    });
});

console.log('🏠 ReCRM initialized');
