# Changelog — Smart Mentions (kondomanager/mention)

Tutte le modifiche degne di nota apportate a questa estensione saranno documentate in questo file.
Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.0.0/) e aderisce al [Semantic Versioning](https://semver.org/).

---

## [Unreleased]
### In programma
- Consultare il file [`ROADMAP.md`](ROADMAP.md) per l'elenco completo dei task e bug in fase di lavorazione.

---

## [1.0.0-b2] - 2026-09-25

### Risolto (Fixed)
- **Placeholder errato stringa di notifica (`NOTIFICATION_MENTION`, Task SM-02)**:
  - Rimosso il placeholder `%2$s` dalle stringhe in `language/it/notification.php` e `language/en/notification.php`.
  - In phpBB `\phpbb\notification\type\post::get_title()` il secondo parametro passato è `$responders_cnt` (il conteggio numerico, tipicamente `1`), non il titolo della discussione (che viene già renderizzato separatamente da `get_reference()`).
  - Corretta la frase in *"Sei stato menzionato da %1$s in:"* (IT) e *"You were mentioned by %1$s in:"* (EN), eliminando la dicitura anomala *"menzionato in: 1"*.

---

## [1.0.0-b1] - 2026-09-25

### Risolto (Fixed)
- **PHP Warning `Undefined array key "post_subject"` e blocco invio post**:
  - Risolto errore critico durante la pubblicazione e modifica dei post: in phpBB `$data` non include `post_subject`, che risiede invece in `$event['subject']`.
  - Risolto conseguente blocco PHP Warning `Cannot modify header information - headers already sent` dovuto all'output inviato prima del redirect.
  - Aggiunti controlli di fallback sicuri per `post_subject`, `topic_title` e `post_username` nel listener eventi.
  - Aggiunto controllo `isset($post['post_subject'])` difensivo in `create_insert_array()` per prevenire warning su chiamate esterne.
  - Cast esplicito a stringa dei parametri in `html_entity_decode()` per evitare deprecation warning su PHP 8.1+.
- **Caricamento lingua nel Pannello di Controllo Utente (UCP)**:
  - Agganciato evento `core.user_setup` per registrare i file di lingua globalmente, garantendo la visualizzazione corretta delle opzioni di notifica menzioni nel pannello utente.
- **Ripristino template email menzioni**:
  - Ripristinata la corretta associazione del template email `@kondomanager_mention/user_mention` sia in lingua inglese che italiana.
- **Sessione utente durante autocomplete**:
  - Aggiunto `credentials: 'same-origin'` e header `X-Requested-With: XMLHttpRequest` nelle chiamate fetch JavaScript per evitare disconnessioni accidentali della sessione utente phpBB.
- **Posizionamento caret autocomplete**:
  - Risolto allineamento orizzontale e verticale del popup dei suggerimenti in modo da seguire dinamicamente la posizione del cursore nella textarea.

### Aggiunto (Added)
- **Integrazione completa notifiche di sistema**:
  - Notifiche sia a schermo (campanella) che via email configurate come attive di default, con possibilità di personalizzazione nel Pannello Utente (UCP).
  - File email bilingue (`language/it/email/user_mention.txt` e `language/en/email/user_mention.txt`).
- **Interfaccia moderna Autocomplete**:
  - Stile Glassmorphism per il dropdown con blur, ombreggiatura morbida e freccia di ancoraggio.
  - Supporto navigazione con tastiera (`Freccia Su`, `Freccia Giù`, `Invio`, `Tab`, `Esc`).
- **Protezione Anti-Abuso e Sicurezza**:
  - Limite massimo configurato a 15 menzioni per post per prevenire flooding e abusi.
  - Parsing XML sicuro tramite `DOMDocument` per evitare injection nel testo s9e.
  - Filtro dei permessi di lettura forum prima dell'invio delle notifiche per evitare disclosure di messaggi riservati.
