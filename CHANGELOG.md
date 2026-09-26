# Changelog — Smart Mentions (kondomanager/mention)

All notable changes to this extension are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

---

## [Unreleased]
### Planned
- See [`ROADMAP.md`](ROADMAP.md) for the active task board and upcoming improvements.

---

## [1.0.0-b5] - 2026-09-26

### Added / Fixed
- **Support for Unicode and accented characters in usernames (Task SM-04)**:
  - Updated the s9e TextFormatter `username` attribute filter in `main_listener.php` with the Unicode character class `/^[\p{L}\p{N} _\-\.]{1,50}$/u`.
  - Updated the s9e `Preg->match` pattern with Unicode lookbehind and capture group: `/(?J)(?<![\p{L}\p{N}])@(?:"(?<username>[^"]{1,50})"|(?<username>[\p{L}\p{N}_\-\.]{1,50}))/u`.
  - Updated the input detection regex in `mention_autocomplete.js` with the Unicode flag: `/(^|[^\p{L}\p{N}])@([\p{L}\p{N}_\-\.]*)$/u`.
  - Provides full support for international and accented usernames (e.g., `Nicolò`, `René`, `Müller`, `José`), including automatic URL-encoding in profile links and real-time notification delivery.

---

## [1.0.0-b4] - 2026-09-25

### Fixed
- **Autocomplete preserves `@` symbol upon suggestion selection (Task SM-01)**:
  - Rewrote caret index calculation (`mentionAtIndex` and `mentionEndIndex`) in `mention_autocomplete.js`.
  - `insertMention()` now explicitly reconstructs the mention token with the `@` prefix (e.g., `@username` or `@"First Last"` for names with spaces).
  - Guarantees the `@` symbol is never stripped when selecting via mouse click or keyboard (`Enter` / `Tab`), allowing phpBB's s9e engine to reliably parse the text into interactive mention links and dispatch notifications.

---

## [1.0.0-b3] - 2026-09-25

### Added
- **Quick Reply box support (Task SM-03)**:
  - Extended JavaScript selectors in `mention_autocomplete.js` to target both standard full editor (`textarea#message`) and Quick Reply editor (`textarea[name="message"]`).
  - Implemented dynamic caret coordinate calculation and popup anchoring across any active textarea.
  - Fully tested and validated on **prosilver** and **prosilver Special Edition**.
- **Click-outside popup dismissal (Task SM-05)**:
  - The autocomplete suggestions dropdown closes immediately when clicking outside the active textarea or the popup menu.

---

## [1.0.0-b2] - 2026-09-25

### Fixed
- **Notification string placeholder bug (`NOTIFICATION_MENTION`, Task SM-02)**:
  - Removed incorrect `%2$s` placeholder in `language/it/notification.php` and `language/en/notification.php`.
  - In phpBB `\phpbb\notification\type\post::get_title()`, the second parameter is `$responders_cnt` (an integer count, usually `1`), not the topic title (which is already rendered separately by `get_reference()`).
  - Fixed strings to *"You were mentioned by %1$s in:"* (EN) and *"Sei stato menzionato da %1$s in:"* (IT), eliminating the awkward *"mentioned in: 1"* output.

---

## [1.0.0-b1] - 2026-09-25

### Added
- Initial beta release of Smart Mentions for phpBB 3.3.x.
- Real-time `@username` autocomplete with glassmorphism UI.
- Native board bell notifications and email notifications.
- Quoted usernames support for names with spaces: `@"First Last"`.
- Rate-limiting & safe DOM parsing for s9e TextFormatter.
