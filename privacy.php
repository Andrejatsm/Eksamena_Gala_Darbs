<?php 
$pageTitle = "Privātuma Politika";
require 'header.php'; 
?>

<div class="container mt-5 flex-grow-1">
    <h2 class="mb-4 fw-bold">Privātuma Politika</h2>
    
    <div id="privacy-policy-text" class="card p-4 shadow-sm">
        <p class="lead mb-4">Mūsu mērķis ir nodrošināt caurspīdīgu un drošu datu apstrādi. Šajā dokumentā aprakstīts, kā "Saprasts" ievāc, izmanto un aizsargā jūsu personas datus.</p>

        <hr class="mb-4">

        <h4 class="fw-bold text-success">1. Ievāktie dati</h4>
        <p>Lai nodrošinātu pilnvērtīgu pakalpojumu sniegšanu un autorizāciju sistēmā, reģistrācijas brīdī mēs ievācam šādu informāciju:</p>
        <ul>
            <li><strong>Personas identifikācija:</strong> Vārds, Uzvārds;</li>
            <li><strong>Kontaktinformācija:</strong> E-pasta adrese, Tālruņa numurs;</li>
            <li><strong>Piekļuves dati:</strong> Lietotājvārds un parole (tiek glabāta šifrētā veidā).</li>
        </ul>

        <h4 class="fw-bold text-success mt-4">2. Datu drošība un GDPR</h4>
        <p>Mēs apstrādājam datus saskaņā ar <strong>GDPR (Vispārīgā datu aizsardzības regula)</strong> prasībām un Latvijas Republikas likumdošanu.</p>
        <ul>
            <li>Visi lietotāju dati tiek pārsūtīti, izmantojot drošu <strong>HTTPS</strong> protokolu.</li>
            <li>Paroles datubāzē tiek glabātas tikai <strong>šifrētā (hash) formātā</strong>, kas nozīmē, ka pat sistēmas administratori tās nevar redzēt.</li>
            <li>Mēs neizpaužam jūsu datus trešajām pusēm bez jūsu piekrišanas, izņemot gadījumus, kad to pieprasa likums.</li>
        </ul>
        
        <h4 class="fw-bold text-success mt-4">3. Maksājumu drošība (Stripe)</h4>
        <p>Visi maksājumi par psihologu konsultācijām tiek apstrādāti, izmantojot sertificētu maksājumu apstrādātāju <strong>Stripe</strong>.</p>
        <p>Vietne "Saprasts" <strong>neievāc, neuzglabā un neapstrādā</strong> jūsu kredītkartes vai debetkartes datus savos serveros. Visa finanšu informācija tiek ievadīta tieši Stripe drošajā vidē.</p>
        
        <h4 class="fw-bold text-success mt-4">4. Konfidencialitāte</h4>
        <p>Psiholoģiskā atbalsta būtība ir uzticēšanās. Konsultāciju saturs (video zvani, čata sarakste) ir stingri konfidenciāls starp klientu un speciālistu.</p>

        <h4 class="fw-bold text-success mt-4">5. Sīkdatnes (Cookies)</h4>
        <p>Mēs izmantojam sesijas sīkdatnes (Session Cookies), lai nodrošinātu jūsu autorizāciju sistēmā (ielogošanos). Tās ir nepieciešamas sistēmas darbībai.</p>

        <div class="mt-5 p-3 bg-light border rounded text-dark">
            <small>Ja jums ir jautājumi par jūsu datu apstrādi vai vēlaties pieprasīt datu dzēšanu, lūdzu, sazinieties ar mums, izmantojot sadaļu "Kontakti".</small>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>