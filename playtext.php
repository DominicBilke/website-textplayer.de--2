<?php
session_start();

require_once __DIR__ . '/config.php';

// Initialize session arrays
$session = $_SESSION['text'] ?? [];
$lang = $_SESSION['language'] ?? [];
$voice = $_SESSION['voice'] ?? [];
$name = $_SESSION['name'] ?? [];

// Delete item
if (isset($_GET['time']) && $_GET['time'] !== '') {
    $timeKey = preg_replace('/[^0-9:]/', '', $_GET['time']);
    unset($_SESSION['text'][$timeKey]);
    unset($_SESSION['language'][$timeKey]);
    unset($_SESSION['voice'][$timeKey]);
    unset($_SESSION['name'][$timeKey]);
    $session = $_SESSION['text'] ?? [];
    $lang = $_SESSION['language'] ?? [];
    $voice = $_SESSION['voice'] ?? [];
    $name = $_SESSION['name'] ?? [];
}

function array_implode_with_keys($array) {
    if (!is_array($array) || count($array) === 0) return '';
    $parts = [];
    foreach ($array as $key => $value) {
        $parts[] = $key . '||||' . $value;
    }
    return implode('----', $parts);
}

function array_explode_with_keys($string) {
    $return = [];
    if (empty($string)) return $return;
    $pieces = explode('----', $string);
    foreach ($pieces as $piece) {
        $keyval = explode('||||', $piece, 2);
        $return[$keyval[0]] = $keyval[1] ?? '';
    }
    return $return;
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    $pdo = null;
}

// Load data from DB by user ID
if (isset($_GET['user']) && $_GET['user'] !== '') {
    $userInput = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['user']);
    if (strlen($userInput) > 0 && $pdo) {
        $stmt = $pdo->prepare("SELECT * FROM textplayer WHERE user = ?");
        $stmt->execute([$userInput]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $session = array_explode_with_keys($row['text'] ?? '');
            $lang = array_explode_with_keys($row['language'] ?? '');
            $voice = array_explode_with_keys($row['voice'] ?? '');
            $name = array_explode_with_keys($row['name'] ?? '');
            $_SESSION['user'] = $row['user'];
            $_SESSION['text'] = $session;
            $_SESSION['language'] = $lang;
            $_SESSION['voice'] = $voice;
            $_SESSION['name'] = $name;
        }
    }
}

// Update DB when session has user (persist changes)
if (isset($_SESSION['user']) && $pdo && !isset($_GET['user']) && !isset($_GET['newuser'])) {
    $currentUser = $_SESSION['user'];
    $stmt = $pdo->prepare("UPDATE textplayer SET text = ?, language = ?, voice = ?, name = ? WHERE user = ?");
    $stmt->execute([
        array_implode_with_keys($session),
        array_implode_with_keys($lang),
        array_implode_with_keys($voice),
        array_implode_with_keys($name),
        $currentUser
    ]);
}

// Create new user ID
if (isset($_GET['newuser']) && $pdo) {
    $newUser = uniqid();
    $_SESSION['user'] = $newUser;
    $_SESSION['text'] = $session;
    $_SESSION['language'] = $lang;
    $_SESSION['voice'] = $voice;
    $_SESSION['name'] = $name;
    $stmt = $pdo->prepare("INSERT INTO textplayer (user, text, language, voice, name) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $newUser,
        array_implode_with_keys($session),
        array_implode_with_keys($lang),
        array_implode_with_keys($voice),
        array_implode_with_keys($name)
    ]);
}

// Unload ID
if (isset($_GET['UnloadID'])) {
    session_destroy();
    $session = [];
    $lang = [];
    $voice = [];
    $name = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Play Text – TextPlayer.de</title>
  <meta name="description" content="Your scheduled texts will be spoken at the set times. Keep this page open.">
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
        <a href="settext.php">Set Text</a>
        <a href="playtext.php" class="active">Play Text</a>
      </nav>
    </div>
  </header>

  <main class="app-main">
    <div class="app-container-wide">
      <h1 class="page-title">Play text at time</h1>
      <p class="page-subtitle">Keep this page open. Texts will play automatically at the scheduled times. You can also play any text manually.</p>

      <?php if (count($session) === 0) : ?>
      <div class="card empty-state">
        <h3>No text set</h3>
        <p>Please add a text and time on the Set Text page.</p>
        <a href="settext.php" class="btn btn-primary">Set Text</a>
      </div>
      <?php else : ?>

      <div class="scheduled-list">
        <?php foreach ($session as $time => $text) :
          $timeSafe = htmlspecialchars($time, ENT_QUOTES, 'UTF-8');
          $textSafe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
          $langVal = $lang[$time] ?? 'en-US';
          $voiceVal = $voice[$time] ?? '';
          $nameVal = $name[$time] ?? 'Jone Due';
          $nameSafe = htmlspecialchars($nameVal, ENT_QUOTES, 'UTF-8');
          $playFn = 'play_' . preg_replace('/[^a-zA-Z0-9]/', '_', $time);
        ?>
        <div class="scheduled-item" data-time="<?php echo $timeSafe; ?>">
          <div class="scheduled-time"><?php echo $timeSafe; ?></div>
          <div class="scheduled-content">
            <?php echo nl2br($textSafe); ?>
            <div style="margin-top: var(--space-sm); font-size: 0.875rem; color: var(--color-text-subtle);"><?php echo $nameSafe; ?></div>
          </div>
          <div class="scheduled-actions">
            <button type="button" class="btn-play" onclick="<?php echo $playFn; ?>()" aria-label="Play"></button>
            <form method="get" action="playtext.php" style="display:inline;">
              <input type="hidden" name="time" value="<?php echo $timeSafe; ?>">
              <button type="submit" class="btn btn-danger">Delete</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <script>
        if (!('speechSynthesis' in window)) {
          console.warn('Speech synthesis not supported');
        }

        const scheduledData = <?php
          $data = [];
          foreach ($session as $time => $text) {
            $data[] = [
              'time' => $time,
              'text' => str_replace(["\r", "\n"], ' ', $text),
              'lang' => $lang[$time] ?? 'en-US',
              'voice' => $voice[$time] ?? ''
            ];
          }
          echo json_encode($data);
        ?>;

        const played = {};
        scheduledData.forEach(d => { played[d.time] = false; });

        function speak(timeKey) {
          const d = scheduledData.find(x => x.time === timeKey);
          if (!d) return;
          const msg = new SpeechSynthesisUtterance();
          msg.lang = d.lang;
          msg.text = d.text;
          if (d.voice) {
            const voices = speechSynthesis.getVoices().filter(v => v.name === d.voice);
            if (voices[0]) msg.voice = voices[0];
          }
          speechSynthesis.speak(msg);
        }

        <?php foreach ($session as $time => $text) :
          $playFn = 'play_' . preg_replace('/[^a-zA-Z0-9]/', '_', $time);
        ?>
        function <?php echo $playFn; ?>() { speak(<?php echo json_encode($time); ?>); }
        <?php endforeach; ?>

        setInterval(function() {
          const now = new Date();
          const h = now.getHours();
          const m = now.getMinutes();
          scheduledData.forEach(d => {
            const [th, tm] = d.time.split(':').map(Number);
            if (th === h && tm === m && !played[d.time]) {
              played[d.time] = true;
              speak(d.time);
            }
          });
        }, 10000);
      </script>

      <div class="id-section card">
        <h3>Save & restore your texts</h3>
        <p style="color: var(--color-text-muted); margin-bottom: var(--space-lg);">Save your ID to restore your scheduled texts on another device or after clearing your browser.</p>
        <form method="get" action="playtext.php" class="id-grid">
          <div class="id-box">
            <label>Current ID</label>
            <p class="id-value"><?php echo htmlspecialchars($_SESSION['user'] ?? 'No ID', ENT_QUOTES, 'UTF-8'); ?></p>
            <div style="margin-top: var(--space-md); display: flex; gap: var(--space-sm);">
              <button type="submit" name="newuser" value="1" class="btn btn-secondary">Get new ID</button>
              <button type="submit" name="UnloadID" value="1" class="btn btn-ghost">Unload ID</button>
            </div>
          </div>
          <div class="id-box">
            <label for="user">Load saved ID</label>
            <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-sm);">
              <input type="text" id="user" name="user" class="form-input" placeholder="Paste your ID" style="flex:1;">
              <button type="submit" class="btn btn-primary">Load</button>
            </div>
          </div>
        </form>
      </div>

      <?php endif; ?>

      <p style="text-align: center; margin-top: var(--space-xl);">
        <a href="settext.php" class="btn btn-secondary">Add another text</a>
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
</body>
</html>
