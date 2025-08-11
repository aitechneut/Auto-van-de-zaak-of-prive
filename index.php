<?php
// AutoKosten Calculator - Zakelijk vs Privé
// Voor pianomanontour.nl/AutoKosten

// Error reporting (zet uit op productie)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start sessie voor data opslag
session_start();

// Basis configuratie
define('APP_NAME', 'AutoKosten Calculator');
define('APP_VERSION', '1.0.0');

// Include helpers (maken we zo)
require_once 'includes/functions.php';
require_once 'includes/calculator.php';

// Verwerk formulier als het verstuurd is
$resultaat = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultaat = berekenAutokosten($_POST);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Zakelijk of Privé?</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🚗 AutoKosten Calculator</h1>
            <p>Bereken of een auto van de zaak of privé voordeliger is</p>
        </header>

        <main>
            <form method="POST" action="">
                <section class="form-section">
                    <h2>Auto gegevens</h2>
                    
                    <label for="catalogusprijs">
                        Catalogusprijs (€):
                        <input type="number" name="catalogusprijs" id="catalogusprijs" 
                               value="<?= $_POST['catalogusprijs'] ?? 35000 ?>" required>
                    </label>

                    <label for="bijtelling_percentage">
                        Bijtelling percentage (%):
                        <select name="bijtelling_percentage" id="bijtelling_percentage">
                            <option value="22">22% (normale auto)</option>
                            <option value="16">16% (PHEV/zuinig)</option>
                            <option value="4">4% (elektrisch)</option>
                        </select>
                    </label>

                    <label for="km_per_jaar">
                        Kilometers per jaar:
                        <input type="number" name="km_per_jaar" id="km_per_jaar" 
                               value="<?= $_POST['km_per_jaar'] ?? 20000 ?>" required>
                    </label>
                </section>

                <section class="form-section">
                    <h2>Persoonlijke situatie</h2>
                    
                    <label for="bruto_inkomen">
                        Bruto jaarinkomen (€):
                        <input type="number" name="bruto_inkomen" id="bruto_inkomen" 
                               value="<?= $_POST['bruto_inkomen'] ?? 50000 ?>" required>
                    </label>

                    <label for="km_prive">
                        Privé kilometers per jaar:
                        <input type="number" name="km_prive" id="km_prive" 
                               value="<?= $_POST['km_prive'] ?? 5000 ?>">
                    </label>
                </section>

                <button type="submit" class="btn-calculate">Bereken Voordeligste Optie</button>
            </form>

            <?php if ($resultaat): ?>
            <section class="resultaat">
                <h2>📊 Berekening Resultaat</h2>
                <div class="resultaat-grid">
                    <div class="optie zakelijk">
                        <h3>Auto van de Zaak</h3>
                        <p class="kosten">€ <?= number_format($resultaat['zakelijk']['totaal'], 2, ',', '.') ?>/jaar</p>
                        <ul>
                            <li>Bijtelling: € <?= number_format($resultaat['zakelijk']['bijtelling'], 2, ',', '.') ?></li>
                            <li>Extra belasting: € <?= number_format($resultaat['zakelijk']['belasting'], 2, ',', '.') ?></li>
                        </ul>
                    </div>
                    
                    <div class="optie prive">
                        <h3>Privé Auto</h3>
                        <p class="kosten">€ <?= number_format($resultaat['prive']['totaal'], 2, ',', '.') ?>/jaar</p>
                        <ul>
                            <li>Afschrijving: € <?= number_format($resultaat['prive']['afschrijving'], 2, ',', '.') ?></li>
                            <li>Brandstof: € <?= number_format($resultaat['prive']['brandstof'], 2, ',', '.') ?></li>
                            <li>Onderhoud: € <?= number_format($resultaat['prive']['onderhoud'], 2, ',', '.') ?></li>
                        </ul>
                    </div>
                </div>
                
                <div class="advies <?= $resultaat['advies']['beste'] ?>">
                    <h3>💡 Advies</h3>
                    <p><?= $resultaat['advies']['tekst'] ?></p>
                    <p class="besparing">Verschil: <strong>€ <?= number_format($resultaat['advies']['verschil'], 2, ',', '.') ?>/jaar</strong></p>
                </div>
            </section>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; <?= date('Y') ?> PianoManOnTour.nl - AutoKosten Calculator v<?= APP_VERSION ?></p>
        </footer>
    </div>
</body>
</html>
