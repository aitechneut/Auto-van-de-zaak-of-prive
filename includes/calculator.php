<?php
// Geavanceerde AutoKosten Calculator Engine
// Met support voor RDW data en uitgebreide berekeningen

/**
 * Hoofdfunctie voor autokosten berekening
 */
function berekenAutokosten($data) {
    // Valideer en parse input data
    $input = validateInput($data);
    
    // Bereken zakelijke kosten
    $zakelijk = berekenZakelijk($input);
    
    // Bereken privé kosten
    $prive = berekenPrive($input);
    
    // Bereken gecombineerd (zakelijk + privé gebruik)
    $gecombineerd = berekenGecombineerd($input, $zakelijk, $prive);
    
    // Maak vergelijking en advies
    $advies = genereerAdvies($zakelijk, $prive, $gecombineerd, $input);
    
    return [
        'input' => $input,
        'zakelijk' => $zakelijk,
        'prive' => $prive,
        'gecombineerd' => $gecombineerd,
        'advies' => $advies,
        'grafiek_data' => genereerGrafiekData($zakelijk, $prive, $gecombineerd)
    ];
}

/**
 * Valideer en parse input data
 */
function validateInput($data) {
    return [
        // Auto gegevens
        'kenteken' => strtoupper(trim($data['kenteken'] ?? '')),
        'merk_model' => $data['merk_model'] ?? 'Onbekend',
        'bouwjaar' => intval($data['bouwjaar'] ?? date('Y')),
        'brandstof' => $data['brandstof'] ?? 'Benzine',
        'gewicht' => intval($data['gewicht'] ?? 1200),
        
        // Waarde
        'dagwaarde' => floatval($data['dagwaarde'] ?? 15000),
        'cataloguswaarde' => floatval($data['cataloguswaarde'] ?? 30000),
        'aankoopprijs' => floatval($data['aankoopprijs'] ?? 0),
        'restwaarde' => floatval($data['restwaarde'] ?? 5000),
        'afschrijving_jaren' => intval($data['afschrijving_jaren'] ?? 5),
        
        // Belasting
        'bijtelling_percentage' => floatval($data['bijtelling_percentage'] ?? 22),
        'inkomstenbelasting_percentage' => floatval($data['inkomstenbelasting_percentage'] ?? 37),
        'mrb_per_maand' => floatval($data['mrb_per_maand'] ?? 50),
        
        // Gebruik
        'km_per_maand' => intval($data['km_per_maand'] ?? 2000),
        'km_prive_per_maand' => intval($data['km_prive_per_maand'] ?? 500),
        'gebruikstype' => $data['gebruikstype'] ?? 'beide',
        
        // Verbruik
        'verbruik' => floatval($data['verbruik'] ?? 7.0), // l/100km of kWh/100km
        'brandstofprijs' => floatval($data['brandstofprijs'] ?? 1.95), // €/l of €/kWh
        
        // Overige kosten
        'verzekering_per_maand' => floatval($data['verzekering_per_maand'] ?? 75),
        'onderhoud_per_maand' => floatval($data['onderhoud_per_maand'] ?? 100),
        
        // Extra voor youngtimers
        'is_youngtimer' => isYoungtimer($data['bouwjaar'] ?? date('Y'))
    ];
}

/**
 * Check of auto een youngtimer is (15-30 jaar oud)
 */
function isYoungtimer($bouwjaar) {
    $leeftijd = date('Y') - intval($bouwjaar);
    return $leeftijd >= 15 && $leeftijd <= 30;
}

/**
 * Bereken zakelijke autokosten
 */
function berekenZakelijk($input) {
    $maandelijks = [];
    $jaarlijks = [];
    
    // Bepaal bijtelling basis (dagwaarde voor youngtimers, cataloguswaarde voor nieuwer)
    if ($input['is_youngtimer']) {
        $bijtelling_basis = $input['dagwaarde'];
        $bijtelling_percentage = 35; // Youngtimers altijd 35%
    } else {
        $bijtelling_basis = $input['cataloguswaarde'];
        $bijtelling_percentage = $input['bijtelling_percentage'];
    }
    
    // Bijtelling berekening
    $bijtelling_per_jaar = $bijtelling_basis * ($bijtelling_percentage / 100);
    $bijtelling_per_maand = $bijtelling_per_jaar / 12;
    
    // Extra belasting door bijtelling
    $extra_belasting_per_jaar = $bijtelling_per_jaar * ($input['inkomstenbelasting_percentage'] / 100);
    $extra_belasting_per_maand = $extra_belasting_per_jaar / 12;
    
    // Vaste kosten (betaald door bedrijf, geen directe kosten voor gebruiker)
    $maandelijks['bijtelling'] = round($bijtelling_per_maand, 2);
    $maandelijks['extra_belasting'] = round($extra_belasting_per_maand, 2);
    
    // Totaal voor gebruiker (alleen de extra belasting)
    $maandelijks['totaal'] = round($extra_belasting_per_maand, 2);
    
    // Jaarlijkse kosten
    foreach ($maandelijks as $key => $value) {
        $jaarlijks[$key] = round($value * 12, 2);
    }
    
    // Extra info voor zakelijk
    $extra_info = [
        'bijtelling_basis' => $bijtelling_basis,
        'bijtelling_percentage' => $bijtelling_percentage,
        'fiscaal_voordeel' => round($bijtelling_per_jaar - $extra_belasting_per_jaar, 2),
        'type' => $input['is_youngtimer'] ? 'Youngtimer regeling' : 'Normale bijtelling'
    ];
    
    return [
        'maandelijks' => $maandelijks,
        'jaarlijks' => $jaarlijks,
        'extra_info' => $extra_info
    ];
}

/**
 * Bereken privé autokosten
 */
function berekenPrive($input) {
    $maandelijks = [];
    $jaarlijks = [];
    
    // Afschrijving
    if ($input['aankoopprijs'] > 0) {
        $afschrijving_per_jaar = ($input['aankoopprijs'] - $input['restwaarde']) / $input['afschrijving_jaren'];
        $maandelijks['afschrijving'] = round($afschrijving_per_jaar / 12, 2);
    } else {
        // Schat afschrijving op basis van dagwaarde (20% per jaar)
        $maandelijks['afschrijving'] = round($input['dagwaarde'] * 0.20 / 12, 2);
    }
    
    // MRB (wegenbelasting)
    $maandelijks['mrb'] = round($input['mrb_per_maand'], 2);
    
    // Verzekering
    $maandelijks['verzekering'] = round($input['verzekering_per_maand'], 2);
    
    // Brandstof
    $brandstof_per_maand = ($input['km_per_maand'] / 100) * $input['verbruik'] * $input['brandstofprijs'];
    $maandelijks['brandstof'] = round($brandstof_per_maand, 2);
    
    // Onderhoud
    $maandelijks['onderhoud'] = round($input['onderhoud_per_maand'], 2);
    
    // APK (voor auto's ouder dan 3 jaar)
    $auto_leeftijd = date('Y') - $input['bouwjaar'];
    if ($auto_leeftijd > 3) {
        $maandelijks['apk'] = round(50 / 12, 2); // €50 per jaar
    } else {
        $maandelijks['apk'] = 0;
    }
    
    // Totaal
    $maandelijks['totaal'] = round(array_sum($maandelijks), 2);
    
    // Jaarlijkse kosten
    foreach ($maandelijks as $key => $value) {
        $jaarlijks[$key] = round($value * 12, 2);
    }
    
    // Extra info voor privé
    $extra_info = [
        'kosten_per_km' => round($maandelijks['totaal'] / max(1, $input['km_per_maand']), 4),
        'dagelijkse_kosten' => round($maandelijks['totaal'] / 30, 2),
        'auto_leeftijd' => $auto_leeftijd,
        'apk_vereist' => $auto_leeftijd > 3
    ];
    
    return [
        'maandelijks' => $maandelijks,
        'jaarlijks' => $jaarlijks,
        'extra_info' => $extra_info
    ];
}

/**
 * Bereken gecombineerd gebruik (zakelijk + privé)
 */
function berekenGecombineerd($input, $zakelijk, $prive) {
    $maandelijks = [];
    $jaarlijks = [];
    
    // Bepaal verhouding zakelijk/privé
    $totale_km = max(1, $input['km_per_maand']);
    $prive_km = min($input['km_prive_per_maand'], $totale_km);
    $zakelijke_km = $totale_km - $prive_km;
    
    $prive_percentage = $prive_km / $totale_km;
    $zakelijk_percentage = $zakelijke_km / $totale_km;
    
    // Voor gecombineerd gebruik
    if ($input['gebruikstype'] === 'beide') {
        // Bijtelling (volledig)
        $maandelijks['bijtelling_belasting'] = $zakelijk['maandelijks']['extra_belasting'];
        
        // Privé kilometers vergoeding (19 cent per km voor 2024)
        $km_vergoeding_per_km = 0.23; // 2024 tarief
        $maandelijks['km_vergoeding'] = round($zakelijke_km * $km_vergoeding_per_km, 2);
        
        // Netto kosten
        $maandelijks['netto_kosten'] = round(
            $maandelijks['bijtelling_belasting'] - $maandelijks['km_vergoeding'], 
            2
        );
        
        // Als er meer dan 500 privé km per jaar is, voeg extra kosten toe
        if ($prive_km * 12 > 500) {
            $maandelijks['eigen_bijdrage'] = 0; // Kan door werkgever bepaald worden
        }
        
        $maandelijks['totaal'] = $maandelijks['netto_kosten'];
    } else {
        // 100% zakelijk of 100% privé
        $maandelijks = $input['gebruikstype'] === 'zakelijk' 
            ? $zakelijk['maandelijks'] 
            : $prive['maandelijks'];
    }
    
    // Jaarlijkse kosten
    foreach ($maandelijks as $key => $value) {
        $jaarlijks[$key] = round($value * 12, 2);
    }
    
    // Extra info
    $extra_info = [
        'zakelijke_km_per_maand' => $zakelijke_km,
        'prive_km_per_maand' => $prive_km,
        'zakelijk_percentage' => round($zakelijk_percentage * 100, 1),
        'prive_percentage' => round($prive_percentage * 100, 1),
        'km_vergoeding_tarief' => $km_vergoeding_per_km ?? 0
    ];
    
    return [
        'maandelijks' => $maandelijks,
        'jaarlijks' => $jaarlijks,
        'extra_info' => $extra_info
    ];
}

/**
 * Genereer advies op basis van berekeningen
 */
function genereerAdvies($zakelijk, $prive, $gecombineerd, $input) {
    $zakelijk_totaal = $zakelijk['jaarlijks']['totaal'];
    $prive_totaal = $prive['jaarlijks']['totaal'];
    
    $verschil = abs($zakelijk_totaal - $prive_totaal);
    $verschil_percentage = round(($verschil / max($zakelijk_totaal, $prive_totaal)) * 100, 1);
    
    // Bepaal beste optie
    if ($zakelijk_totaal < $prive_totaal) {
        $beste_optie = 'zakelijk';
        $besparing = $prive_totaal - $zakelijk_totaal;
        $advies_tekst = "Een auto van de zaak is €" . number_format($besparing, 0, ',', '.') . 
                       " per jaar voordeliger! Dit scheelt " . $verschil_percentage . "%.";
    } else {
        $beste_optie = 'prive';
        $besparing = $zakelijk_totaal - $prive_totaal;
        $advies_tekst = "Een privé auto is €" . number_format($besparing, 0, ',', '.') . 
                       " per jaar voordeliger! Dit scheelt " . $verschil_percentage . "%.";
    }
    
    // Extra overwegingen
    $overwegingen = [];
    
    // Youngtimer advies
    if ($input['is_youngtimer']) {
        $overwegingen[] = "Let op: Voor deze youngtimer geldt 35% bijtelling over de dagwaarde.";
    }
    
    // Elektrisch advies
    if (stripos($input['brandstof'], 'elektr') !== false) {
        $overwegingen[] = "Elektrisch rijden heeft lagere bijtelling en onderhoudskosten.";
    }
    
    // Veel kilometers advies
    if ($input['km_per_maand'] > 2500) {
        $overwegingen[] = "Bij veel kilometers (>" . number_format($input['km_per_maand'] * 12, 0, ',', '.') . 
                         "/jaar) is zakelijk vaak voordeliger.";
    }
    
    // Weinig privé kilometers
    if ($input['km_prive_per_maand'] * 12 <= 500) {
        $overwegingen[] = "Met <500 privé km/jaar kun je bijtelling vermijden met een sluitende rittenregistratie.";
    }
    
    return [
        'beste_optie' => $beste_optie,
        'verschil' => $verschil,
        'verschil_percentage' => $verschil_percentage,
        'besparing_per_jaar' => $besparing,
        'besparing_per_maand' => round($besparing / 12, 2),
        'advies_tekst' => $advies_tekst,
        'overwegingen' => $overwegingen,
        'break_even_km' => berekenBreakEvenKm($zakelijk, $prive, $input)
    ];
}

/**
 * Bereken break-even kilometrage
 */
function berekenBreakEvenKm($zakelijk, $prive, $input) {
    // Vaste kosten zakelijk (bijtelling belasting)
    $vast_zakelijk = $zakelijk['maandelijks']['extra_belasting'];
    
    // Variabele kosten per km voor privé
    $kosten_per_km_prive = $prive['extra_info']['kosten_per_km'];
    
    // Break-even = vast_zakelijk / kosten_per_km_prive
    $break_even = round($vast_zakelijk / max(0.01, $kosten_per_km_prive), 0);
    
    return [
        'km_per_maand' => $break_even,
        'km_per_jaar' => $break_even * 12,
        'uitleg' => "Bij " . number_format($break_even * 12, 0, ',', '.') . 
                   " km/jaar zijn zakelijk en privé even duur."
    ];
}

/**
 * Genereer data voor grafiek
 */
function genereerGrafiekData($zakelijk, $prive, $gecombineerd) {
    return [
        'labels' => [
            'Bijtelling',
            'Afschrijving', 
            'Brandstof',
            'Verzekering',
            'Onderhoud',
            'MRB',
            'Overig'
        ],
        'datasets' => [
            [
                'label' => 'Auto van de Zaak',
                'data' => [
                    $zakelijk['maandelijks']['extra_belasting'] ?? 0,
                    0, // Geen afschrijving voor gebruiker
                    0, // Brandstof betaald door bedrijf
                    0, // Verzekering betaald door bedrijf
                    0, // Onderhoud betaald door bedrijf
                    0, // MRB betaald door bedrijf
                    0
                ],
                'backgroundColor' => 'rgba(102, 126, 234, 0.8)'
            ],
            [
                'label' => 'Privé Auto',
                'data' => [
                    0, // Geen bijtelling
                    $prive['maandelijks']['afschrijving'] ?? 0,
                    $prive['maandelijks']['brandstof'] ?? 0,
                    $prive['maandelijks']['verzekering'] ?? 0,
                    $prive['maandelijks']['onderhoud'] ?? 0,
                    $prive['maandelijks']['mrb'] ?? 0,
                    $prive['maandelijks']['apk'] ?? 0
                ],
                'backgroundColor' => 'rgba(118, 75, 162, 0.8)'
            ]
        ],
        'totalen' => [
            'zakelijk' => $zakelijk['maandelijks']['totaal'],
            'prive' => $prive['maandelijks']['totaal'],
            'gecombineerd' => $gecombineerd['maandelijks']['totaal'] ?? 0
        ]
    ];
}

/**
 * Export functie voor PDF/Excel
 */
function exporteerBerekening($resultaat, $format = 'json') {
    switch ($format) {
        case 'csv':
            return genereerCSV($resultaat);
        case 'pdf':
            return genereerPDF($resultaat);
        default:
            return json_encode($resultaat, JSON_PRETTY_PRINT);
    }
}

/**
 * Genereer CSV export
 */
function genereerCSV($resultaat) {
    $csv = "AutoKosten Berekening\n";
    $csv .= "Datum," . date('d-m-Y H:i') . "\n\n";
    
    $csv .= "Type,Maandelijks,Jaarlijks\n";
    $csv .= "Zakelijk," . $resultaat['zakelijk']['maandelijks']['totaal'] . "," . 
            $resultaat['zakelijk']['jaarlijks']['totaal'] . "\n";
    $csv .= "Prive," . $resultaat['prive']['maandelijks']['totaal'] . "," . 
            $resultaat['prive']['jaarlijks']['totaal'] . "\n";
    
    return $csv;
}

/**
 * Helper functie voor PDF generatie (placeholder)
 */
function genereerPDF($resultaat) {
    // TODO: Implementeer met TCPDF of vergelijkbare library
    return "PDF export komt in volgende versie";
}
