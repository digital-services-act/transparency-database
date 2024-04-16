#### [Annuncio - API] Ripristino dell'errore 422 per uid della piattaforma duplicati (puid)
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Pubblicato il 08/04/2024</p>
L'8 aprile 2024, la Commissione europea ha ripristinato l'errore 422 - Contenuto non elaborabile. Il punto finale dell'API restituirà l'errore ogni volta che una piattaforma invia una Dichiarazione di Motivazioni (SoR) contenente un identificatore univoco della piattaforma (puid) già presente nella SoR precedentemente inviata al database dalla stessa piattaforma.

Si prega di fare riferimento alla [sezione Errori](/page/api-documentation#errors) della [documentazione dell'API](/page/api-documentation) per ulteriori dettagli.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Cambiamento - API] Impostazione del formato platform_uid (puid)
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Pubblicato il 08/04/2024</p>

A partire dal 18 aprile 2024, il team del Database di Trasparenza imporrà il formato dell'Identificatore Unico della Piattaforma (puid) come stringa di massimo 500 caratteri contenente solo caratteri alfanumerici (a-z, A-Z, 0-9), trattini "-" e underscore "_". Non saranno accettati spazi, nuove righe o altri caratteri speciali.

Ad esempio, il puid “344ndbd_3338383-11aST" sarà valido, mentre il puid “123.STATE sor/category” non lo sarà.

Si consiglia di consultare la [documentazione dell'API](/page/api-documentation#additional-explanation-for-statement-attributes) per ulteriori dettagli.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Cambiamento – Accesso ai dati] Miglioramento del formato dei file di estrazione giornaliera
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Pubblicato il 08/04/2024</p>

L'8 aprile 2024, il team del Database di Trasparenza (TDB) sta aggiornando la struttura dei file CSV delle estrazioni giornaliere disponibili nella sezione [Download dei dati](/data-download). Questo cambiamento mira a migliorare la creazione dei file CSV delle estrazioni giornaliere per velocizzarla e renderla più efficiente dal punto di vista computazionale. Ciò consentirà di pubblicare i file CSV in modo rapido e tempestivo anche con l'attuale elevato volume giornaliero di Dichiarazioni di Motivazioni (SoR) inviate al TDB, il quale si prevede aumenterà ulteriormente nei prossimi mesi con l'adesione di piccole piattaforme.

La nuova struttura sarà costituita da un file zip, con diversi file zip al suo interno.
Ogni file zip interno conterrà al massimo 1 milione di record suddivisi in parti CSV di 100.000.

Ad esempio, la versione leggera dell'estrazione globale per il 25 settembre 2024 -chiamata sor-global-2023-09-25-light.zip-, conterrà diversi file zip con nomi come sor-global-2023-09-25-light-00000.csv.zip. Ciascuno di questi conterrà diverse parti CSV, con circa 100.000 SoR ciascuna, chiamate sor-global-2023-09-25-light-00000-00000.csv.

I file nel vecchio formato verranno gradualmente sostituiti dal nuovo formato nei giorni successivi.

Anche se l'attuale implementazione può gestire facilmente il volume attuale delle presentazioni giornaliere, il team TDB si riserva il diritto di apportare ulteriori modifiche alla struttura dei file o al processo di creazione, qualora sia necessario migliorare ulteriormente la gestione del tasso di presentazione giornaliero in aumento.

<p class="ecl-u-type-paragraph" style="margin-top:54px; margin-bottom:24px"><hr/></p>

#### [Annuncio – Accesso ai dati] Implementazione di una nuova politica di conservazione dei dati
<p class="ecl-u-type-paragraph" style="margin-top:-20px; font-style: italic !important">Pubblicato il 08/04/2024</p>

A partire dal 15 aprile 2024, il Database di Trasparenza (TDB) seguirà la [politica di conservazione dei dati](/page/data-retention-policy) stabilita dalla Commissione europea. In particolare, ogni Dichiarazione di Motivazioni (SoR) sarà ricercabile dalla [Ricerca di Dichiarazioni di Motivazioni](/statement) nei sei mesi (180 giorni) successivi alla sua inserzione nel database. Dopo questo periodo, la SoR verrà rimossa dall'indice di ricerca e sarà disponibile nei file CSV delle [estrazioni giornaliere](/data-download) e continuerà a contribuire al [Dashboard](/dashboard).

I [file di estrazione giornaliera](/data-download) saranno disponibili per 18 mesi (540 giorni) dopo la loro creazione. Dopo questo periodo, saranno archiviati in una memoria a freddo.

Infine, il [Dashboard](/dashboard) conterrà le statistiche aggregate degli ultimi 5 anni di dati.

<p class="ecl-u-type-paragraph" style="font-style: italic">
<img width="100%" src="{{asset('/static/images/dsa-retention-policy.png')}}">
</p>
<p class="ecl-u-type-paragraph" style="width:100%; text-align:center; font-style: italic !important; margin-top:-20px"><span style="font-size: smaller">La politica di conservazione dei dati del Database di Trasparenza del DSA.</span></p>

<p class="ecl-u-type-paragraph" style="margin-bottom:100px"></p>
