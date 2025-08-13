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
                    'inrichting' => $vehicle['inrichting'] ?? '',
                    'aantal_zitplaatsen' => $vehicle['aantal_zitplaatsen'] ?? '',
                    'aantal_staanplaatsen' => $vehicle['aantal_staanplaatsen'] ?? '',
                    'co2_uitstoot_gecombineerd' => $vehicle['co2_uitstoot_gecombineerd'] ?? '',
                    'europese_voertuigcategorie' => $vehicle['europese_voertuigcategorie'] ?? ''
                ]
            ]);
            exit;
        }
    }
    
    echo json_encode(['error' => 'Kenteken niet gevonden in RDW database']);
    exit;
}

// Bereken kosten (AJAX)
if (isset($_POST['action']) && $_POST['action'] === 'calculate') {
    header('Content-Type: application/json');
    
    // Get input values
    $km_per_maand = floatval($_POST['km_per_maand'] ?? 1500);
    $bruto_salaris = floatval($_POST['bruto_salaris'] ?? 50000);
    $cataloguswaarde = floatval($_POST['cataloguswaarde'] ?? 30000);
    $dagwaarde = floatval($_POST['dagwaarde'] ?? 15000);
    $bijtelling_percentage = floatval($_POST['bijtelling_percentage'] ?? 22);
    $brandstofprijs = floatval($_POST['brandstofprijs'] ?? 1.75);
    $verbruik = floatval($_POST['verbruik'] ?? 15); // km/liter
    $verzekering = floatval($_POST['verzekering'] ?? 80);
    $onderhoud = floatval($_POST['onderhoud'] ?? 100);
    $mrb = floatval($_POST['mrb'] ?? 50);
    $aankoopprijs = floatval($_POST['aankoopprijs'] ?? $cataloguswaarde);
    $restwaarde = floatval($_POST['restwaarde'] ?? $cataloguswaarde * 0.3);
    $afschrijfperiode = intval($_POST['afschrijfperiode'] ?? 5);
    
    // Bepaal belastingschijf (vereenvoudigd)
    $belasting_percentage = $bruto_salaris > 75000 ? 49.5 : 37; // Nederland 2025
    
    // ZAKELIJKE AUTO KOSTEN
    $bijtelling_basis = ($bijtelling_percentage == 35) ? $dagwaarde : $cataloguswaarde;
    $bijtelling_per_maand = ($bijtelling_basis * ($bijtelling_percentage / 100)) / 12;
    $belasting_op_bijtelling = $bijtelling_per_maand * ($belasting_percentage / 100);
    
    $zakelijke_kosten_per_maand = $belasting_op_bijtelling;
    $zakelijke_kosten_per_jaar = $zakelijke_kosten_per_maand * 12;
    
    // PRIVÉ AUTO KOSTEN  
    $afschrijving_per_maand = ($aankoopprijs - $restwaarde) / ($afschrijfperiode * 12);
    $brandstof_per_maand = ($km_per_maand / $verbruik) * $brandstofprijs;
    $apk_per_maand = 50 / 12; // €50 per jaar
    
    $prive_kosten_per_maand = $afschrijving_per_maand + $brandstof_per_maand + 
                              $verzekering + $onderhoud + $mrb + $apk_per_maand;
    $prive_kosten_per_jaar = $prive_kosten_per_maand * 12;
    
    // VERGELIJKING
    $verschil_per_maand = $prive_kosten_per_maand - $zakelijke_kosten_per_maand;
    $verschil_per_jaar = $verschil_per_maand * 12;
    
    $advies = '';
    if ($verschil_per_maand > 50) {
        $advies = 'Auto van de zaak is voordeliger';
    } elseif ($verschil_per_maand < -50) {
        $advies = 'Privé rijden is voordeliger';
    } else {
        $advies = 'Kosten zijn ongeveer gelijk';
    }
    
    echo json_encode([
        'success' => true,
        'zakelijk' => [
            'bijtelling_per_maand' => round($bijtelling_per_maand, 2),
            'belasting_per_maand' => round($belasting_op_bijtelling, 2),
            'totaal_per_maand' => round($zakelijke_kosten_per_maand, 2),
            'totaal_per_jaar' => round($zakelijke_kosten_per_jaar, 2)
        ],
        'prive' => [
            'afschrijving_per_maand' => round($afschrijving_per_maand, 2),
            'brandstof_per_maand' => round($brandstof_per_maand, 2),
            'verzekering_per_maand' => round($verzekering, 2),
            'onderhoud_per_maand' => round($onderhoud, 2),
            'mrb_per_maand' => round($mrb, 2),
            'apk_per_maand' => round($apk_per_maand, 2),
            'totaal_per_maand' => round($prive_kosten_per_maand, 2),
            'totaal_per_jaar' => round($prive_kosten_per_jaar, 2)
        ],
        'vergelijking' => [
            'verschil_per_maand' => round($verschil_per_maand, 2),
            'verschil_per_jaar' => round($verschil_per_jaar, 2),
            'advies' => $advies
        ]
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoKosten Calculator - Piano Man On Tour</title>
    <meta name="description" content="Vergelijk auto van de zaak vs privé rijden. Met RDW kenteken lookup en actuele bijtelling berekening 2025.">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" type="image/x-icon" href="https://www.pianomanontour.nl/favicon.ico">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="icon-car"></i> AutoKosten Calculator PRO</h1>
            <p class="subtitle">Vergelijk auto van de zaak vs privé rijden | Door Piano Man On Tour</p>
        </header>

        <main>
            <div class="form-section">
                <h2>Voertuig Gegevens</h2>
                
                <div class="input-group">
                    <label for="kenteken">Kenteken (automatisch ophalen RDW data)</label>
                    <input type="text" id="kenteken" placeholder="XX-XXX-X" maxlength="8">
                    <button type="button" id="rdw-lookup">Gegevens Ophalen</button>
                </div>

                <div id="vehicle-info" style="display:none;">
                    <h3>Voertuig Informatie</h3>
                    <div class="info-grid">
                        <div><strong>Merk & Model:</strong> <span id="merk-model"></span></div>
                        <div><strong>Bouwjaar:</strong> <span id="bouwjaar"></span></div>
                        <div><strong>Brandstof:</strong> <span id="brandstof"></span></div>
                        <div><strong>Gewicht:</strong> <span id="gewicht"></span> kg</div>
                        <div><strong>Bijtelling:</strong> <span id="bijtelling-info"></span></div>
                    </div>
                </div>

                <h2>Gebruik & Kosten</h2>
                
                <div class="input-row">
                    <div class="input-group">
                        <label for="km_per_maand">Kilometers per maand</label>
                        <input type="number" id="km_per_maand" value="1500" min="0" step="100">
                    </div>
                    
                    <div class="input-group">
                        <label for="bruto_salaris">Bruto salaris per jaar (€)</label>
                        <input type="number" id="bruto_salaris" value="50000" min="0" step="1000">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label for="cataloguswaarde">Cataloguswaarde (€)</label>
                        <input type="number" id="cataloguswaarde" value="30000" min="0" step="1000">
                    </div>
                    
                    <div class="input-group">
                        <label for="dagwaarde">Dagwaarde (€)</label>
                        <input type="number" id="dagwaarde" value="15000" min="0" step="1000">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label for="bijtelling_percentage">Bijtelling %</label>
                        <input type="number" id="bijtelling_percentage" value="22" min="0" max="50" step="1">
                    </div>
                    
                    <div class="input-group">
                        <label for="brandstofprijs">Brandstofprijs (€/liter)</label>
                        <input type="number" id="brandstofprijs" value="1.75" min="0" step="0.01">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label for="verbruik">Verbruik (km/liter)</label>
                        <input type="number" id="verbruik" value="15" min="1" step="0.1">
                    </div>
                    
                    <div class="input-group">
                        <label for="verzekering">Verzekering per maand (€)</label>
                        <input type="number" id="verzekering" value="80" min="0" step="10">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label for="onderhoud">Onderhoud per maand (€)</label>
                        <input type="number" id="onderhoud" value="100" min="0" step="10">
                    </div>
                    
                    <div class="input-group">
                        <label for="mrb">MRB per maand (€)</label>
                        <input type="number" id="mrb" value="50" min="0" step="5">
                    </div>
                </div>

                <div class="input-row">
                    <div class="input-group">
                        <label for="aankoopprijs">Aankoopprijs (€)</label>
                        <input type="number" id="aankoopprijs" value="30000" min="0" step="1000">
                    </div>
                    
                    <div class="input-group">
                        <label for="restwaarde">Restwaarde na 5 jaar (€)</label>
                        <input type="number" id="restwaarde" value="9000" min="0" step="1000">
                    </div>
                </div>

                <button type="button" id="calculate" class="calculate-btn">Bereken Vergelijking</button>
            </div>

            <div id="results" class="results-section" style="display:none;">
                <h2>Kostenvergelijking</h2>
                
                <div class="comparison-grid">
                    <div class="cost-card zakelijk">
                        <h3>🏢 Auto van de Zaak</h3>
                        <div class="cost-breakdown">
                            <div>Bijtelling per maand: €<span id="zakelijk-bijtelling">-</span></div>
                            <div>Belasting per maand: €<span id="zakelijk-belasting">-</span></div>
                            <div class="total">Totaal per maand: €<span id="zakelijk-totaal-maand">-</span></div>
                            <div class="total">Totaal per jaar: €<span id="zakelijk-totaal-jaar">-</span></div>
                        </div>
                    </div>
                    
                    <div class="cost-card prive">
                        <h3>🏠 Privé Auto</h3>
                        <div class="cost-breakdown">
                            <div>Afschrijving: €<span id="prive-afschrijving">-</span></div>
                            <div>Brandstof: €<span id="prive-brandstof">-</span></div>
                            <div>Verzekering: €<span id="prive-verzekering">-</span></div>
                            <div>Onderhoud: €<span id="prive-onderhoud">-</span></div>
                            <div>MRB: €<span id="prive-mrb">-</span></div>
                            <div>APK: €<span id="prive-apk">-</span></div>
                            <div class="total">Totaal per maand: €<span id="prive-totaal-maand">-</span></div>
                            <div class="total">Totaal per jaar: €<span id="prive-totaal-jaar">-</span></div>
                        </div>
                    </div>
                </div>
                
                <div class="advice-section">
                    <h3>💡 Advies</h3>
                    <div id="advice" class="advice-text">-</div>
                    <div class="savings">
                        <div>Verschil per maand: €<span id="verschil-maand">-</span></div>
                        <div>Verschil per jaar: €<span id="verschil-jaar">-</span></div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 <a href="https://www.pianomanontour.nl" target="_blank">Piano Man On Tour</a> | 
            Duelling Pianoshows | Kinderfeestjes | Den Haag, Zoetermeer, Amsterdam</p>
        </footer>
    </div>

    <script src="assets/autokosten.js"></script>
</body>
</html>