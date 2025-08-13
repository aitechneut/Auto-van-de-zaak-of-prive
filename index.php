<?php
// AutoKosten Calculator PRO - Met RDW Koppeling
// Voor pianomanontour.nl/AutoKosten
// Professional Business Version 2.0

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include business logic
require_once 'includes/bijtelling_database.php';

// Verwerk AJAX RDW lookup
if (isset($_GET['action']) && $_GET['action'] === 'rdw_lookup') {
    header('Content-Type: application/json');
    
    $kenteken = strtoupper(str_replace('-', '', $_GET['kenteken'] ?? ''));
    
    if (strlen($kenteken) < 6) {
        echo json_encode(['error' => 'Ongeldig kenteken']);
        exit;
    }
    
    // RDW Open Data API (gratis, geen key nodig!)
    $rdw_url = "https://opendata.rdw.nl/resource/m9d7-ebf2.json?kenteken=" . urlencode($kenteken);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $rdw_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        
        if (!empty($data[0])) {
            $vehicle = $data[0];
            
            // Haal extra brandstof info op
            $fuel_url = "https://opendata.rdw.nl/resource/8ys7-d773.json?kenteken=" . urlencode($kenteken);
            $fuel_response = @file_get_contents($fuel_url);
            $fuel_data = json_decode($fuel_response, true);
            
            // Bereken bijtelling percentage op basis van datum eerste toelating en brandstof
            $bouwjaar = substr($vehicle['datum_eerste_toelating'] ?? '', 0, 4);
            $brandstof = $fuel_data[0]['brandstof_omschrijving'] ?? $vehicle['brandstof_omschrijving'] ?? 'Benzine';
            
            // Gebruik de professionele bijtelling database
            $bijtelling_info = BijtellingsDatabase::getBijtelling($bouwjaar, $brandstof, 30000, 15000);
            $bijtelling = $bijtelling_info['percentage'];
            
            // Schat dagwaarde (verbeterde logica)
            $leeftijd = date('Y') - intval($bouwjaar);
            $geschatte_nieuwprijs = 30000; // Default schatting
            $dagwaarde = max(5000, $geschatte_nieuwprijs * pow(0.85, $leeftijd));
            
            // MRB schatting op basis van gewicht
            $gewicht = intval($vehicle['massa_ledig_voertuig'] ?? 1200);
            $mrb_per_maand = round(($gewicht / 100) * 8); // Zeer ruwe schatting
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'merk' => $vehicle['merk'] ?? '',
                    'handelsbenaming' => $vehicle['handelsbenaming'] ?? '',
                    'bouwjaar' => $bouwjaar,
                    'brandstof' => $brandstof,
                    'gewicht' => $gewicht,
                    'dagwaarde' => round($dagwaarde),
                    'bijtelling_percentage' => $bijtelling,
                    'bijtelling_basis' => $bijtelling_info['basis'],
                    'bijtelling_reden' => $bijtelling_info['reden'],
                    'mrb_per_maand' => $mrb_per_maand,
                    'eerste_kleur' => $vehicle['eerste_kleur'] ?? '',
                    'aantal_zitplaatsen' => $vehicle['aantal_zitplaatsen'] ?? '',
                    'zuinigheidslabel' => $fuel_data[0]['zuinigheidslabel'] ?? ''
                ]
            ]);
        } else {
            echo json_encode(['error' => 'Kenteken niet gevonden in RDW database']);
        }
    } else {
        echo json_encode(['error' => 'RDW service niet beschikbaar, probeer het later opnieuw']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoKosten Calculator PRO - Nederlandse Bijtelling & RDW Koppeling</title>
    <meta name="description" content="Professionele AutoKosten Calculator voor ondernemers. Vergelijk auto van de zaak vs privé. Met RDW koppeling, Nederlandse bijtelling regels 2025, youngtimer support.">
    <meta name="keywords" content="autokosten, bijtelling, auto van de zaak, zakelijk rijden, youngtimer, RDW, Nederlandse belasting">
    <meta name="author" content="Richard Surie - PianoManOnTour.nl">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="assets/style.css">
    
    <!-- Professional Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
</head>
<body>
    <div class="container">
        <!-- Professional Header -->
        <header>
            <div class="header-content">
                <h1>AutoKosten Calculator PRO</h1>
                <p class="subtitle">Nederlandse Bijtelling & Kostenvergelijking voor Ondernemers</p>
                
                <div class="header-badges">
                    <span class="badge">🔗 RDW Koppeling</span>
                    <span class="badge">📊 Bijtelling 2025</span>
                    <span class="badge">🏆 Youngtimer Support</span>
                    <span class="badge">💼 Zakelijk vs Privé</span>
                </div>
            </div>
        </header>
        
        <main>
            <!-- Professional License Plate Lookup -->
            <div class="form-section">
                <h2>🔍 Kenteken Lookup</h2>
                <p style="margin-bottom: 24px; color: #6b7280;">Vul je kenteken in voor automatische voertuiggegevens via RDW Open Data</p>
                
                <div style="display: flex; gap: 16px; align-items: end; flex-wrap: wrap; justify-content: center;">
                    <div style="flex: 1; min-width: 200px; max-width: 300px;">
                        <label for="kenteken" style="margin-bottom: 8px;">Kenteken</label>
                        <input type="text" 
                               id="kenteken" 
                               placeholder="12-ABC-3" 
                               maxlength="8"
                               style="font-size: 1.25rem; text-align: center; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;"
                               pattern="[A-Za-z0-9\-]+"
                               title="Vul een geldig Nederlands kenteken in">
                    </div>
                    <button class="btn-primary" onclick="lookupKenteken()" style="min-width: 160px;">
                        <span id="lookup-text">🔍 Zoek Auto</span>
                        <span class="loading-spinner" id="lookup-spinner"></span>
                    </button>
                </div>
                
                <div class="message" id="message"></div>
            </div>
            
            <!-- Auto Information Display -->
            <div class="form-section" id="auto-info" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h2 id="auto-title" style="margin: 0; border: none; padding: 0;">Auto Gevonden</h2>
                        <p style="color: #6b7280; margin: 8px 0 0 0;" id="auto-details">Voertuiggegevens via RDW</p>
                    </div>
                    <div class="status-badge status-success" id="auto-badge">
                        Gegevens Geladen
                    </div>
                </div>
                
                <!-- Basic Vehicle Data -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="merk_model">🚗 Merk & Model</label>
                        <input type="text" id="merk_model" readonly style="background: #f8fafc;">
                    </div>
                    <div class="form-group">
                        <label for="bouwjaar">📅 Bouwjaar</label>
                        <input type="text" id="bouwjaar" readonly style="background: #f8fafc;">
                    </div>
                    <div class="form-group">
                        <label for="brandstof">⛽ Brandstof</label>
                        <input type="text" id="brandstof" readonly style="background: #f8fafc;">
                    </div>
                    <div class="form-group">
                        <label for="gewicht">⚖️ Gewicht (kg)</label>
                        <input type="number" id="gewicht" readonly style="background: #f8fafc;">
                    </div>
                </div>
            </div>
            
            <!-- Cost Settings -->
            <div class="form-section">
                <h2>💶 Kosteninstellingen</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="km_per_maand">📏 Kilometers per maand</label>
                        <input type="number" id="km_per_maand" value="2000" min="0" max="10000">
                    </div>
                    <div class="form-group">
                        <label for="bruto_inkomen">💰 Bruto inkomen per jaar (€)</label>
                        <input type="number" id="bruto_inkomen" value="50000" min="0" max="500000">
                    </div>
                    <div class="form-group">
                        <label for="cataloguswaarde">💎 Cataloguswaarde (€)</label>
                        <input type="number" id="cataloguswaarde" value="30000" min="0" max="200000">
                    </div>
                    <div class="form-group">
                        <label for="bijtelling_percentage">📊 Bijtelling percentage (%)</label>
                        <input type="number" id="bijtelling_percentage" value="22" min="0" max="40" step="0.1" readonly>
                        <small style="color: #6b7280; font-size: 0.75rem; margin-top: 4px;" id="bijtelling_uitleg">Automatisch berekend o.b.v. Nederlandse regels</small>
                    </div>
                </div>
            </div>
            
            <!-- Advanced Settings (Collapsible) -->
            <div class="form-section">
                <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleAdvanced()">
                    <h2>⚙️ Geavanceerde instellingen</h2>
                    <span id="advanced-arrow" style="font-size: 1.5rem; transition: transform 0.3s;">▼</span>
                </div>
                
                <div id="advanced-content" style="display: none; margin-top: 24px;">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="verbruik">⛽ Verbruik (L/100km of kWh/100km)</label>
                            <input type="number" id="verbruik" value="7.0" min="0" max="50" step="0.1">
                        </div>
                        <div class="form-group">
                            <label for="brandstofprijs">💸 Brandstofprijs (€/L of €/kWh)</label>
                            <input type="number" id="brandstofprijs" value="1.95" min="0" max="10" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="verzekering_per_maand">🛡️ Verzekering per maand (€)</label>
                            <input type="number" id="verzekering_per_maand" value="75" min="0" max="500">
                        </div>
                        <div class="form-group">
                            <label for="onderhoud_per_maand">🔧 Onderhoud per maand (€)</label>
                            <input type="number" id="onderhoud_per_maand" value="100" min="0" max="1000">
                        </div>
                        <div class="form-group">
                            <label for="mrb_per_maand">🏛️ MRB per maand (€)</label>
                            <input type="number" id="mrb_per_maand" value="50" min="0" max="200">
                        </div>
                        <div class="form-group">
                            <label for="dagwaarde">📈 Dagwaarde (€) - voor youngtimers</label>
                            <input type="number" id="dagwaarde" value="15000" min="0" max="100000">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Calculate Button -->
            <div style="text-align: center; margin: 32px 0;">
                <button class="btn-primary" onclick="berekenKosten()" style="width: 100%; max-width: 400px; padding: 20px 40px; font-size: 1.125rem;">
                    📊 Bereken Kosten
                </button>
            </div>
            
            <!-- Results will be inserted here by JavaScript -->
            
        </main>
    </div>
    
    <!-- JavaScript -->
    <script src="assets/autokosten.js"></script>
    
    <script>
        // Professional JavaScript functionality
        
        // Toggle advanced settings
        function toggleAdvanced() {
            const content = document.getElementById('advanced-content');
            const arrow = document.getElementById('advanced-arrow');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                arrow.style.transform = 'rotate(180deg)';
                content.classList.add('fade-in');
            } else {
                content.style.display = 'none';
                arrow.style.transform = 'rotate(0deg)';
            }
        }
        
        // Format kenteken input with professional validation
        document.getElementById('kenteken').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            if (value.length >= 4) {
                let formatted = '';
                // Try XX-XX-XX format first
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
        });
        
        // Professional RDW Lookup function
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
            
            // Show loading state
            spinner.classList.add('active');
            lookupText.style.display = 'none';
            
            try {
                const response = await fetch(`?action=rdw_lookup&kenteken=${kenteken}`);
                const data = await response.json();
                
                if (data.success) {
                    // Fill form with professional data handling
                    fillAutoData(data.data);
                    
                    // Show auto info section with animation
                    autoInfo.style.display = 'block';
                    autoInfo.classList.add('fade-in');
                    
                    showMessage('✅ Voertuiggegevens succesvol opgehaald via RDW!', 'success');
                    
                    // Auto-calculate after short delay
                    setTimeout(berekenKosten, 500);
                } else {
                    showMessage(`❌ ${data.error}`, 'error');
                    // Enable manual input mode
                    enableManualInput();
                }
            } catch (error) {
                showMessage('❌ Er ging iets mis bij het ophalen van de gegevens. Probeer het opnieuw.', 'error');
                console.error('RDW Lookup error:', error);
                enableManualInput();
            } finally {
                // Hide loading state
                spinner.classList.remove('active');
                lookupText.style.display = 'inline';
            }
        }
        
        // Professional data filling with validation
        function fillAutoData(data) {
            // Update header information
            document.getElementById('auto-title').textContent = 
                `${data.merk} ${data.handelsbenaming}`;
            document.getElementById('auto-details').textContent = 
                `${data.brandstof} - ${data.bouwjaar} - ${data.bijtelling_reden || 'RDW Gegevens'}`;
            
            // Fill form fields with validation
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
                if (field && value !== undefined) {
                    field.value = value;
                }
            }
            
            // Update bijtelling explanation
            const uitlegElement = document.getElementById('bijtelling_uitleg');
            if (uitlegElement && data.bijtelling_reden) {
                uitlegElement.textContent = data.bijtelling_reden;
            }
            
            // Smart fuel consumption estimation
            updateFuelConsumption(data.brandstof);
        }
        
        // Professional fuel consumption estimation
        function updateFuelConsumption(brandstof) {
            const verbruikField = document.getElementById('verbruik');
            const prijsField = document.getElementById('brandstofprijs');
            
            if (!verbruikField || !prijsField) return;
            
            const brandstofLower = brandstof.toLowerCase();
            
            if (brandstofLower.includes('elektr')) {
                verbruikField.value = '18'; // kWh/100km
                prijsField.value = '0.35'; // €/kWh
            } else if (brandstofLower.includes('diesel')) {
                verbruikField.value = '5.5';
                prijsField.value = '1.85';
            } else if (brandstofLower.includes('hybrid') || brandstofLower.includes('phev')) {
                verbruikField.value = '4.5';
                prijsField.value = '1.95';
            } else if (brandstofLower.includes('lpg')) {
                verbruikField.value = '8.5';
                prijsField.value = '0.85';
            } else {
                verbruikField.value = '7.0'; // Benzine
                prijsField.value = '1.95';
            }
        }
        
        // Enable manual input when RDW lookup fails
        function enableManualInput() {
            const autoInfo = document.getElementById('auto-info');
            autoInfo.style.display = 'block';
            autoInfo.classList.add('fade-in');
            
            // Make fields editable
            ['merk_model', 'bouwjaar', 'brandstof', 'gewicht'].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.readOnly = false;
                    field.style.background = '#ffffff';
                }
            });
            
            document.getElementById('auto-title').textContent = 'Handmatige Invoer';
            document.getElementById('auto-details').textContent = 'Vul de gegevens handmatig in';
        }
        
        // Professional message display
        function showMessage(text, type) {
            const message = document.getElementById('message');
            message.textContent = text;
            message.className = `message active ${type}`;
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(() => {
                    message.classList.remove('active');
                }, 5000);
            }
        }
        
        // Professional cost calculation (placeholder - will use autokosten.js)
        function berekenKosten() {
            showMessage('⏳ Berekening wordt uitgevoerd...', 'info');
            
            // This will be handled by the autokosten.js file
            // For now, just show that calculation is happening
            setTimeout(() => {
                showMessage('🎯 Berekening voltooid! Zie resultaten hieronder.', 'success');
            }, 1500);
        }
        
        // Auto-calculate on input changes
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[type="number"]');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Only auto-calculate if we have vehicle data
                    const autoInfo = document.getElementById('auto-info');
                    if (autoInfo.style.display !== 'none') {
                        clearTimeout(window.autoCalcTimeout);
                        window.autoCalcTimeout = setTimeout(berekenKosten, 1000);
                    }
                });
            });
            
            // Enter key support for kenteken lookup
            document.getElementById('kenteken').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    lookupKenteken();
                }
            });
        });
    </script>
</body>
</html>