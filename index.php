<?php
/* ===========================================================
   AutoKosten – Privé vs Zakelijk (Youngtimer ready)
   Single-file PHP app voor Hostinger
   - RDW lookup (kenteken) voor merk/model, bouwjaar, brandstof, gewicht
   - Youngtimer-check + automatische bijtelling-basis/% (35% dagwaarde of 22% catalogus)
   - Indicatieve MRB (Zuid-Holland, benzine) o.b.v. gewicht (overschrijfbaar)
   - Dagwaarde-schatting (op cataloguswaarde + leeftijd) (overschrijfbaar)
   - Autofill highlight + Dark/Light theme
   - Grafieken met Chart.js
   =========================================================== */

// ---------- AJAX ROUTES ----------
if (isset($_GET['action'])) {
  header('Content-Type: application/json; charset=utf-8');
  switch ($_GET['action']) {
    case 'rdw':
      $kenteken = strtoupper(preg_replace('/[^A-Z0-9]/','', $_GET['kenteken'] ?? ''));
      echo json_encode(rdw_lookup($kenteken));
      exit;
    case 'indicative':
      $weight   = intval($_GET['weight'] ?? 0);
      $prov     = trim((string)($_GET['province'] ?? 'Zuid-Holland'));
      $bouwjaar = intval($_GET['bouwjaar'] ?? 0);
      $catalog  = floatval($_GET['catalog'] ?? 0.0);
      echo json_encode([
        'mrb_pm'    => indicative_mrb_month($weight, $prov),
        'dagwaarde' => estimate_dayvalue($catalog, $bouwjaar),
      ]);
      exit;
  }
}

// ---------- HELPERS ----------
function http_get_json(string $url){
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER => ['Accept: application/json']
  ]);
  $out = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  curl_close($ch);
  if ($out === false || $code !== 200) return null;
  $json = json_decode($out, true);
  return $json;
}

function rdw_lookup(string $kenteken): array {
  if (!$kenteken) return ['error'=>'kenteken required'];
  $vehicleUrl = "https://opendata.rdw.nl/resource/m9d7-ebf2.json?kenteken=" . urlencode($kenteken);
  $fuelUrl    = "https://opendata.rdw.nl/resource/8ys7-d773.json?kenteken=" . urlencode($kenteken);

  $res = [
    'kenteken'        => $kenteken,
    'merk'            => null,
    'handelsbenaming' => null,
    'bouwjaar'        => null,
    'gewicht'         => null,
    'brandstof'       => null,
  ];

  $vehicle = http_get_json($vehicleUrl);
  if (is_array($vehicle) && count($vehicle) > 0){
    $v = $vehicle[0];
    $res['merk']            = $v['merk']            ?? null;
    $res['handelsbenaming'] = $v['handelsbenaming'] ?? null;
    if (!empty($v['datum_eerste_toelating'])) $res['bouwjaar'] = substr($v['datum_eerste_toelating'],0,4);
    if (!empty($v['massa_rijklaar']))        $res['gewicht']  = (int)$v['massa_rijklaar'];
    elseif (!empty($v['massa_ledig_voertuig'])) $res['gewicht']= (int)$v['massa_ledig_voertuig'];
  }
  $fuel = http_get_json($fuelUrl);
  if (is_array($fuel) && count($fuel) > 0) $res['brandstof'] = $fuel[0]['brandstof_omschrijving'] ?? null;

  return $res;
}

// Indicatieve MRB (Zuid-Holland, benzine) – ruwe schatting/indicatie!
function indicative_mrb_quarter_zuidholland_benzine(int $kg): float {
  if ($kg <= 950)   return 156;
  if ($kg <= 1050)  return 168;
  if ($kg <= 1150)  return 180;
  if ($kg <= 1250)  return 196;
  if ($kg <= 1350)  return 214;
  if ($kg <= 1450)  return 232;
  if ($kg <= 1550)  return 252;
  if ($kg <= 1650)  return 276;
  if ($kg <= 1750)  return 300;
  if ($kg <= 1850)  return 324;
  return 350;
}
function indicative_mrb_month(int $kg, string $province='Zuid-Holland'): float {
  // Alleen ZH benzine in deze indicatie
  return round(indicative_mrb_quarter_zuidholland_benzine($kg) / 3.0, 2);
}

// Dagwaarde schatting (afschrijvingscurve)
function estimate_dayvalue(float $catalog, int $bouwjaar): float {
  if ($catalog <= 0 || $bouwjaar <= 0) return 0.0;
  $now = (int)date('Y'); $age = max(0, $now - $bouwjaar);
  $v = $catalog;
  for ($y=1; $y <= $age; $y++){
    if ($y <= 5)      $v *= 0.82;  // -18%
    elseif ($y <= 10) $v *= 0.90;  // -10%
    else              $v *= 0.93;  // -7%
  }
  $v = max(500.0, min($v, 8000.0));
  return round($v, 0);
}

// Format helpers
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function num($v, $dec=2){ return number_format((float)$v, $dec, ',', '.'); }

// -------------- Berekening --------------
$DEFAULT_IB = 37.48;
$cars = $_POST['cars'] ?? [];
$results = [];
if (!empty($cars) && is_array($cars)){
  foreach ($cars as $i=>$c){
    $car = normalize_car($c, $DEFAULT_IB);
    $calc = calculate_costs($car);
    $results[] = ['input'=>$car, 'calc'=>$calc];
  }
}

function normalize_car($c, $DEFAULT_IB){
  $get = fn($k,$d='') => isset($c[$k]) ? trim((string)$c[$k]) : $d;
  $car = [
    'label'           => $get('label',''),
    'kenteken'        => strtoupper(preg_replace('/[^A-Z0-9-]/','',$get('kenteken',''))),
    'bouwjaar'        => (int)$get('bouwjaar',0),
    'brandstof'       => $get('brandstof',''),
    'gewicht'         => (int)$get('gewicht',0),
    'km_per_maand'    => (float)$get('km_per_maand',2000),
    'verbruik_waarde' => (float)$get('verbruik_waarde',0),
    'verbruik_type'   => $get('verbruik_type','km_per_l'), // km_per_l | kWh_per_100km
    'energie_prijs'   => (float)$get('energie_prijs',2.10),
    'verzekering_type'=> $get('verzekering_type','WA+'),
    'verzekering_p_m' => (float)$get('verzekering_p_m',100),
    'mrb_p_m'         => (float)$get('mrb_p_m',0),
    'onderhoud_p_m'   => (float)$get('onderhoud_p_m',100),
    'ib_pct'          => (float)$get('ib_pct',$DEFAULT_IB),
    'prive_gebruik'   => $get('prive_gebruik','Ja')==='Ja',
    'is_youngtimer'   => $get('is_youngtimer','Auto'),
    'bijtelling_basis'=> $get('bijtelling_basis','Auto'), // Auto | Dagwaarde | Catalogus
    'bijtelling_pct'  => (float)$get('bijtelling_pct',22),
    'dagwaarde'       => (float)$get('dagwaarde',0),
    'cataloguswaarde' => (float)$get('cataloguswaarde',0),
    'aankoop'         => (float)$get('aankoop',0),
    'restwaarde'      => (float)$get('restwaarde',0),
    'horizon_jaren'   => (int)$get('horizon_jaren',5),
  ];
  // Youngtimer afleiden
  if ($car['is_youngtimer']==='Auto' && $car['bouwjaar']>0){
    $jaar=(int)date('Y'); $car['is_youngtimer'] = (($jaar-$car['bouwjaar'])>=15) ? 'Ja':'Nee';
  }
  // Bijtelling basis/% afleiden
  if ($car['bijtelling_basis']==='Auto'){
    $car['bijtelling_basis'] = ($car['is_youngtimer']==='Ja') ? 'Dagwaarde':'Catalogus';
    $car['bijtelling_pct']   = ($car['is_youngtimer']==='Ja') ? 35 : 22;
  }
  // MRB indicatie als leeg
  if ($car['mrb_p_m']<=0 && $car['gewicht']>0){
    $car['mrb_p_m'] = indicative_mrb_month($car['gewicht'], 'Zuid-Holland');
  }
  // Dagwaarde schatten als leeg en catalogus+bj bekend
  if ($car['dagwaarde']<=0 && $car['cataloguswaarde']>0 && $car['bouwjaar']>0){
    $car['dagwaarde'] = estimate_dayvalue($car['cataloguswaarde'],$car['bouwjaar']);
  }
  return $car;
}

function calculate_costs($car){
  $km = $car['km_per_maand']; $ib = $car['ib_pct']/100.0;

  // Energiekosten
  if ($car['verbruik_type']==='km_per_l'){
    $liters = ($car['verbruik_waarde']>0) ? ($km / $car['verbruik_waarde']) : 0.0;
    $energy = $liters * $car['energie_prijs'];
  } else {
    $kwh = ($car['verbruik_waarde']>0) ? ($km * ($car['verbruik_waarde']/100.0)) : 0.0;
    $energy = $kwh * $car['energie_prijs'];
  }

  $basis = $energy + $car['mrb_p_m'] + $car['verzekering_p_m'] + $car['onderhoud_p_m'];

  // Bijtelling
  $basis_eur = ($car['bijtelling_basis']==='Dagwaarde') ? $car['dagwaarde'] : $car['cataloguswaarde'];
  $bijtelling_bruto_pm = ($car['prive_gebruik'] && $basis_eur>0) ? ($car['bijtelling_pct']/100.0)*$basis_eur/12.0 : 0.0;
  $bijtelling_netto_pm = $bijtelling_bruto_pm * $ib;

  // Zakelijk vs privé
  $belastingvoordeel = $basis * $ib;
  $zakelijk_netto = $basis + $bijtelling_netto_pm - $belastingvoordeel;
  $prive = $basis;

  // Totals
  $mnd = max(1,$car['horizon_jaren']*12);
  $afschrijving = max(0,$car['aankoop'] - $car['restwaarde']);
  $totaal_zakelijk = $zakelijk_netto * $mnd + $afschrijving;
  $totaal_prive    = $prive         * $mnd + $afschrijving;

  return [
    'energy'=>$energy, 'basis'=>$basis,
    'bijtelling_bruto_pm'=>$bijtelling_bruto_pm, 'bijtelling_netto_pm'=>$bijtelling_netto_pm,
    'belastingvoordeel'=>$belastingvoordeel,
    'zakelijk_netto'=>$zakelijk_netto, 'prive'=>$prive,
    'totaal_zakelijk'=>$totaal_zakelijk, 'totaal_prive'=>$totaal_prive,
    'besparing_pm'=>($prive-$zakelijk_netto),
    'besparing_totaal'=>($totaal_prive - $totaal_zakelijk),
  ];
}
?>
<!doctype html>
<html lang="nl" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>AutoKosten – Privé vs Zakelijk (Youngtimer)</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
<style>
  :root{
    --bg:#0b1020; --card:#151c36; --text:#e9eefc; --muted:#b7c2ff; --accent:#79ffa8; --brand:#ff4d4d; --field:#0f1530; --border:#2b3a7a;
  }
  [data-theme="light"]{
    --bg:#f6f7fb; --card:#ffffff; --text:#1a2240; --muted:#56638a; --accent:#1a9c5a; --brand:#e02121; --field:#ffffff; --border:#dfe3f5;
  }
  body{background:var(--bg);color:var(--text)}
  .card{background:var(--card);border:1px solid var(--border)}
  .form-control,.form-select{background:var(--field);color:var(--text);border:1px solid var(--border)}
  .form-control:focus,.form-select:focus{border-color:var(--brand);box-shadow:0 0 0 .2rem color-mix(in srgb, var(--brand) 25%, transparent)}
  .btn-primary{background:var(--brand);border:0}
  .btn-outline-light{border-color:var(--brand);color:var(--text)}
  .btn-outline-light:hover{background:var(--brand)}
  h1, h5, .table thead th { color: var(--brand) !important; }
  .table thead th { border-bottom: 2px solid var(--brand); }
  a{color:color-mix(in srgb, var(--brand) 70%, white)}
  .muted{color:var(--muted)}
  .autofilled{ box-shadow: 0 0 0 .15rem rgba(255,217,0,.5) inset; border-color:#ffd900 !important; }
  .theme-toggle{ position: fixed; top: 14px; right: 14px; z-index: 20; }
</style>
</head>
<body>
<div class="theme-toggle">
  <button class="btn btn-sm btn-outline-light" id="toggleThemeBtn">🌞/🌙</button>
</div>

<div class="container py-4">
  <h1 class="mb-1">AutoKosten – Privé vs Zakelijk <span class="muted">(youngtimer ready)</span></h1>
  <p class="muted mb-4">Vul idealiter alleen <b>kenteken</b> in. De rest wordt automatisch aangevuld (RDW + indicaties). Je kunt alles hierna overschrijven. Klik <b>Bereken</b> voor tabellen & grafieken.</p>

  <form method="post" id="carsForm">
    <div id="carsContainer" class="row g-3">
      <?php
        if (empty($cars)) {
          echo car_card_html(0, []);
        } else {
          foreach ($cars as $i=>$c) echo car_card_html($i, $c);
        }
      ?>
    </div>

    <div class="d-flex gap-2 mt-3">
      <button type="button" class="btn btn-outline-light" id="addCarBtn">+ Auto toevoegen</button>
      <button type="submit" class="btn btn-primary">Bereken</button>
    </div>
  </form>

  <?php if (!empty($results)): ?>
    <hr class="my-4 text-light">
    <h5 class="mb-3">Resultaten</h5>

    <div class="table-responsive mb-3">
      <table class="table table-striped align-middle" style="color:var(--text)">
        <thead>
          <tr>
            <th>Auto</th>
            <th>Privé p/m</th>
            <th>Zakelijk p/m (netto)</th>
            <th>Besparing p/m</th>
            <th>Bijtelling netto p/m</th>
            <th>Belastingvoordeel p/m</th>
            <th>Totaal Privé (<?=h($results[0]['input']['horizon_jaren'])?> jr)</th>
            <th>Totaal Zakelijk (<?=h($results[0]['input']['horizon_jaren'])?> jr)</th>
            <th>Besparing totaal</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($results as $r):
          $i=$r['input']; $x=$r['calc'];
          $label = $i['label'] ?: ($i['kenteken'] ?: 'Auto');
        ?>
          <tr>
            <td><?=h($label)?></td>
            <td>€ <?=num($x['prive'])?></td>
            <td>€ <?=num($x['zakelijk_netto'])?></td>
            <td style="color:var(--accent)">€ <?=num($x['besparing_pm'])?></td>
            <td>€ <?=num($x['bijtelling_netto_pm'])?></td>
            <td>€ <?=num($x['belastingvoordeel'])?></td>
            <td>€ <?=num($x['totaal_prive'])?></td>
            <td>€ <?=num($x['totaal_zakelijk'])?></td>
            <td style="color:var(--accent)">€ <?=num($x['besparing_totaal'])?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="row g-4">
      <div class="col-12 col-lg-6">
        <div class="card p-3">
          <h5>Maandlast per auto</h5>
          <canvas id="chartMonthly"></canvas>
        </div>
      </div>
      <div class="col-12 col-lg-6">
        <div class="card p-3">
          <h5>Totale besparing (horizon)</h5>
          <canvas id="chartTotal"></canvas>
        </div>
      </div>
    </div>

    <script>
      const chartData = <?php
        $labels=[]; $priv=[]; $zak=[]; $saveTot=[];
        foreach ($results as $r){
          $i=$r['input']; $x=$r['calc'];
          $labels[] = $i['label'] ?: ($i['kenteken'] ?: 'Auto');
          $priv[]   = round($x['prive'],2);
          $zak[]    = round($x['zakelijk_netto'],2);
          $saveTot[]= round($x['totaal_prive'] - $x['totaal_zakelijk'],2);
        }
        echo json_encode(['labels'=>$labels,'priv'=>$priv,'zak'=>$zak,'saveTot'=>$saveTot], JSON_UNESCAPED_UNICODE);
      ?>;

      const ctx1=document.getElementById('chartMonthly').getContext('2d');
      new Chart(ctx1,{type:'bar',data:{labels:chartData.labels,datasets:[
        {label:'Privé p/m',data:chartData.priv},
        {label:'Zakelijk p/m (netto)',data:chartData.zak}
      ]},options:{responsive:true,plugins:{legend:{labels:{color:getComputedStyle(document.body).getPropertyValue('--text')}}},
      scales:{x:{ticks:{color:getComputedStyle(document.body).getPropertyValue('--text')}},y:{ticks:{color:getComputedStyle(document.body).getPropertyValue('--text')}}}});

      const ctx2=document.getElementById('chartTotal').getContext('2d');
      new Chart(ctx2,{type:'bar',data:{labels:chartData.labels,datasets:[
        {label:'Besparing totaal (€)',data:chartData.saveTot}
      ]},options:{responsive:true,plugins:{legend:{labels:{color:getComputedStyle(document.body).getPropertyValue('--text')}}},
      scales:{x:{ticks:{color:getComputedStyle(document.body).getPropertyValue('--text')}},y:{ticks:{color:getComputedStyle(document.body).getPropertyValue('--text')}}}});
    </script>
  <?php endif; ?>

  <footer class="mt-5 small muted">
    © <?=date('Y')?> AutoKosten – pianomanontour.nl · Let op: MRB/dagwaarde hier zijn indicaties. Controleer altijd bij officiële bronnen.
  </footer>
</div>

<!-- TEMPLATE + JS -->
<template id="carCardTemplate">
  <?=str_replace(["\n","\r"], '', car_card_html('__INDEX__', [], true));?>
</template>

<script>
// --- Theme toggle ---
const root = document.documentElement;
function setTheme(t){ root.setAttribute('data-theme', t); localStorage.setItem('autokosten-theme', t); }
document.getElementById('toggleThemeBtn').addEventListener('click', ()=>{
  setTheme(root.getAttribute('data-theme')==='dark' ? 'light' : 'dark');
});
(function(){ const t=localStorage.getItem('autokosten-theme'); if(t) setTheme(t); })();

// --- Debounce helper ---
function debounce(fn, ms){ let t; return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args), ms); }; }

// --- Defaults op modelnaam (optioneel uitbreiden) ---
const defaultConsumption = [
  {match:/\bPRIUS\b/i, type:'km_per_l', value:20},
  {match:/\bMODEL\s*3\b/i, type:'kWh_per_100km', value:16},
  {match:/\bMODEL\s*Y\b/i, type:'kWh_per_100km', value:17},
  {match:/\bIONIQ\b.*(EV|ELECTRIC)/i, type:'kWh_per_100km', value:15},
];

// Autofill helper: waarde invullen + highlight, maar highlight weg als user typt
function fillAndMark(selector, value){
  const el = document.querySelector(selector);
  if(!el || value===undefined || value===null || value==='') return;
  if(!el.value){ el.value = value; markAutofilled(el); }
}
function markAutofilled(el){
  el.classList.add('autofilled');
  const remove = ()=> el.classList.remove('autofilled');
  el.addEventListener('input', remove, {once:true});
  el.addEventListener('change', remove, {once:true});
}

function attachAutoLookupForCard(idx){
  const base = `#car-${idx} `;
  const kentekenInput = document.querySelector(`${base}input[name="cars[${idx}][kenteken]"]`);
  if(!kentekenInput) return;
  const doLookup = debounce(()=> rdwLookup(idx), 400);
  kentekenInput.addEventListener('input', doLookup);
  kentekenInput.addEventListener('blur', ()=> rdwLookup(idx));
}

document.addEventListener('DOMContentLoaded', ()=>{
  document.querySelectorAll('[id^="car-"]').forEach(card=>{
    const idx = card.id.split('-')[1];
    attachAutoLookupForCard(idx);
  });
});

const addBtn = document.getElementById('addCarBtn');
if(addBtn){
  addBtn.addEventListener('click', ()=>{
    const tpl = document.getElementById('carCardTemplate').innerHTML;
    const html = tpl.replaceAll('__INDEX__', document.querySelectorAll('[id^="car-"]').length);
    const div = document.createElement('div'); div.className='col-12'; div.innerHTML = html;
    document.getElementById('carsContainer').appendChild(div);
    setTimeout(()=>{
      const last = document.querySelectorAll('[id^="car-"]'); if(!last.length) return;
      const card = last[last.length-1]; const idx = card.id.split('-')[1];
      attachAutoLookupForCard(idx);
    }, 50);
  });
}

function rdwLookup(idx){
  const base = `#car-${idx} `;
  const k = document.querySelector(`${base}input[name="cars[${idx}][kenteken]"]`).value;
  if(!k) return;
  fetch(`?action=rdw&kenteken=${encodeURIComponent(k)}`)
   .then(r=>r.json())
   .then(d=>{
     fillAndMark(`${base}input[name="cars[${idx}][bouwjaar]"]`, d.bouwjaar);
     fillAndMark(`${base}input[name="cars[${idx}][gewicht]"]`, d.gewicht);
     fillAndMark(`${base}input[name="cars[${idx}][brandstof]"]`, d.brandstof);

     const labelEl = document.querySelector(`${base}input[name="cars[${idx}][label]"]`);
     if(labelEl && !labelEl.value){
       const label = `${d.merk||''} ${d.handelsbenaming||''}`.trim();
       if(label){ labelEl.value = label; markAutofilled(labelEl); }
     }

     if(d.bouwjaar){
       const jaar=(new Date()).getFullYear();
       const is15 = (jaar - Number(d.bouwjaar)) >= 15;
       const ytSel = document.querySelector(`${base}select[name="cars[${idx}][is_youngtimer]"]`);
       const basisSel = document.querySelector(`${base}select[name="cars[${idx}][bijtelling_basis]"]`);
       const pctInp   = document.querySelector(`${base}input[name="cars[${idx}][bijtelling_pct]"]`);
       if(ytSel && !ytSel.value){ ytSel.value = is15 ? 'Ja':'Nee'; markAutofilled(ytSel); }
       if(basisSel && !basisSel.value){ basisSel.value = is15 ? 'Dagwaarde':'Catalogus'; markAutofilled(basisSel); }
       if(pctInp && !pctInp.value){ pctInp.value = is15 ? 35 : 22; markAutofilled(pctInp); }
     }

     const verbruikVal = document.querySelector(`${base}input[name="cars[${idx}][verbruik_waarde]"]`);
     const verbruikType= document.querySelector(`${base}select[name="cars[${idx}][verbruik_type]"]`);
     if(verbruikVal && !verbruikVal.value){
       const model = `${d.merk||''} ${d.handelsbenaming||''}`.trim();
       for(const rule of defaultConsumption){
         if(rule.match.test(model)){
           verbruikVal.value = rule.value; markAutofilled(verbruikVal);
           if(verbruikType && !verbruikType.value){ verbruikType.value = rule.type; markAutofilled(verbruikType); }
           break;
         }
       }
     }

     // Indicaties: MRB & dagwaarde
     const catalog = document.querySelector(`${base}input[name="cars[${idx}][cataloguswaarde]"]`).value || 0;
     const weight  = document.querySelector(`${base}input[name="cars[${idx}][gewicht]"]`).value || 0;
     fetch(`?action=indicative&province=Zuid-Holland&weight=${encodeURIComponent(weight)}&bouwjaar=${encodeURIComponent(d.bouwjaar||'')}&catalog=${encodeURIComponent(catalog)}`)
      .then(r=>r.json())
      .then(ind=>{
        const mrbEl = document.querySelector(`${base}input[name="cars[${idx}][mrb_p_m]"]`);
        if(mrbEl && (!mrbEl.value || Number(mrbEl.value)<=0) && ind.mrb_pm){ mrbEl.value = ind.mrb_pm; markAutofilled(mrbEl); }
        const dagEl = document.querySelector(`${base}input[name="cars[${idx}][dagwaarde]"]`);
        if(dagEl && (!dagEl.value || Number(dagEl.value)<=0) && ind.dagwaarde){ dagEl.value = ind.dagwaarde; markAutofilled(dagEl); }
      }).catch(()=>{});
   }).catch(()=>{});
}
</script>
</body>
</html>
<?php
// --------- UI Component: Auto-kaart ---------
function car_card_html($idx, $values=[], $forTemplate=false){
  $pref = "cars[$idx]"; $id = "car-$idx";
  $v = array_merge([
    'label'=>'','kenteken'=>'','bouwjaar'=>'','brandstof'=>'','gewicht'=>'',
    'km_per_maand'=>'2000','verbruik_waarde'=>'','verbruik_type'=>'km_per_l',
    'energie_prijs'=>'2.10','verzekering_type'=>'WA+','verzekering_p_m'=>'100',
    'mrb_p_m'=>'','onderhoud_p_m'=>'100','ib_pct'=>'37.48','prive_gebruik'=>'Ja',
    'is_youngtimer'=>'Auto','bijtelling_basis'=>'Auto','bijtelling_pct'=>'',
    'dagwaarde'=>'','cataloguswaarde'=>'','aankoop'=>'11000','restwaarde'=>'2000',
    'horizon_jaren'=>'5'
  ], $values);

  ob_start(); ?>
  <div class="col-12">
    <div class="card p-3" id="<?=h($id)?>">
      <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="mb-3">Auto <?=h(is_numeric($idx)?($idx+1):'')?></h5>
        <div class="small muted">Automatisch ingevulde velden zijn <b>geel omlijnd</b> en kun je gewoon aanpassen.</div>
      </div>

      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Label / Naam</label>
          <input class="form-control" name="<?=$pref?>[label]" value="<?=h($v['label'])?>" placeholder="Bijv. Prius 2006">
        </div>
        <div class="col-md-3">
          <label class="form-label">Kenteken</label>
          <input class="form-control" name="<?=$pref?>[kenteken]" value="<?=h($v['kenteken'])?>" placeholder="84-SHTF">
        </div>
        <div class="col-md-2">
          <label class="form-label">Bouwjaar</label>
          <input class="form-control" name="<?=$pref?>[bouwjaar]" value="<?=h($v['bouwjaar'])?>" placeholder="2006">
        </div>
        <div class="col-md-2">
          <label class="form-label">Brandstof</label>
          <input class="form-control" name="<?=$pref?>[brandstof]" value="<?=h($v['brandstof'])?>" placeholder="Benzine / Elektrisch">
        </div>
        <div class="col-md-2">
          <label class="form-label">Gewicht (kg)</label>
          <input class="form-control" name="<?=$pref?>[gewicht]" value="<?=h($v['gewicht'])?>" placeholder="1275">
        </div>

        <div class="col-md-2">
          <label class="form-label">Km per maand</label>
          <input type="number" class="form-control" name="<?=$pref?>[km_per_maand]" value="<?=h($v['km_per_maand'])?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Verbruik</label>
          <div class="input-group">
            <input type="number" step="0.01" class="form-control" name="<?=$pref?>[verbruik_waarde]" value="<?=h($v['verbruik_waarde'])?>" placeholder="">
            <select class="form-select" name="<?=$pref?>[verbruik_type]">
              <option value="km_per_l" <?= $v['verbruik_type']==='km_per_l'?'selected':'' ?>>km per liter</option>
              <option value="kWh_per_100km" <?= $v['verbruik_type']==='kWh_per_100km'?'selected':'' ?>>kWh / 100 km</option>
            </select>
          </div>
          <div class="form-text muted">Benzine/diesel: km/l · EV: kWh/100 km</div>
        </div>
        <div class="col-md-2">
          <label class="form-label">Prijs per liter/kWh (€)</label>
          <input type="number" step="0.01" class="form-control" name="<?=$pref?>[energie_prijs]" value="<?=h($v['energie_prijs'])?>">
        </div>

        <div class="col-md-2">
          <label class="form-label">Verzekering</label>
          <select class="form-select" name="<?=$pref?>[verzekering_type]">
            <option <?= $v['verzekering_type']==='WA'?'selected':'' ?>>WA</option>
            <option <?= $v['verzekering_type']==='WA+'?'selected':'' ?>>WA+</option>
            <option <?= $v['verzekering_type']==='Allrisk'?'selected':'' ?>>Allrisk</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Premie p/m (€)</label>
          <input type="number" step="0.01" class="form-control" name="<?=$pref?>[verzekering_p_m]" value="<?=h($v['verzekering_p_m'])?>">
        </div>

        <div class="col-md-2">
          <label class="form-label">MRB p/m (€)</label>
          <input type="number" step="0.01" class="form-control" name="<?=$pref?>[mrb_p_m]" value="<?=h($v['mrb_p_m'])?>" placeholder="auto-indicatie">
        </div>
        <div class="col-md-2">
          <label class="form-label">Onderhoud p/m (€)</label>
          <input type="number" step="0.01" class="form-control" name="<?=$pref?>[onderhoud_p_m]" value="<?=h($v['onderhoud_p_m'])?>">
        </div>

        <div class="col-md-2">
          <label class="form-label">IB-tarief (%)</label>
          <input type="number" step="0.01" class="form-control" name="<?=$pref?>[ib_pct]" value="<?=h($v['ib_pct'])?>">
        </div>

        <div class="col-md-2">
          <label class="form-label">Privégebruik?</label>
          <select class="form-select" name="<?=$pref?>[prive_gebruik]">
            <option <?= $v['prive_gebruik']==='Ja'?'selected':'' ?>>Ja</option>
            <option <?= $v['prive_gebruik']==='Nee'?'selected':'' ?>>Nee</option>
          </select>
        </div>
      </div>

      <div class="collapse mt-3" id="adv-<?=h($idx)?>">
        <div class="row g-3">
          <div class="col-md-2">
            <label class="form-label">Youngtimer?</label>
            <select class="form-select" name="<?=$pref?>[is_youngtimer]">
              <option <?= $v['is_youngtimer']==='Auto'?'selected':'' ?>>Auto</option>
              <option <?= $v['is_youngtimer']==='Ja'?'selected':'' ?>>Ja</option>
              <option <?= $v['is_youngtimer']==='Nee'?'selected':'' ?>>Nee</option>
            </select>
            <div class="form-text muted">Auto = afleiden uit bouwjaar (≥15jr)</div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Bijtelling-basis</label>
            <select class="form-select" name="<?=$pref?>[bijtelling_basis]">
              <option <?= $v['bijtelling_basis']==='Auto'?'selected':'' ?>>Auto</option>
              <option value="Dagwaarde" <?= $v['bijtelling_basis']==='Dagwaarde'?'selected':'' ?>>Dagwaarde</option>
              <option value="Catalogus" <?= $v['bijtelling_basis']==='Catalogus'?'selected':'' ?>>Catalogus</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Bijtelling %</label>
            <input type="number" step="0.01" class="form-control" name="<?=$pref?>[bijtelling_pct]" value="<?=h($v['bijtelling_pct'])?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Dagwaarde (€)</label>
            <input type="number" step="0.01" class="form-control" name="<?=$pref?>[dagwaarde]" value="<?=h($v['dagwaarde'])?>" placeholder="auto-indicatie">
          </div>
          <div class="col-md-3">
            <label class="form-label">Cataloguswaarde (€)</label>
            <input type="number" step="0.01" class="form-control" name="<?=$pref?>[cataloguswaarde]" value="<?=h($v['cataloguswaarde'])?>" placeholder="bv. 28000">
          </div>

          <div class="col-md-2">
            <label class="form-label">Aankoop (€)</label>
            <input type="number" step="0.01" class="form-control" name="<?=$pref?>[aankoop]" value="<?=h($v['aankoop'])?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Restwaarde (€)</label>
            <input type="number" step="0.01" class="form-control" name="<?=$pref?>[restwaarde]" value="<?=h($v['restwaarde'])?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Horizon (jaren)</label>
            <input type="number" class="form-control" name="<?=$pref?>[horizon_jaren]" value="<?=h($v['horizon_jaren'])?>">
          </div>
        </div>
      </div>

      <div class="mt-2">
        <button class="btn btn-sm btn-outline-light" type="button" onclick="document.getElementById('adv-<?=h($idx)?>').classList.toggle('show')">Extra velden</button>
      </div>
    </div>
  </div>
  <?php
  return ob_get_clean();
}
?>
