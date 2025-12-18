<div class="flex items-center space-x-2">
    <i class="fas fa-globe text-gray-500"></i>
    <select onchange="changeLanguage(this.value)" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>English</option>
        <option value="id" {{ app()->getLocale() === 'id' ? 'selected' : '' }}>Indonesia</option>
    </select>
</div>

<script>
function changeLanguage(locale) {
    // Store language preference
    localStorage.setItem('preferred_locale', locale);
    
    // Reload page with language parameter
    const url = new URL(window.location.href);
    url.searchParams.set('lang', locale);
    window.location.href = url.toString();
}

// Auto-set language on page load
document.addEventListener('DOMContentLoaded', function() {
    const preferredLocale = localStorage.getItem('preferred_locale');
    if (preferredLocale && preferredLocale !== '{{ app()->getLocale() }}') {
        changeLanguage(preferredLocale);
    }
});
</script>
