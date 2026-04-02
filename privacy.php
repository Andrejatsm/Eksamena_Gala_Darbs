<?php 
$pageTitle = "Privātuma Politika";
require 'includes/header.php'; 
?>

<div class="flex-grow ui-container py-12">
    
    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-8 text-center">
        Privātuma Politika
    </h1>
    
    <div class="ui-card p-8 md:p-10">
        
        <p class="text-lg text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
            Mūsu mērķis ir nodrošināt caurspīdīgu un drošu datu apstrādi. Šajā dokumentā aprakstīts, kā "Saprasts" ievāc, izmanto un aizsargā jūsu personas datus.
        </p>

        <hr class="my-8 border-gray-200 dark:border-zinc-700">

        <h3 class="text-xl font-bold text-primary mb-3">1. Ievāktie dati</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-3">
            Lai nodrošinātu pilnvērtīgu pakalpojumu sniegšanu un autorizāciju sistēmā, reģistrācijas brīdī mēs ievācam šādu informāciju:
        </p>
        <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 space-y-2 mb-6 ml-2">
            <li><strong class="text-gray-900 dark:text-white">Personas identifikācija:</strong> Vārds, Uzvārds;</li>
            <li><strong class="text-gray-900 dark:text-white">Kontaktinformācija:</strong> E-pasta adrese, Tālruņa numurs;</li>
            <li><strong class="text-gray-900 dark:text-white">Piekļuves dati:</strong> Lietotājvārds un parole (tiek glabāta šifrētā veidā).</li>
        </ul>

        <h3 class="text-xl font-bold text-primary mb-3 mt-8">2. Datu drošība un GDPR</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-3">
            Mēs apstrādājam datus saskaņā ar <strong class="text-gray-900 dark:text-white">GDPR (Vispārīgā datu aizsardzības regula)</strong> prasībām un Latvijas Republikas likumdošanu.
        </p>
        <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 space-y-2 mb-6 ml-2">
            <li>Visi lietotāju dati tiek pārsūtīti, izmantojot drošu <strong class="text-gray-900 dark:text-white">HTTPS</strong> protokolu.</li>
            <li>Paroles datubāzē tiek glabātas tikai <strong class="text-gray-900 dark:text-white">šifrētā (hash) formātā</strong>, kas nozīmē, ka pat sistēmas administratori tās nevar redzēt.</li>
            <li>Mēs neizpaužam jūsu datus trešajām pusēm bez jūsu piekrišanas, izņemot gadījumus, kad to pieprasa likums.</li>
        </ul>
        
        <h3 class="text-xl font-bold text-primary mb-3 mt-8">3. Maksājumu drošība (Stripe)</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-3">
            Visi maksājumi par psihologu konsultācijām tiek apstrādāti, izmantojot sertificētu maksājumu apstrādātāju <strong class="text-gray-900 dark:text-white">Stripe</strong>.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Vietne "Saprasts" <strong class="text-gray-900 dark:text-white">neievāc, neuzglabā un neapstrādā</strong> jūsu kredītkartes vai debetkartes datus savos serveros. Visa finanšu informācija tiek ievadīta tieši Stripe drošajā vidē.
        </p>
        
        <h3 class="text-xl font-bold text-primary mb-3 mt-8">4. Konfidencialitāte</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Psiholoģiskā atbalsta būtība ir uzticēšanās. Konsultāciju saturs (video zvani, čata sarakste) ir stingri konfidenciāls starp klientu un speciālistu.
        </p>

        <h3 class="text-xl font-bold text-primary mb-3 mt-8">5. Sīkdatnes (Cookies)</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Mēs izmantojam sesijas sīkdatnes (Session Cookies), lai nodrošinātu jūsu autorizāciju sistēmā (ielogošanos). Tās ir nepieciešamas sistēmas darbībai.
        </p>

        <div class="mt-10 p-5 bg-white/60 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl flex items-start gap-3">
            <i class="fas fa-info-circle text-primary mt-1"></i>
            <small class="text-gray-500 dark:text-gray-400 text-sm">
                Ja jums ir jautājumi par jūsu datu apstrādi vai vēlaties pieprasīt datu dzēšanu, lūdzu, sazinieties ar mums, izmantojot sadaļu "Kontakti" lapas apakšā.
            </small>
        </div>

    </div>
</div>

<?php require 'includes/footer.php'; ?>