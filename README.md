# TextPlayer.de

TextPlayer.de lets visitors schedule text that is spoken aloud in the browser at a chosen time of day. Users enter text, pick a time, language, and optional voice; multiple schedules are supported. Saved schedules can be synced across visits using an opaque user ID stored in MySQL.

## Features

- **Set Text** (`settext.php`): Add scheduled entries with time, language, voice label, and optional voice from the Web Speech API.
- **Play Text** (`playtext.php`): Lists scheduled texts, plays them automatically when the clock matches the scheduled time (polling every 10 seconds), and allows manual playback per entry.
- **Persistence**: PHP sessions hold entries during a visit; optional **user ID** loads or saves rows in the database for later visits.
- **Landing page** (`index.html`): Overview and navigation.

Speech runs entirely in the browser via the **Web Speech API** (`speechSynthesis`). No external TTS service is required.

## Requirements

- PHP 7.4+ with PDO MySQL enabled.
- MySQL (or MariaDB) with a database and table as described below.
- A web server (Apache with mod\_php, nginx + php-fpm, or PHP’s built-in server for local testing).
- A modern desktop browser with speech synthesis support (for example Chromium-based browsers or Safari).

## Database

Create a table suitable for `playtext.php` (column names match the application):

```sql
CREATE TABLE textplayer (
  user VARCHAR(64) NOT NULL PRIMARY KEY,
  text MEDIUMTEXT NOT NULL,
  language MEDIUMTEXT NOT NULL,
  voice MEDIUMTEXT NOT NULL,
  name MEDIUMTEXT NOT NULL
);
```

The `text`, `language`, `voice`, and `name` columns store serialized key-value strings (time keys and associated values), not raw JSON.

## Configuration

Database credentials are read from `config.php`. For production, prefer environment variables:

| Variable | Meaning |
|----------|---------|
| `TEXTPLAYER_DB_HOST` | Database host |
| `TEXTPLAYER_DB_NAME` | Database name |
| `TEXTPLAYER_DB_USER` | Database user |
| `TEXTPLAYER_DB_PASS` | Database password |

If those variables are not set, `config.php` falls back to its embedded defaults. Replace defaults or use env vars before deploying; do not commit real passwords to public repositories.

Optional: `.htaccess` sets a long PHP session lifetime for hosts that honor `php_value`.

## Local development

1. Clone or copy the project into your web root or project folder.
2. Create the database and `textplayer` table (see above).
3. Point `config.php` (or env vars) at your database.
4. Serve the site with PHP, for example:

   ```bash
   php -S localhost:8080
   ```

   Open `http://localhost:8080/index.html`. Use `settext.php` and `playtext.php` for the dynamic pages.

## Project layout (main files)

| Path | Role |
|------|------|
| `index.html` | Marketing / entry page |
| `settext.php` | Form to add scheduled text |
| `playtext.php` | List, play, delete, ID load/save |
| `config.php` | Database constants |
| `css/modern.css` | Current UI stylesheet |
| `imprint.html`, `privacy_policy.html` | Legal pages |

Older theme assets under `css/style.css`, Bootstrap bundles, and `js/` remain in the tree but are not required by the revamped pages.

## Browser notes

- Voices and languages depend on the OS and browser; the language list on **Set Text** is built from `speechSynthesis.getVoices()`.
- Scheduled playback only runs while **Play Text** stays open in an active tab; background throttling may affect timing if the tab is inactive for a long time.

## Credits

Site content and development are attributed to Bilke Web- und Softwareentwicklung (see imprint and footer links).
