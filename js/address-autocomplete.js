function initializeAddressAutocomplete() {
    const input = document.getElementById('address');
    const suggestions = document.getElementById('suggestions');

    // Only initialize if elements exist on the page
    if (!input || !suggestions) return;

    input.addEventListener('input', async () => {
        const query = input.value.trim();
        if (query.length < 3) {
            suggestions.innerHTML = '';
            suggestions.style.display = 'none';
            return;
        }
        
        try {
            const res = await fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5`);
            const data = await res.json();

            if (data.features && data.features.length > 0) {
                suggestions.innerHTML = data.features.map(place => {
                    const props = place.properties;
                    const fullAddress = `${props.name || ''}, ${props.city || ''}, ${props.state || ''}, ${props.country || ''}`;
                    return `<li onclick="selectAddress('${fullAddress.replace(/'/g, "\\'")}', '${(props.city || '').replace(/'/g, "\\'")}', '${(props.state || '').replace(/'/g, "\\'")}', '${(props.country || '').replace(/'/g, "\\'")}')">${fullAddress}</li>`;
                }).join('');
                suggestions.style.display = 'block';
            } else {
                suggestions.innerHTML = '';
                suggestions.style.display = 'none';
            }
        } catch (error) {
            suggestions.innerHTML = '<li>Error loading suggestions</li>';
            suggestions.style.display = 'block';
        }
    });

    // Make the function available globally
    window.selectAddress = function(full, city, province, country) {
        document.getElementById('address').value = full;
        if (document.getElementById('city')) document.getElementById('city').value = city;
        if (document.getElementById('province')) document.getElementById('province').value = province;
        if (document.getElementById('country')) document.getElementById('country').value = country;
        suggestions.innerHTML = '';
        suggestions.style.display = 'none';
    };

    window.addEventListener('click', function(e) {
        if (e.target.id !== 'address' && !suggestions.contains(e.target)) {
            suggestions.innerHTML = '';
            suggestions.style.display = 'none';
        }
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeAddressAutocomplete);