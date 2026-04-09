#  Saprasts - Psiholoģiskā atbalsta un konsultāciju platforma

**Saprasts** ir tīmekļa platforma, kas izstrādāta kā kvalifikācijas darbs. Tās mērķis ir nodrošināt ērtu un drošu vidi, kurā klienti var atrast sertificētus psihologus, pieteikties konsultācijām, veikt pašnovērtējuma testus un saņemt AI ģenerētus ieteikumus mentālās veselības uzlabošanai.

<img width="1920" height="3276" alt="homepgae1" src="https://github.com/user-attachments/assets/eaa75906-5fcb-4fe2-896b-8306c9c8384e" />

##  Galvenās funkcijas

Sistēma ir sadalīta 3 galvenajās lietotāju lomās:

**Klientiem (Lietotājiem):**
* Psihologu meklēšana un profilu apskate.
* Ērta pieteikšanās klātienes vai tiešsaistes vizītēm.
* Droša apmaksa, izmantojot Stripe integrāciju.
* Psiholoģisko pašnovērtējuma testu (piemēram, PHQ-9, GAD-7) pildīšana.
* Saziņa ar integrēto Gemini AI aģentu ikdienas ieteikumiem un atbalstam.

<img width="1905" height="2001" alt="userdash" src="https://github.com/user-attachments/assets/a4185fd4-fcc1-4b88-83cf-d817d1fd699d" />

**Psihologiem:**
* Sava profila, specializācijas un pakalpojumu cenu pārvaldība.
* Pieejamības grafika (slotu) veidošana un rediģēšana.
* Klientu pierakstu apstiprināšana, noraidīšana vai pārcelšana.
* Informatīvu rakstu publicēšana platformas apmeklētājiem.

<img width="1905" height="1221" alt="psihdashbord" src="https://github.com/user-attachments/assets/7762f16c-ab3d-444b-a8e9-2c77e27b9452" />

**Administratoram:**
* Vispārēja lietotāju kontu pārvaldība (bloķēšana/dzēšana).
* Jaunu psihologu profilu verificēšana un apstiprināšana platformā.

<img width="1905" height="2088" alt="admindash" src="https://github.com/user-attachments/assets/0547fe41-ff6a-4047-b041-d255668a7ae2" />

## Tehnoloģiju steks

* **Front-end:** HTML5, CSS3, Bootstrap 5, JavaScript
* **Back-end:** PHP 8+ (strukturēts bez ietvara, izmantojot sesijas un drošu validāciju)
* **Datubāze:** MySQL (Relāciju datubāze ar ārējām atslēgām un integritāti)
* **Ārējie API:**
  * **Stripe API:** Maksājumu apstrādei.
  * **Google Gemini API:** AI aģenta funkcionalitātei.
