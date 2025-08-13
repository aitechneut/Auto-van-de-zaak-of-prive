#!/bin/bash
# Git Deploy Script voor AutoKosten Project
# Gebruik: ./deploy.sh "Commit message"

echo "🚗 AutoKosten Deploy Script"
echo "=========================="

# Controleer of er een commit message is
if [ -z "$1" ]; then
    echo "❌ Error: Geef een commit message mee!"
    echo "Gebruik: ./deploy.sh \"Je commit message\""
    exit 1
fi

# Toon huidige status
echo "📊 Huidige Git Status:"
git status --short

# Vraag bevestiging
echo ""
read -p "Wil je doorgaan met deployen? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🔄 Bestanden toevoegen..."
    git add .
    
    echo "💾 Committen met message: $1"
    git commit -m "$1"
    
    echo "📤 Pushen naar GitHub..."
    git push origin main
    
    echo "✅ Deploy succesvol!"
    echo "🌐 Check je website: https://www.pianomanontour.nl/AutoKosten"
    
    # Optioneel: Open de website in browser
    read -p "Wil je de live website openen? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        open "https://www.pianomanontour.nl/AutoKosten"
    fi
else
    echo "❌ Deploy geannuleerd"
    exit 0
fi