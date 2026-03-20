<?php
session_start();
if (isset($_POST['text']) && isset($_POST['time'])) {
    $_SESSION['text'][$_POST['time']] = $_POST['text'];
    $_SESSION['language'][$_POST['time']] = isset($_POST['languages']) ? $_POST['languages'] : 'en-us';
    $_SESSION['voice'][$_POST['time']] = isset($_POST['voice']) ? $_POST['voice'] : '';
    $_SESSION['name'][$_POST['time']] = $_POST['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Set Text – TextPlayer.de</title>
  <meta name="description" content="Enter text, time and language. Your text will be spoken at the scheduled time.">
  <meta name="author" content="Bilke Web- und Softwareentwicklung">
  <link rel="icon" href="images/fevicon.png" type="image/gif">
  <link rel="stylesheet" href="css/modern.css">
</head>
<body class="app-layout">
  <header class="app-header">
    <div class="app-header-inner">
      <a href="index.html" class="app-logo">TextPlayer.de</a>
      <button class="nav-toggle" type="button" aria-label="Toggle menu" onclick="document.querySelector('.app-nav').classList.toggle('open')">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
      </button>
      <nav class="app-nav">
        <a href="index.html">About</a>
        <a href="settext.php" class="active">Set Text</a>
        <a href="playtext.php">Play Text</a>
      </nav>
    </div>
  </header>

  <main class="app-main">
    <div class="app-container">
      <h1 class="page-title">Enter text and time</h1>
      <p class="page-subtitle">Add a scheduled text. Choose time, language and voice. Multiple texts are possible.</p>

      <form method="post" action="settext.php" id="settext" class="card">
        <div class="form-group">
          <label class="form-label" for="text">Text</label>
          <textarea id="text" name="text" class="form-textarea" required placeholder="Enter the text you want to be spoken..."></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="time">Time</label>
            <input type="time" id="time" name="time" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="name">Label (e.g. your name)</label>
            <input type="text" id="name" name="name" class="form-input" value="Jone Due" required placeholder="Label for this text">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="languages">Language</label>
            <select id="languages" name="languages" class="form-select" onchange="SetVoices()">
              <option value="en-US">Loading...</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="voice">Voice</label>
            <select id="voice" name="voice" class="form-select">
              <option value="">Default</option>
            </select>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Set Text</button>
      </form>

      <p style="text-align: center; margin-top: var(--space-lg);">
        <a href="playtext.php" class="btn btn-secondary">View scheduled texts →</a>
      </p>
    </div>
  </main>

  <footer class="app-footer">
    <div class="app-footer-inner">
      <div class="app-footer-links">
        <a href="imprint.html">Imprint</a>
        <a href="privacy_policy.html">Privacy Policy</a>
      </div>
      <p class="app-footer-copy">© 2024 <a href="https://www.bilke-web-sw.de/" target="_blank" rel="noopener">Bilke Web and Software Development</a></p>
    </div>
  </footer>

  <script>
    if (!('speechSynthesis' in window)) {
      alert("Your browser doesn't support speech synthesis. Please use a modern browser like Chrome or Edge.");
    }

    const langselect = document.querySelector('#languages');
    const voiceselect = document.querySelector('#voice');

    function SetVoices() {
      const voices = window.speechSynthesis.getVoices();
      voiceselect.innerHTML = '<option value="">Default</option>';
      const lang = langselect.options[langselect.selectedIndex]?.value;
      if (!lang) return;
      for (let i = 0; i < voices.length; i++) {
        if (voices[i].lang === lang) {
          voiceselect.add(new Option(voices[i].name, voices[i].name));
        }
      }
    }

    window.speechSynthesis.onvoiceschanged = function() {
      const voices = window.speechSynthesis.getVoices();
      const langs = [];
      for (let i = 0; i < voices.length; i++) {
        if (!langs.includes(voices[i].lang)) langs.push(voices[i].lang);
      }
      langs.sort();
      langselect.innerHTML = '';
      for (let i = 0; i < langs.length; i++) {
        langselect.add(new Option(langs[i], langs[i]));
      }
      SetVoices();
    };

    // Fallback if voices already loaded
    if (window.speechSynthesis.getVoices().length > 0) {
      window.speechSynthesis.onvoiceschanged();
    }
  </script>
</body>
</html>
