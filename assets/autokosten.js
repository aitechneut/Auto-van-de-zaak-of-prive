// AutoKosten Calculator JavaScript
// Handles calculations, UI updates, and data management

// Global state voor meerdere auto's
let autoData = {};
let currentAutoId = null;
let autoCounter = 0;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCalculator();
});

/**
 * Initialize calculator
 */
function initializeCalculator() {
    // Setup event listeners
    setupEventListeners();
    
    // Load saved data if exists
    loadSavedData();
    
    // Initialize first auto
    if (Object.keys(autoData).length === 0) {
        addNewAuto();
    }
}

/**
 * Setup all event listeners
 */
function setupEventListeners() {
    // Kenteken format on input
    const kentekenInput = document.getElementById('kenteken');
    if (kentekenInput) {
        kentekenInput.addEventListener('input', formatKenteken);
        kentekenInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                lookupKenteken();
            }
        });
    }
    
    // Auto-calculate on value changes
    document.querySelectorAll('input[type="number"], select').forEach(element => {
        element.addEventListener('change', function() {
            if (currentAutoId) {
                updateAutoData();
                berekenKosten();
            }
        });
    });
    
    // Dark mode toggle
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', toggleDarkMode);
    }
}

/**
 * Format kenteken with dashes
 */
function formatKenteken(e) {
    let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    
    if (value.length >= 4) {
        let formatted = '';
        // Try XX-XX-XX format
        if (value.length <= 6) {
            formatted = value.slice(0,2) + '-' + value.slice(2,4) + '-' + value.slice(4,6);
        } else {
            // Try XX-XXX-X format
            formatted = value.slice(0,2) + '-' + value.slice(2,5) + '-' + value.slice(5,6);
        }
        
        if (formatted !== e.target.value) {
            e.target.value = formatted;
        }
    }
}

/**
 * Lookup kenteken via RDW API
 */
async function lookupKenteken() {
    const kentekenInput = document.getElementById('kenteken');
    const kenteken = kentekenInput.value.replace(/[^A-Z0-9]/g, '');
    const message = document.getElementById('message');
    const spinner = document.getElementById('lookup-spinner');
    const lookupText = document.getElementById('lookup-text');
    const autoInfo = document.getElementById('auto-info');
    
    // Reset messages
    message.classList.remove('active', 'error', 'success');
    
    if (kenteken.length < 6) {
        showMessage('Vul een geldig kenteken in (minimaal 6 tekens)', 'error');
        return;
    }
    
    // Show loading
    spinner.classList.add('active');
    lookupText.style.display = 'none';
    
    try {
        const response = await fetch(`?action=rdw_lookup&kenteken=${kenteken}`);
        const data = await response.json();
        
        if (data.success) {
            // Fill fields with RDW data
            fillAutoData(data.data);
            
            // Show auto info section
            autoInfo.classList.add('active');
            
            // Save to current auto
            if (currentAutoId) {
                updateAutoData();
            }
            
            showMessage('✅ Voertuiggegevens succesvol opgehaald!', 'success');
            
            // Auto-calculate
            setTimeout(berekenKosten, 500);
        } else {
            showMessage(`❌ ${data.error || 'Kenteken niet gevonden'}`, 'error');
        }
    } catch (error) {
        showMessage('❌ Er ging iets mis. Probeer het opnieuw.', 'error');
        console.error('RDW Lookup error:', error);
    } finally {
        // Hide loading
        spinner.classList.remove('active');
        lookupText.style.display = 'inline';
    }
}

/**
 * Fill form with auto data
 */
function fillAutoData(data) {
    // Update title
    document.getElementById('auto-title').textContent = 
        `${data.merk} ${data.handelsbenaming}`;
    document.getElementById('auto-badge').textContent = 
        `${data.brandstof} - ${data.bouwjaar}`;
    
    // Fill form fields
    const fields = {
        'merk_model': `${data.merk} ${data.handelsbenaming}`,
        'bouwjaar': data.bouwjaar,
        'brandstof': data.brandstof,
        'gewicht': data.gewicht,
        'dagwaarde': data.dagwaarde,
        'bijtelling_percentage': data.bijtelling_percentage,
        'mrb_per_maand': data.mrb_per_maand
    };
    
    for (const [fieldId, value] of Object.entries(fields)) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = value;
        }
    }
    
    // Estimate fuel consumption based on type
    const verbruikField = document.getElementById('verbruik');
    if (verbruikField) {
        if (data.brandstof.toLowerCase().includes('elektr')) {
            verbruikField.value = '18'; // kWh/100km
            document.getElementById('brandstofprijs').value = '0.35'; // €/kWh
        } else if (data.brandstof.toLowerCase().includes('diesel')) {
            verbruikField.value = '5.5';
            document.getElementById('brandstofprijs').value = '1.85';
        } else if (data.brandstof.toLowerCase().includes('hybrid')) {
            verbruikField.value = '4.5';
            document.getElementById('brandstofprijs').value = '1.95';
        } else {
            verbruikField.value = '7.0';
            document.getElementById('brandstofprijs').value = '1.95';
        }
    }
}

/**
 * Calculate costs
 */
async function berekenKosten() {
    if (!currentAutoId) return;
    
    // Gather all form data
    const formData = gatherFormData();
    
    // Show loading state
    showCalculating(true);
    
    try {
        // Send to PHP for calculation
        const response = await fetch('includes/calculator.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        // Display results
        displayResults(result);
        
        // Save to autoData
        autoData[currentAutoId].berekening = result;
        saveData();
        
    } catch (error) {
        // Fallback to client-side calculation
        const result = calculateClientSide(formData);
        displayResults(result);
    } finally {
        showCalculating(false);
    }
}

/**
 * Client-side calculation fallback
 */
function calculateClientSide(data) {
    // Parse numbers
    const dagwaarde = parseFloat(data.dagwaarde) || 15000;
    const cataloguswaarde = parseFloat(data.cataloguswaarde) || 30000;
    const bijtelling_pct = parseFloat(data.bijtelling_percentage) || 22;
    const inkomsten_belasting = parseFloat(data.inkomstenbelasting_percentage) || 37;
    const km_per_maand = parseInt(data.km_per_maand) || 2000;
    const verbruik = parseFloat(data.verbruik) || 7.0;
    const brandstofprijs = parseFloat(data.brandstofprijs) || 1.95;
    const verzekering = parseFloat(data.verzekering_per_maand) || 75;
    const onderhoud = parseFloat(data.onderhoud_per_maand) || 100;
    const mrb = parseFloat(data.mrb_per_maand) || 50;
    
    // Check if youngtimer
    const bouwjaar = parseInt(data.bouwjaar) || 2020;
    const leeftijd = new Date().getFullYear() - bouwjaar;
    const isYoungtimer = leeftijd >= 15 && leeftijd <= 30;
    
    // ZAKELIJK calculation
    const bijtelling_basis = isYoungtimer ? dagwaarde : cataloguswaarde;
    const bijtelling_percentage = isYoungtimer ? 35 : bijtelling_pct;
    const bijtelling_jaar = bijtelling_basis * (bijtelling_percentage / 100);
    const bijtelling_maand = bijtelling_jaar / 12;
    const extra_belasting_maand = bijtelling_maand * (inkomsten_belasting / 100);
    
    // PRIVE calculation
    const afschrijving = dagwaarde * 0.20 / 12; // 20% per jaar
    const brandstof = (km_per_maand / 100) * verbruik * brandstofprijs;
    const apk = leeftijd > 3 ? 50 / 12 : 0;
    
    const prive_totaal = afschrijving + brandstof + verzekering + onderhoud + mrb + apk;
    const zakelijk_totaal = extra_belasting_maand;
    
    // Determine best option
    const beste_optie = zakelijk_totaal < prive_totaal ? 'zakelijk' : 'prive';
    const verschil = Math.abs(zakelijk_totaal - prive_totaal);
    
    return {
        zakelijk: {
            maandelijks: {
                bijtelling: Math.round(bijtelling_maand * 100) / 100,
                extra_belasting: Math.round(extra_belasting_maand * 100) / 100,
                totaal: Math.round(zakelijk_totaal * 100) / 100
            },
            jaarlijks: {
                totaal: Math.round(zakelijk_totaal * 12 * 100) / 100
            }
        },
        prive: {
            maandelijks: {
                afschrijving: Math.round(afschrijving * 100) / 100,
                brandstof: Math.round(brandstof * 100) / 100,
                verzekering: Math.round(verzekering * 100) / 100,
                onderhoud: Math.round(onderhoud * 100) / 100,
                mrb: Math.round(mrb * 100) / 100,
                apk: Math.round(apk * 100) / 100,
                totaal: Math.round(prive_totaal * 100) / 100
            },
            jaarlijks: {
                totaal: Math.round(prive_totaal * 12 * 100) / 100
            }
        },
        advies: {
            beste_optie: beste_optie,
            verschil: Math.round(verschil * 100) / 100,
            verschil_jaar: Math.round(verschil * 12 * 100) / 100,
            tekst: beste_optie === 'zakelijk' 
                ? `Auto van de zaak is €${Math.round(verschil * 12)} per jaar voordeliger!`
                : `Privé auto is €${Math.round(verschil * 12)} per jaar voordeliger!`
        }
    };
}

/**
 * Display calculation results
 */
function displayResults(result) {
    // Check if results container exists, if not create it
    let resultsContainer = document.getElementById('results-container');
    if (!resultsContainer) {
        resultsContainer = document.createElement('div');
        resultsContainer.id = 'results-container';
        resultsContainer.className = 'results-section';
        document.querySelector('.main-content').appendChild(resultsContainer);
    }
    
    // Create results HTML
    const html = `
        <div class="results-header">
            <h2>📊 Berekening Resultaat</h2>
            <button class="btn-export" onclick="exportResults()">📥 Export</button>
        </div>
        
        <div class="results-grid">
            <div class="result-card zakelijk">
                <h3>🏢 Auto van de Zaak</h3>
                <div class="result-total">
                    €${formatNumber(result.zakelijk.maandelijks.totaal)}/maand
                </div>
                <div class="result-details">
                    <div>Bijtelling: €${formatNumber(result.zakelijk.maandelijks.bijtelling)}</div>
                    <div>Extra belasting: €${formatNumber(result.zakelijk.maandelijks.extra_belasting)}</div>
                    <div class="result-year">Per jaar: €${formatNumber(result.zakelijk.jaarlijks.totaal)}</div>
                </div>
            </div>
            
            <div class="result-card prive">
                <h3>🚗 Privé Auto</h3>
                <div class="result-total">
                    €${formatNumber(result.prive.maandelijks.totaal)}/maand
                </div>
                <div class="result-details">
                    <div>Afschrijving: €${formatNumber(result.prive.maandelijks.afschrijving)}</div>
                    <div>Brandstof: €${formatNumber(result.prive.maandelijks.brandstof)}</div>
                    <div>Verzekering: €${formatNumber(result.prive.maandelijks.verzekering)}</div>
                    <div>Onderhoud: €${formatNumber(result.prive.maandelijks.onderhoud)}</div>
                    <div>MRB: €${formatNumber(result.prive.maandelijks.mrb)}</div>
                    <div class="result-year">Per jaar: €${formatNumber(result.prive.jaarlijks.totaal)}</div>
                </div>
            </div>
        </div>
        
        <div class="advice-section ${result.advies.beste_optie}">
            <h3>💡 Advies</h3>
            <p class="advice-text">${result.advies.tekst}</p>
            <p class="advice-saving">
                Besparing: <strong>€${formatNumber(result.advies.verschil_jaar)}</strong> per jaar
            </p>
        </div>
        
        <div class="chart-container">
            <canvas id="kostenvergelijking"></canvas>
        </div>
    `;
    
    resultsContainer.innerHTML = html;
    resultsContainer.classList.add('active');
    
    // Create chart
    setTimeout(() => createComparisonChart(result), 100);
}

/**
 * Create comparison chart
 */
function createComparisonChart(data) {
    const ctx = document.getElementById('kostenvergelijking');
    if (!ctx) return;
    
    // Simple bar chart with canvas
    const canvas = ctx;
    const context = canvas.getContext('2d');
    
    // Set canvas size
    canvas.width = canvas.offsetWidth;
    canvas.height = 300;
    
    // Clear canvas
    context.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw bars
    const barWidth = 100;
    const barSpacing = 50;
    const startX = (canvas.width - (barWidth * 2 + barSpacing)) / 2;
    
    const maxValue = Math.max(
        data.zakelijk.maandelijks.totaal,
        data.prive.maandelijks.totaal
    );
    
    const scale = 200 / maxValue;
    
    // Zakelijk bar
    const zakelijkHeight = data.zakelijk.maandelijks.totaal * scale;
    context.fillStyle = '#667eea';
    context.fillRect(startX, 250 - zakelijkHeight, barWidth, zakelijkHeight);
    
    // Prive bar
    const priveHeight = data.prive.maandelijks.totaal * scale;
    context.fillStyle = '#764ba2';
    context.fillRect(startX + barWidth + barSpacing, 250 - priveHeight, barWidth, priveHeight);
    
    // Labels
    context.fillStyle = '#333';
    context.font = '14px sans-serif';
    context.textAlign = 'center';
    context.fillText('Zakelijk', startX + barWidth/2, 270);
    context.fillText('Privé', startX + barWidth + barSpacing + barWidth/2, 270);
    
    // Values
    context.fillText(
        `€${formatNumber(data.zakelijk.maandelijks.totaal)}`, 
        startX + barWidth/2, 
        250 - zakelijkHeight - 10
    );
    context.fillText(
        `€${formatNumber(data.prive.maandelijks.totaal)}`, 
        startX + barWidth + barSpacing + barWidth/2, 
        250 - priveHeight - 10
    );
}

/**
 * Gather form data
 */
function gatherFormData() {
    const fields = [
        'kenteken', 'merk_model', 'bouwjaar', 'brandstof', 'gewicht',
        'dagwaarde', 'cataloguswaarde', 'aankoopprijs', 'restwaarde',
        'afschrijving_jaren', 'bijtelling_percentage', 'mrb_per_maand',
        'km_per_maand', 'km_prive_per_maand', 'verbruik', 'brandstofprijs',
        'verzekering_per_maand', 'onderhoud_per_maand', 'gebruikstype'
    ];
    
    const data = {};
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            data[field] = element.value;
        }
    });
    
    // Add calculated fields
    data.inkomstenbelasting_percentage = 37; // Default
    
    return data;
}

/**
 * Update current auto data
 */
function updateAutoData() {
    if (!currentAutoId) return;
    
    const data = gatherFormData();
    autoData[currentAutoId] = {
        ...autoData[currentAutoId],
        ...data,
        laatstBijgewerkt: new Date().toISOString()
    };
    
    saveData();
}

/**
 * Add new auto
 */
function addNewAuto() {
    autoCounter++;
    const autoId = `auto_${autoCounter}`;
    
    autoData[autoId] = {
        id: autoId,
        naam: `Auto ${autoCounter}`,
        toegevoegd: new Date().toISOString()
    };
    
    currentAutoId = autoId;
    
    // Clear form
    document.querySelectorAll('input').forEach(input => {
        if (input.type !== 'button' && input.type !== 'submit') {
            input.value = '';
        }
    });
    
    // Update UI
    updateAutoList();
    saveData();
}

/**
 * Delete auto
 */
function deleteAuto(autoId) {
    if (confirm('Weet je zeker dat je deze auto wilt verwijderen?')) {
        delete autoData[autoId];
        
        if (currentAutoId === autoId) {
            currentAutoId = Object.keys(autoData)[0] || null;
            if (!currentAutoId) {
                addNewAuto();
            }
        }
        
        updateAutoList();
        saveData();
    }
}

/**
 * Switch to auto
 */
function switchToAuto(autoId) {
    if (currentAutoId) {
        updateAutoData();
    }
    
    currentAutoId = autoId;
    const auto = autoData[autoId];
    
    if (auto) {
        // Fill form with auto data
        for (const [key, value] of Object.entries(auto)) {
            const element = document.getElementById(key);
            if (element && element.tagName === 'INPUT') {
                element.value = value;
            }
        }
        
        // Show auto info if data exists
        if (auto.merk_model) {
            document.getElementById('auto-info').classList.add('active');
        }
        
        // Recalculate if data exists
        if (auto.berekening) {
            displayResults(auto.berekening);
        }
    }
    
    updateAutoList();
}

/**
 * Update auto list UI
 */
function updateAutoList() {
    // This would update a list of autos in the UI
    // For now, just console log
    console.log('Current autos:', autoData);
}

/**
 * Save data to localStorage
 */
function saveData() {
    try {
        localStorage.setItem('autoKostenData', JSON.stringify(autoData));
        localStorage.setItem('autoKostenCounter', autoCounter.toString());
    } catch (e) {
        console.error('Could not save data:', e);
    }
}

/**
 * Load saved data
 */
function loadSavedData() {
    try {
        const saved = localStorage.getItem('autoKostenData');
        if (saved) {
            autoData = JSON.parse(saved);
            autoCounter = parseInt(localStorage.getItem('autoKostenCounter') || '0');
            
            // Load first auto
            const firstAutoId = Object.keys(autoData)[0];
            if (firstAutoId) {
                switchToAuto(firstAutoId);
            }
        }
    } catch (e) {
        console.error('Could not load saved data:', e);
    }
}

/**
 * Export results
 */
function exportResults() {
    if (!currentAutoId || !autoData[currentAutoId].berekening) {
        alert('Geen berekening om te exporteren');
        return;
    }
    
    const data = autoData[currentAutoId];
    const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `autokosten_${data.kenteken || 'export'}_${new Date().toISOString().split('T')[0]}.json`;
    a.click();
}

/**
 * Toggle dark mode
 */
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
}

/**
 * Show message
 */
function showMessage(text, type = 'info') {
    const message = document.getElementById('message');
    if (message) {
        message.textContent = text;
        message.className = `message active ${type}`;
        
        setTimeout(() => {
            message.classList.remove('active');
        }, 5000);
    }
}

/**
 * Show calculating state
 */
function showCalculating(show) {
    const button = document.querySelector('.btn-calculate');
    if (button) {
        if (show) {
            button.disabled = true;
            button.textContent = '⏳ Berekenen...';
        } else {
            button.disabled = false;
            button.textContent = '💰 Bereken Autokosten';
        }
    }
}

/**
 * Format number for display
 */
function formatNumber(num) {
    return new Intl.NumberFormat('nl-NL', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num);
}

/**
 * Toggle collapsible sections
 */
function toggleCollapsible(element) {
    element.classList.toggle('active');
}
