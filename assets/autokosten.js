// AutoKosten Calculator JavaScript
// Professional Business Version 2.0

document.addEventListener('DOMContentLoaded', function() {
    console.log('AutoKosten Calculator loaded');
    initializeCalculator();
});

function initializeCalculator() {
    // Setup event listeners
    setupEventListeners();
    
    // Set default values
    setDefaultValues();
}

function setupEventListeners() {
    // Kenteken lookup
    const rdwButton = document.getElementById('rdw-lookup');
    const kentekenInput = document.getElementById('kenteken');
    
    if (rdwButton) {
        rdwButton.addEventListener('click', lookupKenteken);
    }
    
    if (kentekenInput) {
        kentekenInput.addEventListener('input', formatKenteken);
        kentekenInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                lookupKenteken();
            }
        });
    }
    
    // Calculate button
    const calculateButton = document.getElementById('calculate');
    if (calculateButton) {
        calculateButton.addEventListener('click', berekenKosten);
    }
    
    // Auto-calculate on input changes
    const inputs = document.querySelectorAll('input[type="number"]');
    inputs.forEach(input => {
        input.addEventListener('change', debounce(berekenKosten, 500));
    });
}

function setDefaultValues() {
    // Set current year defaults
    const currentYear = new Date().getFullYear();
    
    // Update any year-dependent calculations
    updateBijtelling();
}

function formatKenteken() {
    const input = document.getElementById('kenteken');
    if (!input) return;
    
    let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    
    if (value.length <= 6) {
        // XX-XX-XX format (old)
        value = value.replace(/(\w{2})(\w{2})(\w{2})/, '$1-$2-$3');
    } else {
        // XX-XXX-X format (new)
        value = value.replace(/(\w{2})(\w{3})(\w{1,3})/, '$1-$2-$3');
    }
    
    input.value = value;
}

async function lookupKenteken() {
    const kentekenInput = document.getElementById('kenteken');
    const rdwButton = document.getElementById('rdw-lookup');
    
    if (!kentekenInput || !kentekenInput.value.trim()) {
        alert('Voer eerst een kenteken in');
        return;
    }
    
    const kenteken = kentekenInput.value.trim();
    
    // Update button state
    rdwButton.disabled = true;
    rdwButton.textContent = 'Ophalen...';
    
    try {
        const response = await fetch(`?action=rdw_lookup&kenteken=${encodeURIComponent(kenteken)}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            // Update vehicle info display
            updateVehicleInfo(data.data);
            
            // Update form fields
            updateFormFields(data.data);
            
            // Show vehicle info section
            const vehicleInfo = document.getElementById('vehicle-info');
            if (vehicleInfo) {
                vehicleInfo.style.display = 'block';
            }
            
            // Auto-calculate
            berekenKosten();
            
        } else {
            alert(data.error || 'Kenteken niet gevonden');
        }
        
    } catch (error) {
        console.error('RDW lookup error:', error);
        alert('Fout bij ophalen gegevens. Probeer opnieuw.');
    } finally {
        // Reset button
        rdwButton.disabled = false;
        rdwButton.textContent = 'Gegevens Ophalen';
    }
}

function updateVehicleInfo(data) {
    // Update vehicle info display
    const merkModel = document.getElementById('merk-model');
    const bouwjaar = document.getElementById('bouwjaar');
    const brandstof = document.getElementById('brandstof');
    const gewicht = document.getElementById('gewicht');
    const bijtelling = document.getElementById('bijtelling-info');
    
    if (merkModel) merkModel.textContent = `${data.merk} ${data.handelsbenaming}`;
    if (bouwjaar) bouwjaar.textContent = data.bouwjaar;
    if (brandstof) brandstof.textContent = data.brandstof;
    if (gewicht) gewicht.textContent = data.gewicht;
    if (bijtelling) {
        bijtelling.textContent = `${data.bijtelling_percentage}% (${data.bijtelling_reden})`;
    }
}

function updateFormFields(data) {
    // Update form inputs with RDW data
    const fields = {
        'cataloguswaarde': data.dagwaarde * 1.5, // Estimate catalogus from dagwaarde
        'dagwaarde': data.dagwaarde,
        'bijtelling_percentage': data.bijtelling_percentage,
        'mrb': data.mrb_per_maand
    };
    
    Object.keys(fields).forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field && fields[fieldId]) {
            field.value = Math.round(fields[fieldId]);
        }
    });
    
    // Update aankoopprijs to match cataloguswaarde
    const catalogus = document.getElementById('cataloguswaarde');
    const aankoopprijs = document.getElementById('aankoopprijs');
    if (catalogus && aankoopprijs) {
        aankoopprijs.value = catalogus.value;
    }
    
    // Update restwaarde (30% of aankoopprijs)
    const restwaarde = document.getElementById('restwaarde');
    if (aankoopprijs && restwaarde) {
        restwaarde.value = Math.round(aankoopprijs.value * 0.3);
    }
}

async function berekenKosten() {
    // Get all form values
    const formData = getFormData();
    
    if (!formData.km_per_maand || !formData.bruto_salaris) {
        // Don't calculate if essential fields are missing
        return;
    }
    
    try {
        // Show calculating state
        showCalculating(true);
        
        const response = await fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'calculate',
                ...formData
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateResults(data);
            showResults();
        } else {
            console.error('Calculation error:', data);
        }
        
    } catch (error) {
        console.error('Calculation request error:', error);
    } finally {
        showCalculating(false);
    }
}

function getFormData() {
    const fields = [
        'km_per_maand', 'bruto_salaris', 'cataloguswaarde', 'dagwaarde',
        'bijtelling_percentage', 'brandstofprijs', 'verbruik', 'verzekering',
        'onderhoud', 'mrb', 'aankoopprijs', 'restwaarde'
    ];
    
    const data = {};
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            data[fieldId] = field.value || 0;
        }
    });
    
    return data;
}

function updateResults(data) {
    // Update zakelijk costs
    updateElement('zakelijk-bijtelling', data.zakelijk.bijtelling_per_maand);
    updateElement('zakelijk-belasting', data.zakelijk.belasting_per_maand);
    updateElement('zakelijk-totaal-maand', data.zakelijk.totaal_per_maand);
    updateElement('zakelijk-totaal-jaar', data.zakelijk.totaal_per_jaar);
    
    // Update privé costs
    updateElement('prive-afschrijving', data.prive.afschrijving_per_maand);
    updateElement('prive-brandstof', data.prive.brandstof_per_maand);
    updateElement('prive-verzekering', data.prive.verzekering_per_maand);
    updateElement('prive-onderhoud', data.prive.onderhoud_per_maand);
    updateElement('prive-mrb', data.prive.mrb_per_maand);
    updateElement('prive-apk', data.prive.apk_per_maand);
    updateElement('prive-totaal-maand', data.prive.totaal_per_maand);
    updateElement('prive-totaal-jaar', data.prive.totaal_per_jaar);
    
    // Update advice
    const adviceElement = document.getElementById('advice');
    if (adviceElement) {
        adviceElement.textContent = data.vergelijking.advies;
        
        // Add color based on advice
        if (data.vergelijking.advies.includes('zakelijk')) {
            adviceElement.style.color = '#1e40af';
        } else if (data.vergelijking.advies.includes('privé')) {
            adviceElement.style.color = '#059669';
        } else {
            adviceElement.style.color = '#f59e0b';
        }
    }
    
    // Update differences
    updateElement('verschil-maand', Math.abs(data.vergelijking.verschil_per_maand));
    updateElement('verschil-jaar', Math.abs(data.vergelijking.verschil_per_jaar));
}

function updateElement(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = formatNumber(value);
    }
}

function showResults() {
    const resultsSection = document.getElementById('results');
    if (resultsSection) {
        resultsSection.style.display = 'block';
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }
}

function showCalculating(calculating) {
    const button = document.getElementById('calculate');
    if (button) {
        if (calculating) {
            button.disabled = true;
            button.textContent = '⏳ Berekenen...';
        } else {
            button.disabled = false;
            button.textContent = 'Bereken Vergelijking';
        }
    }
}

function formatNumber(num) {
    return new Intl.NumberFormat('nl-NL', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num);
}

function updateBijtelling() {
    // Update bijtelling percentage based on current rules
    // This could be expanded to automatically update based on vehicle data
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions for testing
window.AutoKostenCalculator = {
    lookupKenteken,
    berekenKosten,
    formatKenteken,
    updateVehicleInfo,
    updateFormFields
};