# Development Roadmap — Smart Mentions (kondomanager/mention)

This document tracks known issues, planned improvements, edge cases, priorities, and local test plans for validation before production deployment.

---

## 🎯 Task Board by Milestone

### 📦 Milestone 1: Beta Stabilization (v1.0.0-b1 — v1.0.0-b10) — `COMPLETED`
Focus: Core functionality, edge cases, notification lifecycle, and security hardening.

| ID | Priority | Module | Summary | Status |
|:---|:---|:---|:---|:---|
| **SM-01** | 🔴 High | JS / Autocomplete | Autocomplete removes `@` character when selecting a suggestion | `COMPLETED` (v1.0.0-b4) |
| **SM-02** | 🔴 High | Language | Incorrect notification string: `%2$s` outputs count instead of topic ("mentioned in: 1") | `COMPLETED` (v1.0.0-b2) |
| **SM-03** | 🟡 Medium | JS / Template | Quick Reply support (bottom of topics) | `COMPLETED` (v1.0.0-b3) |
| **SM-04** | 🟡 Medium | PHP / Regex | Support Unicode and accented characters in usernames (`Nicolò`, `René`) | `COMPLETED` (v1.0.0-b5) |
| **SM-05** | 🟡 Medium | JS / UX | Dismiss autocomplete dropdown on outside click | `COMPLETED` (v1.0.0-b3) |
| **SM-06** | 🟡 Medium | PHP / Notifications | Clean up orphaned notifications upon post deletion (`core.delete_posts_before`) | `COMPLETED` (v1.0.0-b6) |
| **SM-07** | 🟢 Low | PHP / Permissions | Unapproved posts in moderation queue: do not trigger notifications until approved | `COMPLETED` (v1.0.0-b7) |
| **SM-08** | 🟢 Low | PHP / Controller | Autocomplete endpoint hardening: check `u_viewprofile` and escape SQL wildcards | `COMPLETED` (v1.0.0-b8) |
| **SM-09** | 🟢 Low | PHP / Parser | Prevent duplicate mention notifications inside `[quote]` blocks | `COMPLETED` (v1.0.0-b9) |
| **SM-10** | 🟢 Low | PHP / Template | URL-encode `@username` in profile link for usernames with spaces | `COMPLETED` (v1.0.0-b10) |

---

### 🚀 Milestone 2: Release Candidate 1 (v1.0.0-RC1) — `IN PROGRESS`
Focus: Feature freeze, UX polish, dark themes, and mobile usability for public community testing.

| ID | Priority | Module | Summary | Status |
|:---|:---|:---|:---|:---|
| **SM-11** | 🔴 High | CSS / Themes | Dark theme & Dark Mode support (`@media (prefers-color-scheme: dark)` & dark styles) | `TO DO` |
| **SM-12** | 🟡 Medium | Mobile / UX | Mobile & touch interaction polish (virtual keyboard tap handling) | `TO DO` |
| **SM-13** | 🟡 Medium | PHP / Events | Private Messages (PM) mentions handling and consistency | `TO DO` |
| **SM-16** | 🟢 Low | Language | Language packs audit & verification (`en` and `it`) | `TO DO` |

---

### 🏆 Milestone 3: Final Release (v1.0.0 Stable / phpBB CDB Submission) — `PLANNED`
Focus: Official phpBB validation, zero warnings, clean packaging, and official submission.

| ID | Priority | Module | Summary | Status |
|:---|:---|:---|:---|:---|
| **SM-15** | 🔴 High | QA / Validation | Official phpBB Extension Pre-Validator (EPV) compliance audit (0 errors, 0 warnings) | `TO DO` |
| **SM-17** | 🟡 Medium | Packaging | Clean production `.zip` packaging (no `.DS_Store`, no `.git`, correct hierarchy) | `TO DO` |
| **SM-18** | 🟢 Low | Release | phpBB Customisation Database (CDB) submission for Extension Team review | `TO DO` |
| **SM-19** | 🟢 Low | Deploy | Deploy stable release to official forum (`kondomanager-forum`) via Coolify | `TO DO` |

---

### 🔮 Milestone 4: Future Feature Pack (v1.1.0) — `FUTURE`
Focus: Advanced administrator configuration and high-value community enhancements (maintaining zero-config simplicity for v1.0.0).

| ID | Priority | Module | Summary | Status |
|:---|:---|:---|:---|:---|
| **SM-14** | 🟢 Low | ACP / Config | ACP settings page (configurable max mentions per post, custom `u_mention` permissions, badge styling) | `PLANNED (v1.1.0)` |
| **SM-20** | 🟡 Medium | JS / UI | User Profile Hovercard (preview avatar, rank, online status on hover over `@username`) | `PLANNED (v1.1.0)` |
| **SM-21** | 🟡 Medium | PHP / Permissions | Group Mentions (`@moderators`, `@team` with role-based mention permissions) | `PLANNED (v1.1.0)` |
| **SM-22** | 🟢 Low | UCP / Topic | Per-Topic Mention Muting (allow users to silence further mention notifications in specific threads) | `PLANNED (v1.1.0)` |

---

## 📋 Task Details & Test Plans

### SM-01: Autocomplete removes `@` symbol upon selection
- **Issue:** When selecting a username suggestion from the popup, `insertMention` stripped the text before the `@` symbol, inserting only `username `. phpBB's s9e engine failed to parse it as a mention.
- **Affected Files:** `styles/all/template/js/mention_autocomplete.js`
- **Local Test Plan:**
  1. Type `@adm` in the post textarea.
  2. Click the suggested `admin` item (or press `Enter`/`Tab`).
  3. Verify that the textarea contains `@admin ` and not `admin `.
  4. Submit the post and confirm that the text renders as an active mention link and dispatches a notification.

---

### SM-02: Incorrect notification string (`NOTIFICATION_MENTION`)
- **Issue:** In phpBB `\phpbb\notification\type\post`, `%2$s` represents `$responders_cnt` (an integer count, usually `1`), while the topic title is already rendered by `get_reference()`. This produced awkward strings like *"You were mentioned by Admin in: 1"*.
- **Affected Files:** `language/it/notification.php`, `language/en/notification.php`
- **Local Test Plan:**
  1. Mention a test user in a topic.
  2. Log in as the mentioned user and open the notification bell dropdown.
  3. Verify clean wording (e.g., *"You were mentioned by Admin in:"* followed by the topic title below).

---

### SM-03: Quick Reply editor support
- **Issue:** The JavaScript selector only queried `document.getElementById('message')`. In prosilver's `quickreply_editor.html`, the textarea has `name="message"` but lacks an `id`. The autocomplete did not trigger in Quick Reply.
- **Affected Files:** `styles/all/template/js/mention_autocomplete.js`
- **Local Test Plan:**
  1. Open an existing topic and scroll down to the Quick Reply box.
  2. Type `@` followed by two letters.
  3. Verify that the autocomplete dropdown appears correctly anchored to the caret inside Quick Reply.

---

### SM-04: Unicode and accented characters in usernames
- **Issue:** Regex patterns in `main_listener.php` used strict ASCII ranges (`[a-zA-Z0-9_\-\.]`) without the `/u` modifier. Usernames such as `Nicolò` or `René` were truncated or ignored.
- **Affected Files:** `event/main_listener.php`
- **Local Test Plan:**
  1. Create a test user with accented letters (e.g., `Nicolò`).
  2. Compose a post containing `@Nicolò`.
  3. Verify that s9e TextFormatter transforms the full word into a mention link and delivers the notification.

---

### SM-05: Dismiss autocomplete popup on outside click
- **Issue:** When the autocomplete dropdown is open, clicking anywhere else on the page did not dismiss it until `Escape` was pressed.
- **Affected Files:** `styles/all/template/js/mention_autocomplete.js`
- **Local Test Plan:**
  1. Type `@ad` to display the dropdown.
  2. Click anywhere outside the textarea and dropdown.
  3. Verify that the dropdown closes immediately.

---

### SM-06: Clean up orphaned notifications on post deletion
- **Issue:** When a post containing mentions is deleted, its notification remains in phpBB's notification list. Clicking it leads to a 404 / post not found error.
- **Affected Files:** `event/main_listener.php`
- **Local Test Plan:**
  1. Mention a user in a post and verify notification receipt.
  2. Delete the post as moderator or author.
  3. Verify that the notification is automatically removed from the database and user bell dropdown.

---

### SM-07: Posts in moderation queue
- **Issue:** If a post requires moderator approval (`post_visibility != ITEM_APPROVED`), mentions trigger notifications before the post is reviewed and approved.
- **Affected Files:** `event/main_listener.php`
- **Local Test Plan:**
  1. Submit a post with a user in the newly registered / moderated queue.
  2. Verify that mention notifications are deferred until the post is approved.

---

### SM-08: Autocomplete endpoint hardening (Permissions & SQL Wildcards)
- **Issue:** `/mention/autocomplete` does not verify `u_viewprofile` permissions, and the SQL `LIKE` query does not escape `%` or `_` characters.
- **Affected Files:** `controller/autocomplete.php`
- **Local Test Plan:**
  1. Request autocomplete as an anonymous guest on a forum where memberlist viewing is restricted: verify it returns `[]`.
  2. Query `@_` or `@%` and verify SQL executes properly without runaway wildcards.

---

### SM-09: Duplicate mentions inside `[quote]` blocks
- **Issue:** Quoting a post containing a mention re-evaluates `<MENTION>` tags, sending another notification to the mentioned user.
- **Affected Files:** `event/main_listener.php`
- **Local Test Plan:**
  1. User A mentions User B.
  2. User C quotes User A's post.
  3. Verify that User B does not receive a second notification solely from being quoted.

---

### SM-10: URL-encoding usernames with spaces
- **Issue:** Mentioning `@"First Last"` generates an unencoded space in `memberlist.php?mode=viewprofile&un={@username}`.
- **Affected Files:** `event/main_listener.php`
- **Local Test Plan:**
  1. Mention a user with spaces in their name: `@"Jane Doe"`.
  2. Click the profile link in the posted message and verify the URL is valid and navigates properly.

---

### SM-11: Dark Themes & Dark Mode Support
- **Issue:** `mention.css` currently uses hardcoded light background colors and borders. On dark themes or when `prefers-color-scheme: dark` is active, the popup and mention badge clash with the dark background.
- **Affected Files:** `styles/all/template/css/mention.css`
- **Local Test Plan:**
  1. Test with OS / browser set to dark mode.
  2. Verify autocomplete dropdown renders with dark background, high-contrast readable text, and elegant borders.
  3. Verify `.mention` link badge in posts is readable and aesthetically harmonious against dark post bodies.

---

### SM-12: Mobile & Touch Interaction Polish
- **Issue:** On mobile devices (iOS Safari, Android Chrome), tapping a suggestion from the dropdown while the software keyboard is active might trigger blur or close prematurely on certain viewport resizes.
- **Affected Files:** `styles/all/template/js/mention_autocomplete.js`
- **Local Test Plan:**
  1. Emulate touch device / mobile screen in browser.
  2. Type `@ad` to open suggestions.
  3. Tap the item and verify suggestion is inserted smoothly without losing focus or causing keyboard flicker.

---

### SM-13: Private Messages (PM) Mentions Handling
- **Issue:** Mentions in Private Messages (`ucp.php?i=pm`) currently invoke autocomplete and s9e formatting, but PM notification dispatch needs explicit architectural definition (whether to send board notifications for PM mentions or restrict them to public posts).
- **Affected Files:** `event/main_listener.php`
- **Local Test Plan:**
  1. Compose a PM mentioning a user.
  2. Verify consistent behavior and absence of notification permission leaks across private folders.

---

### SM-14: ACP Configuration Settings Page
- **Issue:** Settings like max mentions per post (currently hardcoded to 10) cannot be adjusted by forum administrators without editing code. Planned for v1.1.0 to keep v1.0.0 zero-config.
- **Affected Files:** `acp/`, `config/services.yml`, `migrations/`
- **Local Test Plan:**
  1. Open ACP -> Extensions -> Smart Mentions.
  2. Adjust max mentions setting and save.
  3. Submit a post with exceeding mentions and verify limit enforcement.

---

### SM-15: Extension Pre-Validator (EPV) Compliance
- **Issue:** Preparation for official phpBB Extension Database submission requires running phpBB EPV to guarantee zero notices, strict standard compliance, and correct packaging.
- **Affected Files:** Entire repository
- **Local Test Plan:**
  1. Run `phpbb/epv` against repository.
  2. Fix any flagged warnings, missing docblocks, or code style deviations.

---

### SM-16: Language Pack Audit & Verification
- **Issue:** Ensure all language files (`language/en/` and `language/it/`) contain complete, matching keys without missing translations.
- **Affected Files:** `language/en/`, `language/it/`
- **Local Test Plan:**
  1. Compare translation key trees between `en` and `it`.
  2. Verify all notification texts, tooltips, and labels render correctly in both languages.

---

### SM-20: User Profile Hovercard (v1.1.0)
- **Feature:** Display an interactive mini-card when hovering over an `@username` mention in a post (avatar, username, group badge, online status, registration date, and direct profile / PM links).
- **Affected Files:** `styles/all/template/js/`, `styles/all/template/css/`, `controller/`
- **Local Test Plan:**
  1. Hover over a rendered `@mention` link in a topic.
  2. Verify a polished tooltip/card appears with cached user profile info without sluggish DB roundtrips.

---

### SM-21: Group Mentions (v1.1.0)
- **Feature:** Allow authorized users (e.g. staff/moderators) to mention an entire usergroup (e.g. `@moderators`, `@support-team`), notifying all group members at once with permission checks to prevent abuse.
- **Affected Files:** `event/main_listener.php`, `notification/type/mention.php`
- **Local Test Plan:**
  1. As administrator, post `@Moderatori`.
  2. Verify all members of the moderators group receive a single notification.
  3. As regular user, verify group mention syntax is either restricted or does not trigger mass spam.

---

### SM-22: Per-Topic Mention Muting (v1.1.0)
- **Feature:** Allow users mentioned in very active discussions to mute/opt-out of further mention alerts for that specific topic while still following the general forum.
- **Affected Files:** `event/main_listener.php`, `notification/type/mention.php`, `migrations/`
- **Local Test Plan:**
  1. In a topic where a user was mentioned, click "Mute mentions for this topic".
  2. Subsequent posts mentioning the user in that topic do not generate notifications.
