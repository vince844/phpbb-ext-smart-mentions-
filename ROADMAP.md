# Roadmap di Sviluppo — Smart Mentions (kondomanager/mention)

Questo documento traccia i bug noti, le migliorie pianificate e i casi limite dell'estensione, con priorità e relativi piani di test per la validazione in ambiente locale prima del deploy in produzione.

---

## 🎯 Task Board

| ID | Priorità | Modulo | Descrizione sintetica | Stato |
|:---|:---|:---|:---|:---|
| **SM-01** | 🔴 Alta | JS / Autocomplete | Autocomplete rimuove il carattere `@` alla selezione dal dropdown | `DA FARE` |
| **SM-02** | 🔴 Alta | Lingua | Stringa di notifica errata: `%2$s` stampa conteggio anziché titolo ("menzionato in: 1") | `DA FARE` |
| **SM-03** | 🟡 Media | JS / Template | Supporto Quick Reply (Risposta Rapida in basso nei topic) | `DA FARE` |
| **SM-04** | 🟡 Media | PHP / Regex | Supporto caratteri Unicode e lettere accentate negli username (`Nicolò`, `René`) | `DA FARE` |
| **SM-05** | 🟡 Media | JS / UX | Chiusura menu autocomplete al click fuori dalla textarea | `DA FARE` |
| **SM-06** | 🟡 Media | PHP / Notifiche | Pulizia notifiche orfane alla cancellazione di un post (`core.delete_posts_before`) | `DA FARE` |
| **SM-07** | 🟢 Bassa | PHP / Permessi | Post in coda di moderazione: non inviare menzioni prima dell'approvazione | `DA FARE` |
| **SM-08** | 🟢 Bassa | PHP / Controller | Protezione endpoint autocomplete: permessi `u_viewprofile` ed escaping wildcard SQL | `DA FARE` |
| **SM-09** | 🟢 Bassa | PHP / Parser | Evitare notifiche duplicate se la menzione è all'interno di un `[quote]` | `DA FARE` |
| **SM-10** | 🟢 Bassa | PHP / Template | URL-encoding di `@username` nel link del profilo generato per username con spazi | `DA FARE` |

---

## 📋 Dettaglio dei Task e Piani di Test

### SM-01: Autocomplete rimuove il carattere `@` alla selezione
- **Problema:** Quando l'utente seleziona uno username suggerito dal menu popup, la funzione `insertMention` taglia la stringa prima della chiocciola e inserisce solo `username `, perdendo il simbolo `@`. Il motore s9e di phpBB non rileva il testo come menzione.
- **File coinvolti:** `styles/all/template/js/mention_autocomplete.js`
- **Piano di Test Locale:**
  1. Digitare `@adm` nella textarea di un post.
  2. Cliccare sulla voce suggerita `admin` (o premere `Invio`/`Tab`).
  3. Verificare che nel messaggio appaia `@admin ` e non `admin `.
  4. Pubblicare il post e verificare che il testo diventi un link azzurro evidenziato e la notifica venga recapitata.

---

### SM-02: Stringa di notifica errata (`NOTIFICATION_MENTION`)
- **Problema:** `%2$s` nei file di lingua per `\phpbb\notification\type\post` corrisponde a `$responders_cnt` (un intero, tipicamente `1`), mentre il titolo del topic viene già renderizzato da `get_reference()`. Attualmente genera la frase *"Sei stato menzionato da Admin in: 1"*.
- **File coinvolti:** `language/it/notification.php`, `language/en/notification.php`
- **Piano di Test Locale:**
  1. Menzionare un utente di test in una discussione.
  2. Accedere con l'utente menzionato e aprire la tendina delle notifiche (icona campana).
  3. Verificare che il testo visualizzato sia pulito (es. *"Sei stato menzionato da Admin nella discussione:"* seguito dal titolo sotto tra virgolette).

---

### SM-03: Supporto al box Risposta Rapida (Quick Reply)
- **Problema:** Il selettore JavaScript cerca solo `document.getElementById('message')`. Nel template prosilver standard `quickreply_editor.html`, la textarea ha `name="message"` ma non ha l'`id`. L'autocomplete non si apre nel quick reply.
- **File coinvolti:** `styles/all/template/js/mention_autocomplete.js`
- **Piano di Test Locale:**
  1. Aprire un topic esistente e scorrere in fondo al box "Risposta Rapida".
  2. Digitare `@` seguito da due lettere.
  3. Verificare che il dropdown appaia correttamente sopra/sotto il cursore anche nel quick reply.

---

### SM-04: Supporto caratteri Unicode e lettere accentate
- **Problema:** I pattern regex in `main_listener.php` usano classi strettamente ASCII (`[a-zA-Z0-9_\-\.]`) senza modificatore `/u`. Username come `Nicolò` o `René` vengono troncati o scartati.
- **File coinvolti:** `event/main_listener.php`
- **Piano di Test Locale:**
  1. Creare un utente di test con accento (es. `utente_città`).
  2. Scrivere un messaggio contenente `@utente_città`.
  3. Verificare che s9e TextFormatter trasformi l'intera parola in link di menzione e che la notifica arrivi.

---

### SM-05: Chiusura dropdown autocomplete su click esterno
- **Problema:** Se il menu a tendina dell'autocomplete è aperto e l'utente clicca altrove nella pagina (fuori dalla textarea), il popup rimane bloccato a video finché non viene premuto `Esc`.
- **File coinvolti:** `styles/all/template/js/mention_autocomplete.js`
- **Piano di Test Locale:**
  1. Digitare `@ad` per far comparire il popup.
  2. Cliccare con il mouse in un punto vuoto della pagina.
  3. Verificare che il popup si chiuda immediatamente.

---

### SM-06: Cancellazione notifiche orfane alla cancellazione post
- **Problema:** Quando un post con menzioni viene eliminato, la notifica rimane nel database di phpBB e nella campanella dell'utente. Cliccandoci si ottiene un errore 404/post non trovato.
- **File coinvolti:** `event/main_listener.php`
- **Piano di Test Locale:**
  1. Menzionare un utente in un post.
  2. Verificare che la notifica compaia nella campanella del destinatario.
  3. Da moderatore o autore, cancellare il post.
  4. Ricaricare la pagina con l'utente menzionato: verificare che la notifica sia stata rimossa automaticamente dal DB.

---

### SM-07: Post in coda di moderazione
- **Problema:** Se un messaggio richiede approvazione (`post_visibility != ITEM_APPROVED`), la menzione viene inviata subito via email e campanella prima dell'approvazione del moderatore.
- **File coinvolti:** `event/main_listener.php`
- **Piano di Test Locale:**
  1. Inviare un post con un utente che ha i messaggi in coda di moderazione.
  2. Verificare che la notifica **non** parta finché il post non viene approvato.

---

### SM-08: Sicurezza Autocomplete (Permessi & SQL Wildcard)
- **Problema:** L'endpoint `/mention/autocomplete` non controlla `u_viewprofile` e la clausola SQL `LIKE` non effettua l'escape dei caratteri `%` e `_`.
- **File coinvolti:** `controller/autocomplete.php`
- **Piano di Test Locale:**
  1. Effettuare una chiamata da utente anonimo quando il forum vieta la visualizzazione iscritti: verificare che risponda array vuoto `[]`.
  2. Digitare `@_` o `@%` e verificare che non esegua wildcard SQL incontrollate.

---

### SM-09: Menzioni duplicate all'interno dei Quote
- **Problema:** Quando si cita un messaggio con `[quote]`, i tag `<MENTION>` citati vengono nuovamente conteggiati, notificando nuovamente gli utenti.
- **File coinvolti:** `event/main_listener.php`
- **Piano di Test Locale:**
  1. L'utente A menziona l'utente B.
  2. L'utente C fa "Cita" del post di A.
  3. Verificare che B non riceva una seconda notifica di menzione per il solo fatto di essere stato citato nel quote.

---

### SM-10: URL-encoding degli username con spazi
- **Problema:** Quando si menziona `@"Nome Cognome"`, l'attributo `href="memberlist.php?mode=viewprofile&un={@username}"` genera uno spazio non codificato nell'URL.
- **File coinvolti:** `event/main_listener.php`
- **Piano di Test Locale:**
  1. Menzionare un utente con spazi nel nome: `@"Mario Rossi"`.
  2. Cliccare sul link generato nel post: verificare che reindirizzi correttamente al profilo senza generare URL malformati.
