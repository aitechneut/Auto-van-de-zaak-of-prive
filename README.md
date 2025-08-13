# 🚗 Auto van de Zaak of Privé Calculator

Een geavanceerde webapplicatie die ondernemers helpt bij het maken van de juiste keuze tussen een auto van de zaak of een privé auto.

## ✨ Features

- **🔍 RDW Kenteken Lookup** - Automatisch voertuiggegevens ophalen via kenteken
- **💰 Realtime Kostenvergelijking** - Direct inzicht in zakelijke vs privé autokosten
- **📊 Bijtelling Berekening** - Automatische berekening op basis van brandstoftype en leeftijd
- **🎯 Youngtimer Detectie** - Speciale 35% regeling voor auto's 15-30 jaar oud
- **💾 Export Functionaliteit** - Bewaar berekeningen als JSON
- **🖨️ Print Vriendelijk** - Optimaal voor printen van resultaten
- **📱 Responsive Design** - Werkt perfect op desktop, tablet en mobiel

## 🚀 Live Demo

Probeer de calculator direct uit: [www.pianomanontour.nl/AutoKosten](https://www.pianomanontour.nl/AutoKosten)

## 🛠️ Technologie Stack

- **Frontend**: HTML5, CSS3 (Gradient design), Vanilla JavaScript
- **Backend**: PHP 7.4+
- **API**: RDW Open Data API (gratis, geen key nodig)
- **Hosting**: Hostinger
- **Version Control**: Git/GitHub

## 📋 Installatie

### Lokaal draaien:
```bash
# Clone de repository
git clone https://github.com/aitechneut/Auto-van-de-zaak-of-prive.git

# Ga naar de project directory
cd Auto-van-de-zaak-of-prive

# Start een lokale PHP server
php -S localhost:8000
```

### Vereisten:
- PHP 7.4 of hoger
- cURL extensie voor PHP
- Internetverbinding voor RDW API calls

## 💡 Gebruik

1. **Voer een kenteken in** - Bijvoorbeeld: AB-123-C
2. **Klik op "Zoek Auto"** - Haalt automatisch voertuiggegevens op
3. **Pas eventueel waarden aan** - Kilometrage, brandstofprijs, etc.
4. **Klik op "Bereken Autokosten"** - Zie direct welke optie voordeliger is
5. **Bewaar of print het resultaat** - Voor je administratie

## 📊 Berekeningen

### Zakelijke auto (bijtelling):
- Bijtelling percentage (4%, 16%, 22% of 35%)
- Berekend over cataloguswaarde (of dagwaarde bij youngtimer)
- Extra inkomstenbelasting (37% default)

### Privé auto kosten:
- Afschrijving
- Brandstof
- Verzekering
- Onderhoud
- Wegenbelasting (MRB)
- APK (indien van toepassing)

## 🔄 Updates

### Laatste update: December 2024
- RDW API integratie
- Youngtimer detectie
- Verbeterde UI/UX
- Export functionaliteit

## 👨‍💻 Developer

Ontwikkeld door **Richard Surie**
- 🎹 Eigenaar Muziekschool & Duelling Pianoshows
- 💻 AI Techneut & Websitebouwer
- 📍 Den Haag, Zoetermeer, Amsterdam

## 📝 Licentie

Dit project is eigendom van PianoManOnTour.nl. 
Voor commercieel gebruik, neem contact op via de website.

## 🤝 Contributing

Suggesties en verbeteringen zijn welkom! Open een issue of stuur een pull request.

## 📧 Contact

Voor vragen of ondersteuning:
- Website: [www.pianomanontour.nl](https://www.pianomanontour.nl)
- Project: [AutoKosten Calculator](https://www.pianomanontour.nl/AutoKosten)

---
*Made with ❤️ by PianoManOnTour.nl*