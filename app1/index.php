<?php
// Connect to Redis using the SERVICE NAME "redis" (not localhost)
// In Docker Compose, containers find each other by service name
$redis = new Redis();
$redis->connect('redis', 6379);

// Every time this page loads, increment the visit counter
// INCR is atomic — safe even if 100 users load at the same time
$redis->incr('visit_count');
$visitCount = $redis->get('visit_count');

// If the form was submitted (POST request), save the message
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        // RPUSH adds the message to the RIGHT end of the Redis list
        // This keeps messages in chronological order
        $redis->rPush('messages', $message);
        // Redirect after POST to prevent duplicate submissions on refresh
        header('Location: /');
        exit;
    } else {
        $error = 'Message cannot be empty!';
    }
}

// Get all messages from Redis list (0 = start, -1 = end means ALL)
$messages = $redis->lRange('messages', 0, -1);
$messages = array_reverse($messages); // Show newest first
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DevOpsHub — Message Collector</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #0d0f14; --surface: #161922; --border: #252a36;
      --accent: #00e5a0; --accent2: #7c6af7; --text: #e8eaf0;
      --muted: #6b7280; --danger: #f87171; --radius: 12px;
      --mono: 'Space Mono', monospace; --sans: 'Syne', sans-serif;
    }
    body { background: var(--bg); color: var(--text); font-family: var(--sans);
           min-height: 100vh; padding: 2rem 1rem; display: flex;
           flex-direction: column; align-items: center; }
    header { width: 100%; max-width: 720px; margin-bottom: 2.5rem;
             display: flex; justify-content: space-between; align-items: center;
             border-bottom: 1px solid var(--border); padding-bottom: 1.25rem; }
    .logo { font-size: 1.4rem; font-weight: 800; }
    .logo span { color: var(--accent); }
    .badge { background: var(--surface); border: 1px solid var(--border);
             border-radius: 999px; padding: .35rem .9rem;
             font-family: var(--mono); font-size: .78rem; color: var(--accent); }
    .visit-card { width: 100%; max-width: 720px; background: var(--surface);
                  border: 1px solid var(--border); border-left: 3px solid var(--accent2);
                  border-radius: var(--radius); padding: 1.25rem 1.5rem;
                  display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; }
    .visit-info p { font-size: .78rem; color: var(--muted); margin-bottom: .2rem;
                    text-transform: uppercase; letter-spacing: 1px; font-family: var(--mono); }
    .visit-info strong { font-size: 2rem; font-weight: 800; color: var(--accent2); font-family: var(--mono); }
    .card { width: 100%; max-width: 720px; background: var(--surface);
            border: 1px solid var(--border); border-radius: var(--radius);
            padding: 2rem; margin-bottom: 2rem; }
    .card h2 { font-size: 1.1rem; font-weight: 700; margin-bottom: 1.25rem; }
    textarea { width: 100%; background: var(--bg); border: 1px solid var(--border);
               border-radius: 8px; color: var(--text); font-family: var(--mono);
               font-size: .9rem; padding: .9rem 1rem; resize: vertical;
               min-height: 120px; outline: none; }
    textarea:focus { border-color: var(--accent); }
    textarea::placeholder { color: var(--muted); }
    .error { color: var(--danger); font-family: var(--mono); font-size: .82rem; margin-top: .5rem; }
    button { margin-top: 1rem; background: var(--accent); color: #0d0f14;
             border: none; border-radius: 8px; padding: .75rem 2rem;
             font-family: var(--sans); font-weight: 700; font-size: .95rem; cursor: pointer; }
    button:hover { opacity: .88; }
    .messages-section { width: 100%; max-width: 720px; }
    .section-header { display: flex; justify-content: space-between;
                      align-items: center; margin-bottom: 1rem; }
    .section-title { font-size: .78rem; text-transform: uppercase;
                     letter-spacing: 1.5px; color: var(--muted); font-family: var(--mono); }
    .count-chip { background: var(--accent); color: #0d0f14; font-family: var(--mono);
                  font-size: .75rem; font-weight: 700; padding: .2rem .6rem; border-radius: 999px; }
    .message-item { background: var(--surface); border: 1px solid var(--border);
                    border-radius: 8px; padding: .9rem 1.1rem; margin-bottom: .6rem;
                    font-family: var(--mono); font-size: .88rem; display: flex;
                    align-items: flex-start; gap: .8rem; }
    .msg-index { color: var(--muted); font-size: .75rem; min-width: 24px; }
    .empty-state { text-align: center; padding: 3rem 1rem; color: var(--muted);
                   font-family: var(--mono); border: 1px dashed var(--border);
                   border-radius: var(--radius); }
    footer { margin-top: 3rem; color: var(--muted); font-size: .75rem;
             font-family: var(--mono); text-align: center; }
  </style>
</head>
<body>
  <header>
    <div class="logo">DevOps<span>Hub</span></div>
    <span class="badge">● App 1 · Message Collector</span>
  </header>

  <div class="visit-card">
    <div style="font-size:1.8rem">👁</div>
    <div class="visit-info">
      <p>Page Visits (stored in Redis)</p>
      <strong><?= htmlspecialchars($visitCount) ?></strong>
    </div>
  </div>

  <div class="card">
    <h2>Submit a Message</h2>
    <form method="POST" action="/">
      <textarea name="message" placeholder="Type your feedback here…"></textarea>
      <?php if ($error): ?>
        <p class="error">⚠ <?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <button type="submit">Send Message →</button>
    </form>
  </div>

  <div class="messages-section">
    <div class="section-header">
      <span class="section-title">Redis Message Log</span>
      <?php if ($messages): ?>
        <span class="count-chip"><?= count($messages) ?></span>
      <?php endif; ?>
    </div>
    <?php if ($messages): ?>
      <?php foreach ($messages as $i => $msg): ?>
        <div class="message-item">
          <span class="msg-index">#<?= count($messages) - $i ?></span>
          <span><?= htmlspecialchars($msg) ?></span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">📭 No messages yet. Be the first!</div>
    <?php endif; ?>
  </div>

  <footer>
    Redis Keys: <strong>messages</strong> (LIST) · <strong>visit_count</strong> (STRING) · App 1 on port 5001
  </footer>
</body>
</html>