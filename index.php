<?php
// AutoKosten Calculator PRO - Met RDW Koppeling
// Voor pianomanontour.nl/AutoKosten
<<<<<<< HEAD
// Professional Business Version 2.0
=======
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

<<<<<<< HEAD
// Include business logic
require_once 'includes/bijtelling_database.php';

=======
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
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
            
<<<<<<< HEAD
            // Gebruik de professionele bijtelling database
            $bijtelling_info = BijtellingsDatabase::getBijtelling($bouwjaar, $brandstof, 30000, 15000);
            $bijtelling = $bijtelling_info['percentage'];
            
            // Schat dagwaarde (verbeterde logica)
            $leeftijd = date('Y') - intval($bouwjaar);
            $geschatte_nieuwprijs = 30000; // Default schatting
            $dagwaarde = max(5000, $geschatte_nieuwprijs * pow(0.85, $leeftijd));
=======
            // Bepaal bijtelling (simplified)
            $bijtelling = 22; // default
            if (strpos(strtolower($brandstof), 'elektr') !== false) {
                $bijtelling = 16; // elektrisch
            }
            
            // Schat dagwaarde (zeer simpel: -20% per jaar vanaf nieuwprijs)
            $leeftijd = date('Y') - intval($bouwjaar);
            $geschatte_nieuwprijs = 30000; // Default schatting
            $dagwaarde = max(5000, $geschatte_nieuwprijs * pow(0.8, $leeftijd));
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
            
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
<<<<<<< HEAD
                    'bijtelling_basis' => $bijtelling_info['basis'],
                    'bijtelling_reden' => $bijtelling_info['reden'],
=======
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
                    'mrb_per_maand' => $mrb_per_maand,
                    'eerste_kleur' => $vehicle['eerste_kleur'] ?? '',
                    'aantal_zitplaatsen' => $vehicle['aantal_zitplaatsen'] ?? '',
                    'zuinigheidslabel' => $fuel_data[0]['zuinigheidslabel'] ?? ''
                ]
            ]);
        } else {
<<<<<<< HEAD
            echo json_encode(['error' => 'Kenteken niet gevonden in RDW database']);
        }
    } else {
        echo json_encode(['error' => 'RDW service niet beschikbaar, probeer het later opnieuw']);
=======
            echo json_encode(['error' => 'Kenteken niet gevonden']);
        }
    } else {
        echo json_encode(['error' => 'RDW service niet beschikbaar']);
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
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
=======
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
        
        @media (max-width: 768px) {
            .kenteken-input-group {
                flex-direction: column;
            }
            
            #kenteken {
                width: 100%;
                max-width: 300px;
            }
            
            .auto-header {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
            }
            
            .btn-calculate, .btn-lookup, button {
                display: none;
            }
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
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
                    </button>
                </div>
                
                <div class="message" id="message"></div>
<<<<<<< HEAD
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
                
=======
                
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
            
            <!-- Results will be inserted here -->
            <div id="calculation-results"></div>
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
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
                if (formatted !== e.target.value) {
                    e.target.value = formatted;
                }
            }
        });
        
<<<<<<< HEAD
        // Professional RDW Lookup function
=======
        // Toggle collapsible sections
        function toggleCollapsible(element) {
            element.classList.toggle('active');
        }
        
        // RDW Lookup functie
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
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
<<<<<<< HEAD
                showMessage('Vul een geldig kenteken in (minimaal 6 tekens)', 'error');
                return;
            }
            
            // Show loading state
=======
                message.textContent = 'Vul een geldig kenteken in (minimaal 6 tekens)';
                message.classList.add('active', 'error');
                return;
            }
            
            // Show loading
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
            spinner.classList.add('active');
            lookupText.style.display = 'none';
            
            try {
                const response = await fetch(`?action=rdw_lookup&kenteken=${kenteken}`);
                const data = await response.json();
                
                if (data.success) {
<<<<<<< HEAD
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
=======
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
>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
                spinner.classList.remove('active');
                lookupText.style.display = 'inline';
            }
        }
        
<<<<<<< HEAD
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
=======
        // Bereken kosten functie - NIEUWE WERKENDE VERSIE
        function berekenKosten() {
            // Verzamel alle form data
            const dagwaarde = parseFloat(document.getElementById('dagwaarde').value) || 15000;
            const cataloguswaarde = parseFloat(document.getElementById('cataloguswaarde').value) || 30000;
            const bijtelling_pct = parseFloat(document.getElementById('bijtelling_percentage').value) || 22;
            const km_per_maand = parseInt(document.getElementById('km_per_maand').value) || 2000;
            const verbruik = parseFloat(document.getElementById('verbruik').value) || 7.0;
            const brandstofprijs = parseFloat(document.getElementById('brandstofprijs').value) || 1.95;
            const verzekering = parseFloat(document.getElementById('verzekering').value) || 75;
            const mrb = parseFloat(document.getElementById('mrb_per_maand').value) || 50;
            const bouwjaar = parseInt(document.getElementById('bouwjaar').value) || 2020;
            
            // Extra waarden
            const aankoopprijs = parseFloat(document.getElementById('aankoopprijs').value) || dagwaarde;
            const restwaarde = parseFloat(document.getElementById('restwaarde').value) || dagwaarde * 0.3;
            const afschrijving_jaren = parseInt(document.getElementById('afschrijving_jaren').value) || 5;
            
            // Constanten
            const inkomsten_belasting = 37; // 37% belastingtarief
            const onderhoud_per_maand = 100; // Geschatte onderhoudskosten
            
            // Check of het een youngtimer is
            const leeftijd = new Date().getFullYear() - bouwjaar;
            const isYoungtimer = leeftijd >= 15 && leeftijd <= 30;
            
            // === ZAKELIJK BEREKENING ===
            const bijtelling_basis = isYoungtimer ? dagwaarde : cataloguswaarde;
            const bijtelling_percentage = isYoungtimer ? 35 : bijtelling_pct;
            const bijtelling_jaar = bijtelling_basis * (bijtelling_percentage / 100);
            const bijtelling_maand = bijtelling_jaar / 12;
            const extra_belasting_maand = bijtelling_maand * (inkomsten_belasting / 100);
            const zakelijk_totaal_maand = extra_belasting_maand;
            const zakelijk_totaal_jaar = zakelijk_totaal_maand * 12;
            
            // === PRIVÉ BEREKENING ===
            const afschrijving_maand = (aankoopprijs - restwaarde) / (afschrijving_jaren * 12);
            const brandstof_maand = (km_per_maand / 100) * verbruik * brandstofprijs;
            const apk_maand = leeftijd > 3 ? (50 / 12) : 0;
            
            const prive_totaal_maand = afschrijving_maand + brandstof_maand + verzekering + 
                                        onderhoud_per_maand + mrb + apk_maand;
            const prive_totaal_jaar = prive_totaal_maand * 12;
            
            // === VERGELIJKING ===
            const beste_optie = zakelijk_totaal_maand < prive_totaal_maand ? 'zakelijk' : 'prive';
            const verschil_maand = Math.abs(zakelijk_totaal_maand - prive_totaal_maand);
            const verschil_jaar = verschil_maand * 12;
            const verschil_percentage = (verschil_jaar / Math.max(zakelijk_totaal_jaar, prive_totaal_jaar)) * 100;
            
            // === TOON RESULTATEN ===
            let resultsHTML = `
                <div class="results-container" style="
                    background: white;
                    border-radius: 15px;
                    padding: 30px;
                    margin-top: 30px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                ">
                    <h2 style="color: #667eea; margin-bottom: 30px; text-align: center;">
                        📊 Berekening Resultaat
                    </h2>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                        <!-- Zakelijk Card -->
                        <div style="
                            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
                            border: 2px solid #667eea;
                            border-radius: 10px;
                            padding: 20px;
                        ">
                            <h3 style="color: #667eea; margin-bottom: 15px;">🏢 Auto van de Zaak</h3>
                            <div style="font-size: 28px; font-weight: bold; color: #333; margin-bottom: 20px;">
                                €${formatNumber(zakelijk_totaal_maand)}<span style="font-size: 16px; font-weight: normal;">/maand</span>
                            </div>
                            <div style="border-top: 1px solid #e0e0e0; padding-top: 15px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span>Bijtelling (${bijtelling_percentage}%):</span>
                                    <strong>€${formatNumber(bijtelling_maand)}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span>Extra belasting (${inkomsten_belasting}%):</span>
                                    <strong>€${formatNumber(extra_belasting_maand)}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 10px; border-top: 1px solid #667eea;">
                                    <span>Per jaar:</span>
                                    <strong style="color: #667eea;">€${formatNumber(zakelijk_totaal_jaar)}</strong>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Privé Card -->
                        <div style="
                            background: linear-gradient(135deg, #764ba215 0%, #667eea15 100%);
                            border: 2px solid #764ba2;
                            border-radius: 10px;
                            padding: 20px;
                        ">
                            <h3 style="color: #764ba2; margin-bottom: 15px;">🚗 Privé Auto</h3>
                            <div style="font-size: 28px; font-weight: bold; color: #333; margin-bottom: 20px;">
                                €${formatNumber(prive_totaal_maand)}<span style="font-size: 16px; font-weight: normal;">/maand</span>
                            </div>
                            <div style="border-top: 1px solid #e0e0e0; padding-top: 15px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span>Afschrijving:</span>
                                    <strong>€${formatNumber(afschrijving_maand)}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span>Brandstof:</span>
                                    <strong>€${formatNumber(brandstof_maand)}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span>Verzekering:</span>
                                    <strong>€${formatNumber(verzekering)}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span>Onderhoud:</span>
                                    <strong>€${formatNumber(onderhoud_per_maand)}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span>MRB:</span>
                                    <strong>€${formatNumber(mrb)}</strong>
                                </div>
                                ${apk_maand > 0 ? `
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <span>APK:</span>
                                    <strong>€${formatNumber(apk_maand)}</strong>
                                </div>
                                ` : ''}
                                <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 10px; border-top: 1px solid #764ba2;">
                                    <span>Per jaar:</span>
                                    <strong style="color: #764ba2;">€${formatNumber(prive_totaal_jaar)}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Advies -->
                    <div style="
                        background: ${beste_optie === 'zakelijk' ? 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)' : 'linear-gradient(135deg, #ed8936 0%, #dd6b20 100%)'};
                        color: white;
                        border-radius: 10px;
                        padding: 25px;
                        text-align: center;
                    ">
                        <h3 style="margin-bottom: 15px; font-size: 24px;">
                            💡 Advies
                        </h3>
                        <p style="font-size: 20px; margin-bottom: 10px;">
                            <strong>${beste_optie === 'zakelijk' ? 'Auto van de zaak' : 'Privé auto'}</strong> is voordeliger!
                        </p>
                        <p style="font-size: 18px; opacity: 0.95;">
                            Je bespaart <strong>€${formatNumber(verschil_jaar)}</strong> per jaar 
                            (${verschil_percentage.toFixed(1)}% goedkoper)
                        </p>
                        <p style="margin-top: 15px; opacity: 0.9;">
                            Dat is €${formatNumber(verschil_maand)} per maand in je portemonnee!
                        </p>
                        ${isYoungtimer ? `
                        <p style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.3); opacity: 0.9;">
                            ⚠️ Let op: Voor deze youngtimer (${leeftijd} jaar oud) geldt ${bijtelling_percentage}% bijtelling over de dagwaarde.
                        </p>
                        ` : ''}
                    </div>
                    
                    <!-- Extra informatie -->
                    <div style="
                        margin-top: 30px;
                        padding: 20px;
                        background: #f8f9fa;
                        border-radius: 10px;
                        border-left: 4px solid #667eea;
                    ">
                        <h4 style="margin-bottom: 15px; color: #333;">📌 Extra informatie</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 10px;">
                                ✓ Berekening is gebaseerd op <strong>${km_per_maand * 12}</strong> km per jaar
                            </li>
                            <li style="margin-bottom: 10px;">
                                ✓ Brandstofverbruik: <strong>${verbruik}</strong> per 100km à €${brandstofprijs}
                            </li>
                            <li style="margin-bottom: 10px;">
                                ✓ ${isYoungtimer ? 'Youngtimer regeling toegepast' : `Normale bijtelling van ${bijtelling_pct}%`}
                            </li>
                            <li style="margin-bottom: 10px;">
                                ✓ Belastingtarief: ${inkomsten_belasting}% (kan afwijken per inkomen)
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Bewaar knop -->
                    <div style="text-align: center; margin-top: 30px;">
                        <button onclick="saveCalculation()" style="
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border: none;
                            padding: 15px 40px;
                            border-radius: 8px;
                            font-size: 16px;
                            font-weight: bold;
                            cursor: pointer;
                            margin-right: 10px;
                        ">
                            💾 Bewaar Berekening
                        </button>
                        <button onclick="window.print()" style="
                            background: white;
                            color: #667eea;
                            border: 2px solid #667eea;
                            padding: 15px 40px;
                            border-radius: 8px;
                            font-size: 16px;
                            font-weight: bold;
                            cursor: pointer;
                        ">
                            🖨️ Print Resultaat
                        </button>
                    </div>
                </div>
            `;
            
            // Voeg resultaten toe aan de pagina
            let resultsDiv = document.getElementById('calculation-results');
            if (!resultsDiv) {
                resultsDiv = document.createElement('div');
                resultsDiv.id = 'calculation-results';
                document.querySelector('.main-content').appendChild(resultsDiv);
            }
            resultsDiv.innerHTML = resultsHTML;
            
            // Scroll naar resultaten
            resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Bewaar in localStorage
            const calculationData = {
                kenteken: document.getElementById('kenteken').value,
                merk_model: document.getElementById('merk_model').value,
                timestamp: new Date().toISOString(),
                zakelijk: {
                    maand: zakelijk_totaal_maand,
                    jaar: zakelijk_totaal_jaar
                },
                prive: {
                    maand: prive_totaal_maand,
                    jaar: prive_totaal_jaar
                },
                advies: beste_optie,
                besparing: verschil_jaar
            };
            
            try {
                localStorage.setItem('laatsteBerekening', JSON.stringify(calculationData));
            } catch(e) {
                console.log('Kon niet opslaan in localStorage');
            }
        }
        
        // Helper functie voor nummer formatting
        function formatNumber(num) {
            return new Intl.NumberFormat('nl-NL', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        }
        
        // Functie om berekening op te slaan
        function saveCalculation() {
            const data = localStorage.getItem('laatsteBerekening');
            if (data) {
                const blob = new Blob([data], {type: 'application/json'});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `autokosten_${new Date().toISOString().split('T')[0]}.json`;
                a.click();
                
                // Toon bevestiging
                alert('✅ Berekening opgeslagen! Check je Downloads folder.');
            }
        }
    </script>
</body>
</html>

>>>>>>> 8df5887b2437a8fa0c5f90c05bdc4a22048257cd
