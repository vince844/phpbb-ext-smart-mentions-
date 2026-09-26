# Smart Mentions for phpBB 3.3.x

[![phpBB Version](https://img.shields.io/badge/phpBB-3.3.x-blue.svg)](https://www.phpbb.com)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.1.3-8892BF.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/License-GPL--2.0-green.svg)](license.txt)
[![Latest Release](https://img.shields.io/badge/Release-v1.0.0--b5-orange.svg)](https://github.com/vince844/phpbb-ext-smart-mentions-/releases)

A modern, lightweight, and zero-configuration extension for phpBB 3.3+ that allows forum users to mention others by typing `@username` in their posts.

---

## 🌟 Key Features

- **Real-Time Autocomplete**: Fast, lightweight vanilla JavaScript dropdown that instantly searches matching usernames as you type.
- **Full Quick Reply & Editor Support**: Works seamlessly in both the Full Posting Editor (`posting.php`) and the Quick Reply box (`quickreply_editor.html`) at the bottom of topics.
- **Unicode & Accented Character Support**: Full support for international alphabets and accented letters (e.g., `@Nicolò`, `@René`, `@Müller`, `@José`).
- **Usernames with Spaces**: Mentions usernames with spaces effortlessly using quotes (e.g., `@"Jane Doe"`).
- **Native Notification Integration**: Fully integrated with phpBB's notification framework. Mentioned users receive real-time board notifications (the notification bell with direct post link) and optional email notifications based on their UCP preferences.
- **Premium Glassmorphic UI**: Features a sleek, modern design with subtle blur, speech bubble pointer, smooth micro-animations, and full compatibility with `prosilver`, `prosilver Special Edition`, and derived themes.
- **Performance First**:
  - Activates only after typing `@` followed by at least 2 characters.
  - Queries are capped at 5 suggestions per lookup, ensuring zero lag even on large boards with hundreds of thousands of members.
- **Zero Config**: Plug-and-play setup. Enable the extension and start mentioning right away—no complicated ACP settings needed.

---

## 📋 Requirements

- **phpBB**: 3.3.0 or higher (3.3.x compatible)
- **PHP**: 7.1.3 or higher (tested up to PHP 8.4)
- **Styles**: Compatible with `prosilver`, `prosilver Special Edition`, and all prosilver-based styles.

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
5. Purge the cache via ACP or CLI: `php bin/phpbbcli.php cache:purge`.

---

## 🗑️ Uninstallation

1. Go to ACP -> **Customise** -> **Manage extensions**.
2. Click **Disable** next to **Smart Mentions**.
3. To permanently remove all data, click **Delete data**.
4. Safely delete the directory `ext/kondomanager/mention/` from your web server.

---

## 💡 How to Use

Simply type `@` followed by the username in any post or Quick Reply box:
- `@admin` — mentions user *admin*.
- `@Nicolò` — mentions user *Nicolò* (accented characters supported).
- `@"John Doe"` — mentions user *John Doe* (wrap names containing spaces in quotes).

Selecting a suggestion from the popup automatically formats the mention with the `@` prefix and sets the cursor right after it.

---

## 🗺️ Roadmap & Changelog

- Detailed development milestones and known tasks: [`ROADMAP.md`](ROADMAP.md)
- Complete version release history: [`CHANGELOG.md`](CHANGELOG.md)

---

## 📄 License

This extension is licensed under the [GNU General Public License v2.0 (GPL-2.0)](license.txt).
