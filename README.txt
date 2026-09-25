TODON MINECRAFT SKINS — DEPLOYMENT GUIDE (shared PHP/MySQL hosting)
=====================================================================

WHAT CHANGED FROM BEFORE
-------------------------
The site used to store everything in your browser only (IndexedDB),
so uploads only ever existed on your own device. Now it uses a small
PHP + MySQL backend, so anyone who visits your domain can enter a
code and get the same skin — this is required for a real public site.

FILES IN THIS FOLDER
---------------------
index.html        the website itself (frontend)
config.php        <-- YOU MUST EDIT THIS with your DB details
schema.sql        run this once to create the database tables
upload.php        handles uploads
skin.php          looks up a skin by code
serve.php         serves preview images + individual files
download_zip.php  zips multiple files together for one download
list.php          lists uploaded skins (used on the upload screen)
delete.php        deletes a skin
uploads/          where uploaded files are stored (auto-created)
.htaccess         raises PHP upload limits (Apache/mod_php hosts)
.user.ini         raises PHP upload limits (PHP-FPM hosts)

SETUP STEPS
-----------
1. In your hosting control panel (Hostinger hPanel, cPanel, etc.),
   create a new MySQL database and a database user with full
   privileges on it. Note down: database name, username, password,
   and host (almost always "localhost").

2. Open phpMyAdmin for that database, go to the "SQL" tab, paste in
   the contents of schema.sql, and run it. This creates two tables:
   "skins" and "skin_files".

3. Open config.php in a text editor and fill in:
     DB_HOST, DB_NAME, DB_USER, DB_PASS
   and set UPLOAD_SECRET to whatever code you want to require for
   uploading (currently "2308" — change this before going live).

4. Upload ALL files in this folder (keeping the folder structure,
   including the uploads/ subfolder and its .htaccess + index.html)
   to your domain's public web folder (often called public_html,
   htdocs, or www — check your host's docs).

5. Make sure the "uploads" folder is writable by PHP. Most hosts
   default new folders to permissions that already allow this; if
   uploads fail with a permissions error, set uploads/ to 755 (or
   775 if 755 doesn't work) via your host's File Manager or FTP client.

6. Visit your domain. Try entering a code — you should see
   "No skin found for that code." Then use the secret code to open
   the upload screen and upload a test skin. If that works, enter its
   code from a DIFFERENT device/browser to confirm it downloads there
   too — that's the whole point of this upgrade.

IF LARGE FILE UPLOADS FAIL (e.g. an .apk or .exe over a few MB)
-----------------------------------------------------------------
Your host may cap PHP upload sizes regardless of .htaccess/.user.ini
(this is common on budget shared hosting). If uploads of large files
fail:
  - Check your hosting control panel for a "PHP Configuration" or
    "MultiPHP INI Editor" section (common on cPanel/Hostinger) and
    raise upload_max_filesize / post_max_size / max_execution_time /
    memory_limit there directly.
  - If you truly can't raise the limit high enough (some budget
    hosts hard-cap around 32–64MB no matter what), you'll need either
    a hosting plan/provider that allows larger uploads, or to host
    very large files (like a 100MB+ .exe) externally (e.g. Google
    Drive, Mega, a dedicated file host) and just store a code that
    links out to that instead of uploading it here directly. Ask me
    if you want that variant built instead.

SECURITY NOTES
--------------
- The uploads/ folder is locked down (.htaccess) so uploaded files
  can never be executed as scripts, no matter what extension they
  have — this matters because you're intentionally allowing ANY file
  type to be uploaded (apk, html, etc.).
- The upload/delete/list actions all check UPLOAD_SECRET on the
  SERVER, not just in the browser, so someone can't bypass the
  "Upload Here" gate just by reading your JavaScript.
- Still, treat UPLOAD_SECRET like a password — don't share it
  publicly, and change it from the default before going live.
