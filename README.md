AESNote Quick Start Guide

What it is: A local-first, encrypted rich-text editor for .ptxt files, running entirely in your browser.
Prerequisites: A modern browser (Chromium-based recommended) with File System Access API support; open the app from a local file or served over localhost/HTTPS.
Quick Start
Open the app: open AESNote.html in your browser.
Pick a folder: click the folder button or use New/Open to select a local folder that contains .ptxt files. The app stores a folder handle for re-use.
Unlock a file: click a filename in the left sidebar to load it; a password modal will appear. Enter your password to decrypt and load.
Edit: use the central rich-text editor; toolbar supports Bold, Italic, Underline, lists.
Save: click Save to write back to the same file using the current session password. To switch password, choose Save As and enter a new password. The app will request write permission to the folder (and the file handle when possible).
Manage access: if permission is denied, use the breadcrumb's Grant Access action to re-request access and refresh the file list.
Delete files: with a file selected, use Delete and confirm.
Security notes: passwords stay in memory only; data is stored locally in encrypted form in your folder.
Fallbacks & Tips
If direct writes fail, use Save As to export via download or choose a new location.
The app works best in Chromium-based browsers; some APIs may be unavailable in others.
Theme toggle (light/dark) is stored locally in your browser.
If you need any adjustments to wording or format, tell me and I’ll tweak accordingly.
