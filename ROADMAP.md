# Development Roadmap — Smart Mentions (kondomanager/mention)

This document tracks known issues, planned improvements, edge cases, priorities, and local test plans for validation before production deployment.

---

## 🎯 Task Board

| ID | Priority | Module | Summary | Status |
|:---|:---|:---|:---|:---|
| **SM-01** | 🔴 High | JS / Autocomplete | Autocomplete removes `@` character when selecting a suggestion | `COMPLETED` (v1.0.0-b4) |
| **SM-02** | 🔴 High | Language | Incorrect notification string: `%2$s` outputs count instead of topic ("mentioned in: 1") | `COMPLETED` (v1.0.0-b2) |
| **SM-03** | 🟡 Medium | JS / Template | Quick Reply support (bottom of topics) | `COMPLETED` (v1.0.0-b3) |
| **SM-04** | 🟡 Medium | PHP / Regex | Support Unicode and accented characters in usernames (`Nicolò`, `René`) | `COMPLETED` (v1.0.0-b5) |
| **SM-05** | 🟡 Medium | JS / UX | Dismiss autocomplete dropdown on outside click | `COMPLETED` (v1.0.0-b3) |
| **SM-06** | 🟡 Medium | PHP / Notifications | Clean up orphaned notifications upon post deletion (`core.delete_posts_before`) | `TO DO` |
| **SM-07** | 🟢 Low | PHP / Permissions | Unapproved posts in moderation queue: do not trigger notifications until approved | `TO DO` |
| **SM-08** | 🟢 Low | PHP / Controller | Autocomplete endpoint hardening: check `u_viewprofile` and escape SQL wildcards | `TO DO` |
| **SM-09** | 🟢 Low | PHP / Parser | Prevent duplicate mention notifications inside `[quote]` blocks | `TO DO` |
| **SM-10** | 🟢 Low | PHP / Template | URL-encode `@username` in profile link for usernames with spaces | `TO DO` |

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
