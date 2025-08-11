<?php
// Bereken functies voor autokosten

function berekenAutokosten($data) {
    // Input validatie
    $catalogusprijs = floatval($data['catalogusprijs'] ?? 35000);
    $bijtelling_pct = floatval($data['bijtelling_percentage'] ?? 22) / 100;
    $km_jaar = intval($data['km_per_jaar'] ?? 20000);
    $bruto_inkomen = floatval($data['bruto_inkomen'] ?? 50000);
    $km_prive = intval($data['km_prive'] ?? 5000);
    
    // ZAKELIJK (auto van de zaak)
    $bijtelling_bedrag = $catalogusprijs * $bijtelling_pct;
    $belasting_tarief = berekenBelastingSchijf($bruto_inkomen + $bijtelling_bedrag);
    $extra_belasting = $bijtelling_bedrag * $belasting_tarief;
    
    $zakelijk_totaal = $extra_belasting;
    
    // PRIVÉ
    $afschrijving = $catalogusprijs * 0.20; // 20% per jaar
    $brandstof = $km_jaar * 0.08; // €0.08 per km (aanname)
    $onderhoud = $catalogusprijs * 0.04; // 4% van catalogusprijs
    $verzekering = $catalogusprijs * 0.03; // 3% van catalogusprijs
    $wegenbelasting = 600; // gemiddeld
    
    $prive_totaal = $afschrijving + $brandstof + $onderhoud + $verzekering + $wegenbelasting;
    
    // Advies
    $verschil = abs($zakelijk_totaal - $prive_totaal);
    if ($zakelijk_totaal < $prive_totaal) {
        $beste = 'zakelijk';
        $advies_tekst = 'Een auto van de zaak is voordeliger voor jouw situatie!';
    } else {
        $beste = 'prive';
        $advies_tekst = 'Een privé auto is voordeliger voor jouw situatie!';
    }
    
    return [
        'zakelijk' => [
            'bijtelling' => $bijtelling_bedrag,
            'belasting' => $extra_belasting,
            'totaal' => $zakelijk_totaal
        ],
        'prive' => [
            'afschrijving' => $afschrijving,
            'brandstof' => $brandstof,
            'onderhoud' => $onderhoud,
            'verzekering' => $verzekering,
            'wegenbelasting' => $wegenbelasting,
            'totaal' => $prive_totaal
        ],
        'advies' => [
            'beste' => $beste,
            'verschil' => $verschil,
            'tekst' => $advies_tekst
        ]
    ];
}
