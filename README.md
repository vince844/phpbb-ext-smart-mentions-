# Smart Mentions for phpBB 3.3.x

[![phpBB Version](https://img.shields.io/badge/phpBB-3.3.x-blue.svg)](https://www.phpbb.com)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.1.3-8892BF.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/License-GPL--2.0-green.svg)](license.txt)
[![Latest Release](https://img.shields.io/badge/Release-v1.0.0--b10-brightgreen.svg)](https://github.com/vince844/phpbb-ext-smart-mentions-/releases)

A modern, robust, and zero-configuration extension for phpBB 3.3+ that allows forum users to mention others by typing `@username` in their posts.

Engineered from the ground up to solve common forum edge-cases: moderation queues, quote deduplication, orphaned notification cleanup, international Unicode character support, and security hardening against user enumeration.

---

## 🌟 Key Features

- **Real-Time Autocomplete**: Fast, lightweight vanilla JavaScript dropdown with speech-bubble pointer and keyboard navigation (Arrow Up/Down, Enter, Tab, Escape, outside click dismissal).
- **Full Quick Reply & Editor Support**: Works seamlessly in both the Full Posting Editor (`posting.php`) and the Quick Reply box (`quickreply_editor.html`) at the bottom of topics.
- **Unicode & Accented Character Support**: Full support for international alphabets and accented letters (e.g., `@Nicolò`, `@René`, `@Müller`, `@François`, `@José`).
- **Usernames with Spaces**: Mentions usernames containing spaces using quotes (e.g., `@"Jane Doe"`).
- **RFC 3986 Compliant Profile Links**: Mentions render as clickable profile links properly URL-encoded (e.g., `memberlist.php?mode=viewprofile&un=Jane%20Doe` and `un=Nicol%C3%B2`) with transparent backward compatibility for legacy posts.
- **Native Notification Integration**: Fully integrated with phpBB's notification system. Mentioned users receive board notifications (the notification bell with direct post anchor link) and optional email notifications based on their UCP preferences.
- **Moderation Queue Integration**: Mentions inside posts submitted to the moderation queue (`post_visibility != ITEM_APPROVED`) do not send premature notifications. Notifications are automatically dispatched only when a moderator approves the post or topic in the MCP.
- **No Notification Spam from Quotes**: Mentions nested inside `[quote]` blocks are automatically excluded during notification dispatch. Quoting an earlier post with mentions does not trigger duplicate notifications for quoted users.
- **Automatic Notification Cleanup**: Hooks into `core.delete_posts_before` to automatically purge mention notifications when posts or topics are deleted, pruned, or disapproved in the MCP. Completely eliminates phantom unread counts and "The requested post does not exist" 404 errors.
- **Hardened Security & Performance**:
  - The autocomplete endpoint verifies `u_viewprofile` permissions to prevent unauthorized memberlist enumeration by guests.
  - Queries leverage phpBB DBAL's `sql_like_expression()` with automatic escaping of SQL wildcards (`_` and `%`) to prevent heavy runaway queries.
  - Capped at 5 suggestions per lookup, ensuring zero lag even on large boards with hundreds of thousands of members.
- **Zero External JS Dependencies**: Pure vanilla JavaScript and CSS. No heavy third-party libraries (no Tribute.js, At.js, or external jQuery plugins).
- **Zero Configuration Needed**: Plug-and-play setup. Enable the extension and start mentioning right away.

---

## 📋 Requirements

- **phpBB**: 3.3.0 or higher (3.3.x compatible)
- **PHP**: 7.1.3 or higher (tested up to PHP 8.4)
- **Styles**: Compatible with `prosilver`, `prosilver Special Edition`, and all prosilver-derived styles.

---

## 📦 Installation

1. Download the latest release `.zip` archive from the [Releases page](https://github.com/vince844/phpbb-ext-smart-mentions-/releases).
2. Decompress the archive and upload the files to your forum's `ext/` directory.
   The extension directory path on your server must be:
   ```text
   phpBB/ext/kondomanager/mention/
   ```
3. In your browser, navigate to your phpBB **Administration Control Panel (ACP)**.
4. Go to the **Customise** tab -> **Manage extensions**.
5. Locate **Smart Mentions** in the *Disabled Extensions* list and click **Enable**.

---

## 🔄 Updating to a Newer Version

1. In the ACP, navigate to **Customise** -> **Manage extensions**.
2. Click **Disable** for **Smart Mentions** (do **NOT** click *Delete data*).
3. Replace the existing files in `ext/kondomanager/mention/` with the new version's files.
4. Return to the ACP and click **Enable**.
5. Purge the cache via ACP (*General* tab -> *Purge the cache*) or CLI: `php bin/phpbbcli.php cache:purge`.

---

## 🗑️ Uninstallation

1. Go to ACP -> **Customise** -> **Manage extensions**.
2. Click **Disable** next to **Smart Mentions**.
3. To permanently remove all extension data, click **Delete data**.
4. Safely delete the directory `ext/kondomanager/mention/` from your web server.

---

## 💡 How to Use

Simply type `@` followed by at least 2 characters in any post editor or Quick Reply box:
- `@admin` — mentions user *admin*.
- `@Nicolò` — mentions user *Nicolò* (accented characters supported).
- `@"Jane Doe"` — mentions user *Jane Doe* (wrap names containing spaces in quotes).

### Keyboard Navigation
- **Arrow Down / Up**: Navigate through suggested usernames.
- **Enter / Tab**: Select the highlighted username.
- **Escape / Outside Click**: Dismiss the autocomplete dropdown.

---

## 🗺️ Roadmap & Changelog

- Active task board and test specifications: [`ROADMAP.md`](ROADMAP.md)
- Complete version release history: [`CHANGELOG.md`](CHANGELOG.md)

---

## 📄 License

This extension is licensed under the [GNU General Public License v2.0 (GPL-2.0)](license.txt).
