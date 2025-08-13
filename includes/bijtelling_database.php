<?php
/**
 * Nederlandse Bijtelling Database 2004-2025+
 * Complete database voor AutoKosten Calculator
 * 
 * @author Richard Surie - PianoManOnTour.nl
 * @version 2.0
 * @date 2025-08-14
 */

class BijtellingsDatabase {
    
    /**
     * Complete bijtelling percentages per jaar
     * Gevalideerd via Belastingdienst, RDW en officiële bronnen
     */
    private static $bijtelling_regels = [
        // 2025 - Huidige regels
        2025 => [
            'elektrisch' => [
                'percentage_laag' => 17,
                'drempel' => 30000,
                'percentage_hoog' => 22,
                'volledig_percentage' => ['waterstof', 'zonnecel'] // Uitzonderingen
            ],
            'brandstof' => [
                'benzine' => 22,
                'diesel' => 22,
                'hybride' => 22,
                'phev' => 22,
                'lpg' => 22,
                'cng' => 22
            ],
            'youngtimer' => [
                'enabled' => true,
                'min_leeftijd' => 15,
                'max_leeftijd' => 30,
                'percentage' => 35,
                'basis' => 'dagwaarde'
            ],
            'pre_2017_regel' => 25 // Auto's van voor 2017 behouden 25%
        ],
        
        // 2024
        2024 => [
            'elektrisch' => [
                'percentage_laag' => 16,
                'drempel' => 30000,
                'percentage_hoog' => 22,
                'volledig_percentage' => ['waterstof', 'zonnecel']
            ],
            'brandstof' => [
                'benzine' => 22, 'diesel' => 22, 'hybride' => 22,
                'phev' => 22, 'lpg' => 22, 'cng' => 22
            ],
            'youngtimer' => [
                'enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30,
                'percentage' => 35, 'basis' => 'dagwaarde'
            ],
            'pre_2017_regel' => 25
        ],
        
        // 2023
        2023 => [
            'elektrisch' => [
                'percentage_laag' => 16,
                'drempel' => 30000,
                'percentage_hoog' => 22,
                'volledig_percentage' => ['waterstof', 'zonnecel']
            ],
            'brandstof' => [
                'benzine' => 22, 'diesel' => 22, 'hybride' => 22,
                'phev' => 22, 'lpg' => 22, 'cng' => 22
            ],
            'youngtimer' => [
                'enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30,
                'percentage' => 35, 'basis' => 'dagwaarde'
            ],
            'pre_2017_regel' => 25
        ],
        
        // 2022
        2022 => [
            'elektrisch' => [
                'percentage_laag' => 16,
                'drempel' => 35000,
                'percentage_hoog' => 22,
                'volledig_percentage' => ['waterstof', 'zonnecel']
            ],
            'brandstof' => [
                'benzine' => 22, 'diesel' => 22, 'hybride' => 22,
                'phev' => 22, 'lpg' => 22, 'cng' => 22
            ],
            'youngtimer' => [
                'enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30,
                'percentage' => 35, 'basis' => 'dagwaarde'
            ],
            'pre_2017_regel' => 25
        ],
        
        // 2021
        2021 => [
            'elektrisch' => [
                'percentage_laag' => 12,
                'drempel' => 40000,
                'percentage_hoog' => 22,
                'volledig_percentage' => ['waterstof', 'zonnecel']
            ],
            'brandstof' => [
                'benzine' => 22, 'diesel' => 22, 'hybride' => 22,
                'phev' => 22, 'lpg' => 22, 'cng' => 22
            ],
            'youngtimer' => [
                'enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30,
                'percentage' => 35, 'basis' => 'dagwaarde'
            ],
            'pre_2017_regel' => 25
        ],
        
        // 2020
        2020 => [
            'elektrisch' => [
                'percentage_laag' => 8,
                'drempel' => 45000,
                'percentage_hoog' => 22,
                'volledig_percentage' => ['waterstof', 'zonnecel']
            ],
            'brandstof' => [
                'benzine' => 22, 'diesel' => 22, 'hybride' => 22,
                'phev' => 22, 'lpg' => 22, 'cng' => 22
            ],
            'youngtimer' => [
                'enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30,
                'percentage' => 35, 'basis' => 'dagwaarde'
            ],
            'pre_2017_regel' => 25
        ],
        
        // 2019
        2019 => [
            'elektrisch' => [
                'percentage_laag' => 4,
                'drempel' => 50000,
                'percentage_hoog' => 22,
                'volledig_percentage' => ['waterstof', 'zonnecel']
            ],
            'brandstof' => [
                'benzine' => 22, 'diesel' => 22, 'hybride' => 22,
                'phev' => 22, 'lpg' => 22, 'cng' => 22
            ],
            'youngtimer' => [
                'enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30,
                'percentage' => 35, 'basis' => 'dagwaarde'
            ],
            'pre_2017_regel' => 25
        ],
        
        // 2017-2018
        2018 => [
            'elektrisch' => ['percentage_laag' => 4, 'drempel' => null, 'percentage_hoog' => 4],
            'brandstof' => ['benzine' => 22, 'diesel' => 22, 'hybride' => 22, 'phev' => 22, 'lpg' => 22, 'cng' => 22],
            'youngtimer' => ['enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30, 'percentage' => 35, 'basis' => 'dagwaarde'],
            'pre_2017_regel' => 25
        ],
        2017 => [
            'elektrisch' => ['percentage_laag' => 4, 'drempel' => null, 'percentage_hoog' => 4],
            'brandstof' => ['benzine' => 22, 'diesel' => 22, 'hybride' => 22, 'phev' => 22, 'lpg' => 22, 'cng' => 22],
            'youngtimer' => ['enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30, 'percentage' => 35, 'basis' => 'dagwaarde'],
            'pre_2017_regel' => null // Dit jaar introduceerde de 22% regel
        ],
        
        // 2016 (overgang jaar)
        2016 => [
            'elektrisch' => ['percentage_laag' => 4, 'drempel' => null, 'percentage_hoog' => 4],
            'phev' => ['percentage' => 15, 'co2_max' => 50],
            'brandstof' => ['benzine' => 21, 'diesel' => 21], // CO2 afhankelijk
            'co2_regels' => [
                'laag' => ['percentage' => 21, 'co2_max' => 106],
                'hoog' => ['percentage' => 25, 'co2_min' => 107]
            ],
            'youngtimer' => ['enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30, 'percentage' => 35, 'basis' => 'dagwaarde'],
            'algemeen' => 25
        ],
        
        // 2015
        2015 => [
            'elektrisch' => ['percentage_laag' => 4, 'drempel' => null, 'percentage_hoog' => 4],
            'phev' => ['percentage' => 7, 'co2_max' => 50],
            'co2_regels' => [
                'zeer_laag' => ['percentage' => 14, 'co2_max' => 82],
                'laag' => ['percentage' => 20, 'co2_max' => 110],
                'hoog' => ['percentage' => 25, 'co2_min' => 111]
            ],
            'youngtimer' => ['enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30, 'percentage' => 35, 'basis' => 'dagwaarde'],
            'algemeen' => 25
        ],
        
        // 2014
        2014 => [
            'elektrisch' => ['percentage_laag' => 4, 'drempel' => null, 'percentage_hoog' => 4],
            'phev' => ['percentage' => 7, 'co2_max' => 50],
            'co2_regels' => [
                'zeer_laag_diesel' => ['percentage' => 14, 'co2_max' => 85, 'brandstof' => 'diesel'],
                'zeer_laag_benzine' => ['percentage' => 14, 'co2_max' => 88, 'brandstof' => 'benzine'],
                'laag_diesel' => ['percentage' => 20, 'co2_max' => 111, 'brandstof' => 'diesel'],
                'laag_benzine' => ['percentage' => 20, 'co2_max' => 117, 'brandstof' => 'benzine'],
                'hoog' => ['percentage' => 25, 'co2_min' => 112]
            ],
            'youngtimer' => ['enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30, 'percentage' => 35, 'basis' => 'dagwaarde'],
            'algemeen' => 25
        ],
        
        // 2012-2013 (0% elektrisch periode)
        2013 => [
            'elektrisch' => ['percentage_laag' => 0, 'co2_max' => 50],
            'co2_regels' => [
                'zeer_laag_diesel' => ['percentage' => 14, 'co2_max' => 88, 'brandstof' => 'diesel'],
                'zeer_laag_benzine' => ['percentage' => 14, 'co2_max' => 95, 'brandstof' => 'benzine'],
                'laag_diesel' => ['percentage' => 20, 'co2_max' => 112, 'brandstof' => 'diesel'],
                'laag_benzine' => ['percentage' => 20, 'co2_max' => 124, 'brandstof' => 'benzine'],
                'hoog' => ['percentage' => 25, 'co2_min' => 113]
            ],
            'youngtimer' => ['enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30, 'percentage' => 35, 'basis' => 'dagwaarde'],
            'algemeen' => 25
        ],
        2012 => [
            'elektrisch' => ['percentage_laag' => 0, 'co2_max' => 50, 'start_datum' => '2012-07-01'],
            'co2_regels' => [
                'zeer_laag_diesel' => ['percentage' => 14, 'co2_max' => 95, 'brandstof' => 'diesel'],
                'zeer_laag_benzine' => ['percentage' => 14, 'co2_max' => 110, 'brandstof' => 'benzine'],
                'laag_diesel' => ['percentage' => 20, 'co2_max' => 116, 'brandstof' => 'diesel'],
                'laag_benzine' => ['percentage' => 20, 'co2_max' => 140, 'brandstof' => 'benzine'],
                'hoog' => ['percentage' => 25, 'co2_min' => 117]
            ],
            'youngtimer' => ['enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30, 'percentage' => 35, 'basis' => 'dagwaarde'], // Introduceerde youngtimer regeling
            'algemeen' => 25
        ],
        
        // 2008-2011 (CO2 tijdperk zonder youngtimer)
        2011 => [
            'co2_regels' => [
                'zeer_laag_diesel' => ['percentage' => 14, 'co2_max' => 95, 'brandstof' => 'diesel'],
                'zeer_laag_benzine' => ['percentage' => 14, 'co2_max' => 110, 'brandstof' => 'benzine'],
                'laag_diesel' => ['percentage' => 20, 'co2_max' => 116, 'brandstof' => 'diesel'],
                'laag_benzine' => ['percentage' => 20, 'co2_max' => 140, 'brandstof' => 'benzine'],
                'hoog' => ['percentage' => 25, 'co2_min' => 117]
            ],
            'algemeen' => 25
        ],
        2010 => [
            'co2_regels' => [
                'zeer_laag_diesel' => ['percentage' => 14, 'co2_max' => 95, 'brandstof' => 'diesel'],
                'zeer_laag_benzine' => ['percentage' => 14, 'co2_max' => 110, 'brandstof' => 'benzine'],
                'laag_diesel' => ['percentage' => 20, 'co2_max' => 116, 'brandstof' => 'diesel'],
                'laag_benzine' => ['percentage' => 20, 'co2_max' => 140, 'brandstof' => 'benzine'],
                'hoog' => ['percentage' => 25, 'co2_min' => 117]
            ],
            'algemeen' => 25
        ],
        2009 => [
            'co2_regels' => [
                'laag_diesel' => ['percentage' => 14, 'co2_max' => 95, 'brandstof' => 'diesel'],
                'laag_benzine' => ['percentage' => 14, 'co2_max' => 110, 'brandstof' => 'benzine'],
                'midden_diesel' => ['percentage' => 20, 'co2_max' => 116, 'brandstof' => 'diesel'],
                'midden_benzine' => ['percentage' => 20, 'co2_max' => 140, 'brandstof' => 'benzine'],
                'hoog' => ['percentage' => 25, 'co2_min' => 117]
            ],
            'algemeen' => 25
        ],
        2008 => [
            'co2_regels' => [
                'laag_diesel' => ['percentage' => 14, 'co2_max' => 95, 'brandstof' => 'diesel'],
                'laag_benzine' => ['percentage' => 14, 'co2_max' => 110, 'brandstof' => 'benzine'],
                'hoog' => ['percentage' => 25, 'co2_min' => 96] // Voor diesel, 111 voor benzine
            ],
            'algemeen' => 25
        ],
        
        // 2004-2007 (Vlakke periode)
        2007 => ['algemeen' => 22],
        2006 => ['algemeen' => 22],
        2005 => ['algemeen' => 22],
        2004 => ['algemeen' => 22],
        
        // Toekomst
        2026 => [
            'algemeen' => 22, // Alles wordt 22%
            'youngtimer' => ['enabled' => true, 'min_leeftijd' => 15, 'max_leeftijd' => 30, 'percentage' => 35, 'basis' => 'dagwaarde']
        ]
    ];
    
    /**
     * Bereken bijtelling percentage voor specifieke auto
     */
    public static function berekenBijtelling($bouwjaar, $brandstof, $cataloguswaarde, $dagwaarde = null, $co2_uitstoot = null, $eerste_toelating = null) {
        $resultaat = [
            'bijtelling_percentage' => 22,
            'bijtelling_basis' => $cataloguswaarde,
            'bijtelling_bedrag_jaar' => 0,
            'drempelwaarde' => null,
            'is_youngtimer' => false,
            'is_elektrisch' => false,
            'regels_jaar' => $bouwjaar,
            'uitleg' => '',
            'waarschuwingen' => []
        ];
        
        // Bepaal welk jaar regels om te gebruiken
        $regels_jaar = $eerste_toelating ? 
            intval(substr($eerste_toelating, 0, 4)) : 
            $bouwjaar;
            
        // Check voor youngtimer status
        $auto_leeftijd = date('Y') - $bouwjaar;
        $is_youngtimer = self::isYoungtimer($auto_leeftijd, $regels_jaar);
        
        if ($is_youngtimer) {
            $resultaat['is_youngtimer'] = true;
            $resultaat['bijtelling_percentage'] = 35;
            $resultaat['bijtelling_basis'] = $dagwaarde ?: $cataloguswaarde * 0.3; // Schat dagwaarde
            $resultaat['uitleg'] = "Youngtimer regeling: 35% over dagwaarde (auto is {$auto_leeftijd} jaar oud)";
            $resultaat['bijtelling_bedrag_jaar'] = $resultaat['bijtelling_basis'] * 0.35;
            return $resultaat;
        }
        
        // Check voor pre-2017 regel
        if ($regels_jaar < 2017 && isset(self::$bijtelling_regels[$regels_jaar])) {
            $regels = self::$bijtelling_regels[$regels_jaar];
            if (isset($regels['pre_2017_regel'])) {
                $resultaat['bijtelling_percentage'] = $regels['pre_2017_regel'];
                $resultaat['uitleg'] = "Pre-2017 auto: behoud van {$regels['pre_2017_regel']}% tarief";
                $resultaat['bijtelling_bedrag_jaar'] = $cataloguswaarde * ($regels['pre_2017_regel'] / 100);
                return $resultaat;
            }
        }
        
        // Normaliseer brandstof type
        $brandstof_norm = self::normaliseerBrandstof($brandstof);
        
        // Check voor elektrisch
        if ($brandstof_norm === 'elektrisch') {
            $resultaat['is_elektrisch'] = true;
            return self::berekenElektrischeBijtelling($regels_jaar, $cataloguswaarde, $resultaat);
        }
        
        // Haal regels voor het jaar op
        if (!isset(self::$bijtelling_regels[$regels_jaar])) {
            $regels_jaar = self::vindDichtstbijzijndeJaar($regels_jaar);
        }
        
        $regels = self::$bijtelling_regels[$regels_jaar];
        
        // Voor moderne jaren (2017+)
        if ($regels_jaar >= 2017) {
            $percentage = isset($regels['brandstof'][$brandstof_norm]) ? 
                $regels['brandstof'][$brandstof_norm] : 22;
            
            $resultaat['bijtelling_percentage'] = $percentage;
            $resultaat['uitleg'] = "Standaard bijtelling {$percentage}% voor {$brandstof_norm}";
            $resultaat['bijtelling_bedrag_jaar'] = $cataloguswaarde * ($percentage / 100);
            
            return $resultaat;
        }
        
        // Voor historische jaren met CO2 regels (2008-2016)
        if (isset($regels['co2_regels']) && $co2_uitstoot !== null) {
            return self::berekenCO2Bijtelling($regels, $brandstof_norm, $co2_uitstoot, $cataloguswaarde, $resultaat);
        }
        
        // Fallback naar algemeen tarief
        $percentage = $regels['algemeen'] ?? 22;
        $resultaat['bijtelling_percentage'] = $percentage;
        $resultaat['uitleg'] = "Algemeen tarief {$percentage}% voor jaar {$regels_jaar}";
        $resultaat['bijtelling_bedrag_jaar'] = $cataloguswaarde * ($percentage / 100);
        
        return $resultaat;
    }
    
    /**
     * Check of auto een youngtimer is
     */
    private static function isYoungtimer($auto_leeftijd, $regels_jaar) {
        if ($regels_jaar < 2012) {
            return false; // Youngtimer regeling bestond nog niet
        }
        
        return $auto_leeftijd >= 15 && $auto_leeftijd <= 30;
    }
    
    /**
     * Bereken elektrische bijtelling
     */
    private static function berekenElektrischeBijtelling($regels_jaar, $cataloguswaarde, $resultaat) {
        $regels = self::$bijtelling_regels[$regels_jaar];
        
        if (!isset($regels['elektrisch'])) {
            $resultaat['bijtelling_percentage'] = 22;
            $resultaat['uitleg'] = "Geen speciale elektrische regels voor {$regels_jaar}, standaard 22%";
            $resultaat['bijtelling_bedrag_jaar'] = $cataloguswaarde * 0.22;
            return $resultaat;
        }
        
        $elektr_regels = $regels['elektrisch'];
        
        // Check voor drempelwaarde
        if (isset($elektr_regels['drempel']) && $elektr_regels['drempel']) {
            $drempel = $elektr_regels['drempel'];
            $resultaat['drempelwaarde'] = $drempel;
            
            if ($cataloguswaarde <= $drempel) {
                $percentage = $elektr_regels['percentage_laag'];
                $resultaat['bijtelling_percentage'] = $percentage;
                $resultaat['uitleg'] = "Elektrisch {$percentage}% (cataloguswaarde ≤ €" . number_format($drempel, 0, ',', '.') . ")";
                $resultaat['bijtelling_bedrag_jaar'] = $cataloguswaarde * ($percentage / 100);
            } else {
                // Split berekening
                $laag_deel = $drempel * ($elektr_regels['percentage_laag'] / 100);
                $hoog_deel = ($cataloguswaarde - $drempel) * ($elektr_regels['percentage_hoog'] / 100);
                
                $resultaat['bijtelling_bedrag_jaar'] = $laag_deel + $hoog_deel;
                $resultaat['bijtelling_percentage'] = round(($resultaat['bijtelling_bedrag_jaar'] / $cataloguswaarde) * 100, 1);
                $resultaat['uitleg'] = "Elektrisch split: {$elektr_regels['percentage_laag']}% tot €" . 
                    number_format($drempel, 0, ',', '.') . ", {$elektr_regels['percentage_hoog']}% daarboven";
            }
        } else {
            $percentage = $elektr_regels['percentage_laag'];
            $resultaat['bijtelling_percentage'] = $percentage;
            $resultaat['uitleg'] = "Elektrisch volledig {$percentage}% (geen drempelwaarde)";
            $resultaat['bijtelling_bedrag_jaar'] = $cataloguswaarde * ($percentage / 100);
        }
        
        return $resultaat;
    }
    
    /**
     * Bereken CO2-gebaseerde bijtelling (2008-2016)
     */
    private static function berekenCO2Bijtelling($regels, $brandstof, $co2_uitstoot, $cataloguswaarde, $resultaat) {
        $co2_regels = $regels['co2_regels'];
        
        foreach ($co2_regels as $regel_naam => $regel) {
            if (isset($regel['brandstof']) && $regel['brandstof'] !== $brandstof) {
                continue;
            }
            
            $voldoet_max = !isset($regel['co2_max']) || $co2_uitstoot <= $regel['co2_max'];
            $voldoet_min = !isset($regel['co2_min']) || $co2_uitstoot >= $regel['co2_min'];
            
            if ($voldoet_max && $voldoet_min) {
                $resultaat['bijtelling_percentage'] = $regel['percentage'];
                $resultaat['uitleg'] = "CO2 categorie '{$regel_naam}': {$regel['percentage']}% " .
                    "({$co2_uitstoot} g/km CO2, {$brandstof})";
                $resultaat['bijtelling_bedrag_jaar'] = $cataloguswaarde * ($regel['percentage'] / 100);
                return $resultaat;
            }
        }
        
        $percentage = $regels['algemeen'] ?? 25;
        $resultaat['bijtelling_percentage'] = $percentage;
        $resultaat['uitleg'] = "CO2 fallback: {$percentage}% (geen match gevonden voor {$co2_uitstoot} g/km)";
        $resultaat['bijtelling_bedrag_jaar'] = $cataloguswaarde * ($percentage / 100);
        
        return $resultaat;
    }
    
    /**
     * Normaliseer brandstof type
     */
    private static function normaliseerBrandstof($brandstof) {
        $brandstof_lower = strtolower(trim($brandstof));
        
        $mapping = [
            'elektrisch' => 'elektrisch', 'electric' => 'elektrisch', 'ev' => 'elektrisch', 'bev' => 'elektrisch',
            'benzine' => 'benzine', 'gasoline' => 'benzine', 'otto' => 'benzine',
            'diesel' => 'diesel', 'hybride' => 'hybride', 'hybrid' => 'hybride',
            'phev' => 'phev', 'plug-in' => 'phev', 'waterstof' => 'waterstof', 'hydrogen' => 'waterstof',
            'lpg' => 'lpg', 'autogas' => 'lpg', 'cng' => 'cng', 'aardgas' => 'cng'
        ];
        
        foreach ($mapping as $zoekterm => $resultaat) {
            if (strpos($brandstof_lower, $zoekterm) !== false) {
                return $resultaat;
            }
        }
        
        return 'benzine'; // Default fallback
    }
    
    /**
     * Vind dichtstbijzijnde jaar met regels
     */
    private static function vindDichtstbijzijndeJaar($doel_jaar) {
        $beschikbare_jaren = array_keys(self::$bijtelling_regels);
        $dichtstbij = $beschikbare_jaren[0];
        $kleinste_verschil = abs($doel_jaar - $dichtstbij);
        
        foreach ($beschikbare_jaren as $jaar) {
            $verschil = abs($doel_jaar - $jaar);
            if ($verschil < $kleinste_verschil) {
                $kleinste_verschil = $verschil;
                $dichtstbij = $jaar;
            }
        }
        
        return $dichtstbij;
    }
    
    /**
     * Bereken 60-maanden vastzetting
     */
    public static function bereken60MaandenVastzetting($eerste_toelating_datum, $huidig_jaar = null) {
        $huidig_jaar = $huidig_jaar ?: date('Y');
        
        $datum_delen = explode('-', $eerste_toelating_datum);
        if (count($datum_delen) !== 3) {
            return ['error' => 'Ongeldige datum format'];
        }
        
        $toelating_jaar = intval($datum_delen[0]);
        $toelating_maand = intval($datum_delen[1]);
        
        $start_maand = $toelating_maand + 1;
        $start_jaar = $toelating_jaar;
        
        if ($start_maand > 12) {
            $start_maand = 1;
            $start_jaar++;
        }
        
        $eind_maand = $start_maand;
        $eind_jaar = $start_jaar + 5; // 60 maanden = 5 jaar
        
        $is_actief = ($huidig_jaar < $eind_jaar) || 
                    ($huidig_jaar == $eind_jaar && date('n') < $eind_maand);
        
        return [
            'start_datum' => sprintf('%04d-%02d-01', $start_jaar, $start_maand),
            'eind_datum' => sprintf('%04d-%02d-01', $eind_jaar, $eind_maand),
            'is_actief' => $is_actief,
            'regels_jaar' => $toelating_jaar,
            'resterende_maanden' => $is_actief ? 
                (($eind_jaar - $huidig_jaar) * 12 + ($eind_maand - date('n'))) : 0
        ];
    }
    
    /**
     * Get all available years
     */
    public static function getBeschikbareJaren() {
        return array_keys(self::$bijtelling_regels);
    }
    
    /**
     * Get rules for specific year
     */
    public static function getRegelsVoorJaar($jaar) {
        return self::$bijtelling_regels[$jaar] ?? null;
    }
}

/**
 * Helper functies voor quick access
 */
function getBijtelling($bouwjaar, $brandstof, $cataloguswaarde, $dagwaarde = null, $co2_uitstoot = null, $eerste_toelating = null) {
    return BijtellingsDatabase::berekenBijtelling($bouwjaar, $brandstof, $cataloguswaarde, $dagwaarde, $co2_uitstoot, $eerste_toelating);
}

function isYoungtimerAuto($bouwjaar) {
    $leeftijd = date('Y') - $bouwjaar;
    return $leeftijd >= 15 && $leeftijd <= 30;
}

function getElektrischPercentage($cataloguswaarde, $jaar = null) {
    $jaar = $jaar ?: date('Y');
    $result = BijtellingsDatabase::berekenBijtelling($jaar, 'elektrisch', $cataloguswaarde);
    return $result['bijtelling_percentage'];
}
