<?php
// AutoKosten Calculator PRO - Met RDW Koppeling
// Voor pianomanontour.nl/AutoKosten

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
            
            // Bepaal bijtelling (simplified)
            $bijtelling = 22; // default
            if (strpos(strtolower($brandstof), 'elektr') !== false) {
                $bijtelling = 16; // elektrisch
            }
            
            // Schat dagwaarde (zeer simpel: -20% per jaar vanaf nieuwprijs)
            $leeftijd = date('Y') - intval($bouwjaar);
            $geschatte_nieuwprijs = 30000; // Default schatting
            $dagwaarde = max(5000, $geschatte_nieuwprijs * pow(0.8, $leeftijd));
            
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
                    'mrb_per_maand' => $mrb_per_maand,
                    'eerste_kleur' => $vehicle['eerste_kleur'] ?? '',
                    'aantal_zitplaatsen' => $vehicle['aantal_zitplaatsen'] ?? '',
                    'zuinigheidslabel' => $fuel_data[0]['zuinigheidslabel'] ?? ''
                ]
            ]);
        } else {
            echo json_encode(['error' => 'Kenteken niet gevonden']);
        }
    } else {
        echo json_encode(['error' => 'RDW service niet beschikbaar']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoKosten Calculator PRO - RDW Koppeling</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #764ba2;
            --success: #48bb78;
            --danger: #f56565;
            --warning: #ed8936;
            --dark: #2d3748;
            --light: #f7fafc;
            --border: #e2e8f0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .main-content {
            padding: 30px;
        }
        
        /* Kenteken Input Sectie */
        .kenteken-section {
            background: var(--light);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .kenteken-input-group {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
        }
        
        #kenteken {
            font-size: 2em;
            padding: 15px 25px;
            border: 3px solid var(--primary);
            border-radius: 10px;
            text-transform: uppercase;
            text-align: center;
            width: 250px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        #kenteken:focus {
            outline: none;
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-lookup {
            font-size: 1.2em;
            padding: 18px 35px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s;
        }
        
        .btn-lookup:hover {
            transform: translateY(-2px);
        }
        
        .btn-lookup:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Auto Info Display */
        .auto-info {
            display: none;
            background: white;
            border: 2px solid var(--border);
            border-radius: 15px;
            padding: 25px;
            margin-top: 25px;
        }
        
        .auto-info.active {
            display: block;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .auto-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
            margin-bottom: 20px;
        }
        
        .auto-title {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--dark);
        }
        
        .auto-badge {
            background: var(--success);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        
        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .form-group input,
        .form-group select {
            padding: 10px 15px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-group input[readonly] {
            background: var(--light);
            color: #666;
        }
        
        /* Section Headers */
        .section-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            margin: 30px 0 20px 0;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Collapsible Sections */
        .collapsible {
            border: 2px solid var(--border);
            border-radius: 10px;
            margin: 20px 0;
            overflow: hidden;
        }
        
        .collapsible-header {
            background: var(--light);
            padding: 15px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            color: var(--dark);
            transition: background 0.2s;
        }
        
        .collapsible-header:hover {
            background: #e2e8f0;
        }
        
        .collapsible-content {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
        }
        
        .collapsible.active .collapsible-content {
            padding: 20px;
            max-height: 1000px;
        }
        
        .collapsible-arrow {
            transition: transform 0.3s;
        }
        
        .collapsible.active .collapsible-arrow {
            transform: rotate(180deg);
        }
        
        /* Loading Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .spinner.active {
            display: inline-block;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
            display: none;
        }
        
        .message.active {
            display: block;
        }
        
        .message.error {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #fc8181;
        }
        
        .message.success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        /* Calculate Button */
        .btn-calculate {
            width: 100%;
            padding: 20px;
            background: linear-gradient(135deg, var(--success) 0%, #38a169 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            margin-top: 30px;
            transition: transform 0.2s;
        }
        
        .btn-calculate:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🚗 AutoKosten Calculator PRO</h1>
            <p>Met directe RDW koppeling voor accurate voertuiggegevens</p>
        </header>
        
        <div class="main-content">
            <!-- Kenteken Lookup Section -->
            <div class="kenteken-section">
                <h2>Start met je kenteken</h2>
                <p>Vul je kenteken in voor automatische voertuiggegevens</p>
                
                <div class="kenteken-input-group">
                    <input type="text" 
                           id="kenteken" 
                           placeholder="AB-123-C" 
                           maxlength="8"
                           pattern="[A-Za-z0-9\-]+"
                           title="Vul een geldig Nederlands kenteken in">
                    <button class="btn-lookup" onclick="lookupKenteken()">
                        <span id="lookup-text">🔍 Zoek Auto</span>
                        <span class="spinner" id="lookup-spinner"></span>
                    </button>
                </div>
                
                <div class="message" id="message"></div>
                
                <!-- Auto Info Display -->
                <div class="auto-info" id="auto-info">
                    <div class="auto-header">
                        <div class="auto-title" id="auto-title">-</div>
                        <div class="auto-badge" id="auto-badge">-</div>
                    </div>
                    
                    <!-- Basisgegevens -->
                    <div class="section-header">
                        📋 Basisgegevens
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>🚗 Merk & Model</label>
                            <input type="text" id="merk_model" readonly>
                        </div>
                        <div class="form-group">
                            <label>📅 Bouwjaar</label>
                            <input type="text" id="bouwjaar" readonly>
                        </div>
                        <div class="form-group">
                            <label>⛽ Brandstof</label>
                            <input type="text" id="brandstof" readonly>
                        </div>
                        <div class="form-group">
                            <label>⚖️ Gewicht (kg)</label>
                            <input type="number" id="gewicht" readonly>
                        </div>
                        <div class="form-group">
                            <label>💰 Dagwaarde (€)</label>
                            <input type="number" id="dagwaarde" placeholder="Geschatte waarde">
                        </div>
                        <div class="form-group">
                            <label>🏷️ Cataloguswaarde (€)</label>
                            <input type="number" id="cataloguswaarde" placeholder="Originele prijs">
                        </div>
                    </div>
                    
                    <!-- Kosteninstellingen -->
                    <div class="section-header">
                        💶 Kosteninstellingen
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>📊 Bijtelling %</label>
                            <select id="bijtelling_percentage">
                                <option value="4">4% (Elektrisch)</option>
                                <option value="16">16% (PHEV/Zuinig)</option>
                                <option value="22" selected>22% (Normaal)</option>
                                <option value="35">35% (Youngtimer)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>💸 MRB per maand (€)</label>
                            <input type="number" id="mrb_per_maand" placeholder="Wegenbelasting">
                        </div>
                        <div class="form-group">
                            <label>🚦 Km per maand</label>
                            <input type="number" id="km_per_maand" value="2000">
                        </div>
                        <div class="form-group">
                            <label>⛽ Verbruik (l/100km)</label>
                            <input type="number" id="verbruik" step="0.1" value="7.0">
                        </div>
                        <div class="form-group">
                            <label>💶 Brandstofprijs (€/l)</label>
                            <input type="number" id="brandstofprijs" step="0.01" value="1.95">
                        </div>
                        <div class="form-group">
                            <label>🛡️ Verzekering p/m (€)</label>
                            <input type="number" id="verzekering" value="75">
                        </div>
                    </div>
                    
                    <!-- Geavanceerd (Collapsible) -->
                    <div class="collapsible">
                        <div class="collapsible-header" onclick="toggleCollapsible(this.parentElement)">
                            <span>⚙️ Geavanceerde instellingen</span>
                            <span class="collapsible-arrow">▼</span>
                        </div>
                        <div class="collapsible-content">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>💵 Aankoopprijs (€)</label>
                                    <input type="number" id="aankoopprijs" placeholder="Wat heb je betaald?">
                                </div>
                                <div class="form-group">
                                    <label>📉 Restwaarde (€)</label>
                                    <input type="number" id="restwaarde" placeholder="Verwachte waarde na gebruik">
                                </div>
                                <div class="form-group">
                                    <label>📅 Afschrijving (jaren)</label>
                                    <input type="number" id="afschrijving_jaren" value="5">
                                </div>
                                <div class="form-group">
                                    <label>👤 Gebruikstype</label>
                                    <select id="gebruikstype">
                                        <option value="zakelijk">100% Zakelijk</option>
                                        <option value="prive">100% Privé</option>
                                        <option value="beide" selected>Zakelijk + Privé</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button class="btn-calculate" onclick="berekenKosten()">
                        💰 Bereken Autokosten
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Format kenteken met streepjes
        document.getElementById('kenteken').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Nederlandse kenteken formatting (simplified)
            if (value.length >= 4) {
                let formatted = '';
                // Probeer XX-XX-XX format
                if (value.length <= 6) {
                    formatted = value.slice(0,2) + '-' + value.slice(2,4) + '-' + value.slice(4,6);
                } else {
                    // Probeer XX-XXX-X format
                    formatted = value.slice(0,2) + '-' + value.slice(2,5) + '-' + value.slice(5,6);
                }
                
                // Alleen updaten als het anders is om cursor positie te behouden
                if (formatted !== e.target.value) {
                    e.target.value = formatted;
                }
            }
        });
        
        // Toggle collapsible sections
        function toggleCollapsible(element) {
            element.classList.toggle('active');
        }
        
        // RDW Lookup functie
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
                message.textContent = 'Vul een geldig kenteken in (minimaal 6 tekens)';
                message.classList.add('active', 'error');
                return;
            }
            
            // Show loading
            spinner.classList.add('active');
            lookupText.style.display = 'none';
            
            try {
                const response = await fetch(`?action=rdw_lookup&kenteken=${kenteken}`);
                const data = await response.json();
                
                if (data.success) {
                    // Vul de velden
                    document.getElementById('auto-title').textContent = 
                        `${data.data.merk} ${data.data.handelsbenaming}`;
                    document.getElementById('auto-badge').textContent = 
                        `${data.data.brandstof} - ${data.data.bouwjaar}`;
                    
                    document.getElementById('merk_model').value = 
                        `${data.data.merk} ${data.data.handelsbenaming}`;
                    document.getElementById('bouwjaar').value = data.data.bouwjaar;
                    document.getElementById('brandstof').value = data.data.brandstof;
                    document.getElementById('gewicht').value = data.data.gewicht;
                    document.getElementById('dagwaarde').value = data.data.dagwaarde;
                    document.getElementById('bijtelling_percentage').value = data.data.bijtelling_percentage;
                    document.getElementById('mrb_per_maand').value = data.data.mrb_per_maand;
                    
                    // Toon auto info
                    autoInfo.classList.add('active');
                    
                    message.textContent = '✅ Voertuiggegevens succesvol opgehaald!';
                    message.classList.add('active', 'success');
                } else {
                    message.textContent = `❌ ${data.error || 'Kenteken niet gevonden'}`;
                    message.classList.add('active', 'error');
                }
            } catch (error) {
                message.textContent = '❌ Er ging iets mis. Probeer het opnieuw.';
                message.classList.add('active', 'error');
                console.error('RDW Lookup error:', error);
            } finally {
                // Hide loading
                spinner.classList.remove('active');
                lookupText.style.display = 'inline';
            }
        }
        
        // Bereken kosten functie
        function berekenKosten() {
            // TODO: Implementeer berekening
            alert('Berekening komt in de volgende update!');
        }
        
        // Test met voorbeeldkenteken bij laden
        window.addEventListener('load', function() {
            // Uncomment om te testen:
            // document.getElementById('kenteken').value = 'GB-320-B';
        });
    </script>
</body>
</html>
