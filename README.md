# Smart Mentions for phpBB 3.3

A lightweight, modern, and zero-config extension for phpBB 3.3+ that allows users to mention others by typing `@username` in their posts.

## 🌟 Features
- **Dynamic Autocomplete**: Vanilla JavaScript autocomplete dropdown that instantly searches for users.
- **Premium UI**: Features a modern "glassmorphism" design with speech bubble arrows, rounded corners, and a polished look that blends seamlessly with prosilver.
- **Performance First**: 
  - Activates only after typing `@` plus at least 2 characters.
  - Limits database queries to 5 results per keystroke to guarantee zero server lag, even on forums with millions of users.
- **Native Notifications**: Fully integrated with phpBB's native notification system. Users receive a board notification (the red bell) and an email (if enabled in their UCP) when someone mentions them.
- **Zero Config**: No complex ACP setup required. Plug and play!

## 📦 Installation
1. Download the latest release from the repository.
2. Unzip the downloaded file and copy the `kondomanager` folder to the `ext/` directory of your phpBB forum.
   Make sure the final path looks exactly like this: `ext/kondomanager/mention/`
3. Navigate to your phpBB Administration Control Panel (ACP).
4. Go to `Customise -> Manage extensions`.
5. Look for **Smart Mentions** under the "Disabled Extensions" list, and click **Enable**.

## 🗑️ Uninstallation
1. Navigate to your phpBB Administration Control Panel (ACP).
2. Go to `Customise -> Extension Management -> Extensions`.
3. Look for **Smart Mentions** under the "Enabled Extensions" list, and click **Disable**.
4. To permanently uninstall, click **Delete Data** and then safely delete the `ext/kondomanager/mention` folder from your server.

## 📄 License
[GPL-2.0-only](license.txt)
