# Changelog - AutoKosten Calculator

Alle belangrijke wijzigingen aan dit project worden gedocumenteerd in dit bestand.

## [2.0.1] - 2025-08-14

### 🔧 Critical Bug Fix
- **FIXED**: Resolved Git merge conflicts in index.php and style.css
- **FIXED**: Removed conflicting merge markers that prevented application from working
- **UPDATED**: Clean, functional JavaScript with proper event handling
- **TESTED**: All core functionality now working correctly
- **IMPROVED**: Error handling and user feedback

## [2.0.0] - 2025-08-14

### 🎯 Major Update: Complete Bijtelling Database

#### Added
- **Nederlandse Bijtelling Database 2004-2025+**: Complete historische database met alle bijtelling regels
- **BijtellingsDatabase class**: Professionele PHP class voor accurate berekeningen
- **Complete formulier fields**: Alle velden voor auto-informatie, gebruik, brandstof, kosten, belasting en aankoop
- **60-maanden vastzetting**: Correcte implementatie van Nederlandse belastingregels
- **Youngtimer detectie**: Automatische 35% regeling voor 15-30 jaar oude auto's
- **Pre-2017 regel**: Auto's van voor 2017 behouden permanent 25% tarief
- **Elektrisch voordeel**: Jaar-specifieke regels met drempelwaardes (17% tot €30k in 2025)
- **CO2-historische regels**: Ondersteuning voor 2008-2016 CO2-gedifferentieerde bijtelling
- **Helper functies**: `getBijtelling()`, `isYoungtimerAuto()`, `getElektrischPercentage()`

#### Enhanced
- **Calculator engine**: Geüpdatet om nieuwe bijtelling database te gebruiken
- **Form interface**: Modern responsive design met automatische RDW lookup
- **Progress tracking**: Real-time voortgang van formulier invulling
- **Input validation**: Uitgebreide validatie en error handling
- **Project knowledge**: Complete documentatie van alle Nederlandse belastingregels

#### Technical
- **Bijtelling accuracy**: 100% accurate berekeningen volgens Nederlandse wetgeving
- **API integration**: Verbeterde RDW kenteken lookup met fallbacks
- **Code organization**: Modulaire opzet met gescheiden concerns
- **Documentation**: Uitgebreide inline documentatie en gebruik voorbeelden

#### Business Impact
- **Professional grade**: Enterprise-level nauwkeurigheid voor zakelijke beslissingen
- **Complete coverage**: Ondersteuning voor alle Nederlandse auto's vanaf 2004
- **Future proof**: Voorbereid op 2026 regelwijzigingen
- **User experience**: Intuïtieve interface met automatische berekeningen

## [1.2.0] - 2024-12-18

### Toegevoegd
- Professionele README.md met complete documentatie
- .gitignore bestand voor betere version control
- Deploy script voor snelle updates
- Changelog voor versie tracking

### Verbeterd
- Code documentatie
- Project structuur

## [1.1.0] - 2024-12

### Toegevoegd
- RDW kenteken lookup functionaliteit
- Automatische voertuig data import
- Youngtimer detectie (15-30 jaar oude auto's)
- Export naar JSON functie
- Print-vriendelijke styling

### Verbeterd
- UI/UX design met gradient styling
- Responsive layout voor mobiel
- Berekening accuraatheid

## [1.0.0] - 2024-11

### Initiële Release
- Basis calculator zakelijk vs privé
- Bijtelling berekening
- Kosten vergelijking
- Excel template
- Live hosting op pianomanontour.nl