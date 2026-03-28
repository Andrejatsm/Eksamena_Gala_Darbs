# Saprasts - Psiholoģiskā atbalsta un konsultāciju platforma

**Saprasts** ir tīmekļa platforma, kas izstrādāta kā kvalifikācijas darbs. Tās mērķis ir nodrošināt ērtu un drošu vidi, kurā klienti var atrast sertificētus psihologus, pieteikties konsultācijām, veikt pašnovērtējuma testus un saņemt AI ģenerētus ieteikumus mentālās veselības uzlabošanai.

<img width="1920" height="3331" alt="bilde" src="https://github.com/user-attachments/assets/b25ef1c3-ce3d-4661-bba0-845aed236a38" />

##  Galvenās funkcijas

Sistēma ir sadalīta 3 galvenajās lietotāju lomās:

**Klientiem (Lietotājiem):**
* Psihologu meklēšana un profilu apskate.
* Ērta pieteikšanās klātienes vai tiešsaistes vizītēm.
* Droša apmaksa, izmantojot Stripe integrāciju.
* Psiholoģisko pašnovērtējuma testu (piemēram, PHQ-9, GAD-7) pildīšana.
* Saziņa ar integrēto Gemini AI aģentu ikdienas ieteikumiem un atbalstam.

<img width="1920" height="2439" alt="bilde2" src="https://github.com/user-attachments/assets/18a06d88-52b4-43a5-8db8-03cb812f59c9" />


**Psihologiem:**
* Sava profila, specializācijas un pakalpojumu cenu pārvaldība.
* Pieejamības grafika (slotu) veidošana un rediģēšana.
* Klientu pierakstu apstiprināšana, noraidīšana vai pārcelšana.
* Informatīvu rakstu publicēšana platformas apmeklētājiem.

<img width="1905" height="1221" alt="localhost_psihologi_specialist_dashboard php" src="https://github.com/user-attachments/assets/7d421f23-7a92-4dac-a319-c03a2867ce3d" />


**Administratoram:**
* Vispārēja lietotāju kontu pārvaldība (bloķēšana/dzēšana).
* Jaunu psihologu profilu verificēšana un apstiprināšana platformā.

<img width="1920" height="1790" alt="bilde3" src="https://github.com/user-attachments/assets/9ae75f53-7320-4425-badb-f9eeef8d1bff" />

##  Izmantotas tehnaloģijas un ietvari

* **Front-end:** HTML5, CSS3, Bootstrap 5, JavaScript
* **Back-end:** PHP 8+ (strukturēts bez ietvara, izmantojot sesijas un drošu validāciju)
* **Datubāze:** MySQL (Relāciju datubāze ar ārējām atslēgām un integritāti)
* **Ārējie API:**
  * **Stripe API:** Maksājumu apstrādei.
  * **Google Gemini API:** AI aģenta funkcionalitātei.

## Papildus informācijai

<img width="1905" height="1334" alt="bilde5" src="https://github.com/user-attachments/assets/8c3fffc3-26f7-4a78-9e89-39d595a8affd" />


