<?php
// Connect to the SAME Redis instance as App 1
// "redis" is the service name — Docker resolves it to the Redis container IP
$redis = new Redis();
$redis->connect('redis', 6379);

// Read total message count using LLEN (List LENgth)
// Returns 0 if the key doesn't exist yet — safe default
$messageCount = $redis->lLen('messages');

// Read visit count — GET returns null if key doesn't exist
$visitCount = (int)($redis->get('visit_count') ?? 0);

// Get the last 5 messages for a preview (index -5 to -1 = last 5 items)
$recentMessages = $redis->lRange('messages', -5, -1);
$recentMessages = array_reverse($recentMessages); // newest first
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Auto-refresh every 10 seconds so stats stay live -->
  <meta http-equiv="refresh" content="10">
  <title>DevOpsHub — Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #07090f; --surface: #10131c; --border: #1e2335;
      --accent: #00e5a0; --accent2: #7c6af7; --accent3: #f59e0b;
      --text: #e8eaf0; --muted: #5a6275; --radius: 14px;
      --mono: 'Space Mono', monospace; --sans: 'Syne', sans-serif;
    }
    body { background: var(--bg); color: var(--text); font-family: var(--sans);
           min-height: 100vh; padding: 2rem 1rem; display: flex;
           flex-direction: column; align-items: center; }
    header { width: 100%; max-width: 860px; display: flex; justify-content: space-between;
             align-items: center; border-bottom: 1px solid var(--border);
             padding-bottom: 1.25rem; margin-bottom: 2.5rem; }
    .logo { font-size: 1.4rem; font-weight: 800; }
    .logo span { color: var(--accent); }
    .badge { background: var(--surface); border: 1px solid var(--border);
             border-radius: 999px; padding: .35rem .9rem;
             font-family: var(--mono); font-size: .75rem; color: var(--accent2); }
    .live { display: flex; align-items: center; gap: .4rem;
            font-family: var(--mono); font-size: .72rem; color: var(--accent); }
    .live-dot { width: 7px; height: 7px; background: var(--accent);
                border-radius: 50%; animation: pulse 1.5s infinite; }
    @keyframes pulse {
      0%,100% { box-shadow: 0 0 0 0 rgba(0,229,160,.5); }
      50%      { box-shadow: 0 0 0 5px rgba(0,229,160,0); }
    }
    .page-title { width: 100%; max-width: 860px; margin-bottom: 2rem; }
    .page-title h1 { font-size: 2rem; font-weight: 800; letter-spacing: -1px; }
    .page-title p { color: var(--muted); font-family: var(--mono); font-size: .82rem; margin-top: .4rem; }
    .stats-grid { width: 100%; max-width: 860px; display: grid;
                  grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 2rem; }
    .stat-card { background: var(--surface); border: 1px solid var(--border);
                 border-radius: var(--radius); padding: 1.75rem; position: relative; }
    .stat-card::before { content: ''; position: absolute; top: 0; left: 0;
                         width: 100%; height: 3px; border-radius: var(--radius) var(--radius) 0 0; }
    .stat-card.messages::before { background: var(--accent); }
    .stat-card.visits::before   { background: var(--accent2); }
    .stat-label { font-family: var(--mono); font-size: .72rem; text-transform: uppercase;
                  letter-spacing: 1.5px; color: var(--muted); margin-bottom: .75rem; }
    .stat-number { font-size: 3.5rem; font-weight: 800; font-family: var(--mono);
                   line-height: 1; letter-spacing: -2px; }
    .stat-card.messages .stat-number { color: var(--accent); }
    .stat-card.visits   .stat-number { color: var(--accent2); }
    .stat-sublabel { margin-top: .6rem; font-size: .8rem; color: var(--muted); }
    .info-grid { width: 100%; max-width: 860px; display: grid;
                 grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 2rem; }
    .info-card { background: var(--surface); border: 1px solid var(--border);
                 border-radius: var(--radius); padding: 1.5rem; }
    .info-card h3 { font-size: .8rem; text-transform: uppercase; letter-spacing: 1px;
                    color: var(--muted); font-family: var(--mono); margin-bottom: 1rem; }
    .redis-row { display: flex; justify-content: space-between; align-items: center;
                 padding: .5rem 0; border-bottom: 1px solid var(--border);
                 font-family: var(--mono); font-size: .82rem; }
    .redis-row:last-child { border-bottom: none; }
    .rkey { color: var(--muted); }
    .rval { color: var(--accent3); font-weight: 700; }
    .preview-section { width: 100%; max-width: 860px; }
    .section-header { display: flex; justify-content: space-between;
                      align-items: center; margin-bottom: 1rem; }
    .section-title { font-size: .78rem; text-transform: uppercase;
                     letter-spacing: 1.5px; color: var(--muted); font-family: var(--mono); }
    .app1-link { font-family: var(--mono); font-size: .75rem; color: var(--accent);
                 text-decoration: none; border: 1px solid var(--accent);
                 border-radius: 6px; padding: .25rem .7rem; }
    .preview-item { background: var(--surface); border: 1px solid var(--border);
                    border-radius: 8px; padding: .9rem 1.1rem; margin-bottom: .6rem;
                    font-family: var(--mono); font-size: .87rem;
                    display: flex; gap: .75rem; }
    .arrow { color: var(--accent); }
    .empty-state { text-align: center; padding: 2.5rem; color: var(--muted);
                   font-family: var(--mono); border: 1px dashed var(--border);
                   border-radius: var(--radius); }
    footer { margin-top: 3rem; color: var(--muted); font-size: .73rem;
             font-family: var(--mono); text-align: center; line-height: 1.8; }
  </style>
</head>
<body>
  <header>
    <div class="logo">DevOps<span>Hub</span></div>
    <div style="display:flex;align-items:center;gap:1rem">
      <div class="live"><span class="live-dot"></span> LIVE · auto-refresh 10s</div>
      <span class="badge">● App 2 · Dashboard</span>
    </div>
  </header>

  <div class="page-title">
    <h1>System Dashboard</h1>
    <p>Reading live data from shared Redis database · Updates on every page load</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card messages">
      <div class="stat-label">Total Messages</div>
      <div class="stat-number"><?= $messageCount ?></div>
      <p class="stat-sublabel">Stored in Redis LIST</p>
    </div>
    <div class="stat-card visits">
      <div class="stat-label">Total Page Visits</div>
      <div class="stat-number"><?= $visitCount ?></div>
      <p class="stat-sublabel">Tracked by App 1</p>
    </div>
  </div>

  <div class="info-grid">
    <div class="info-card">
      <h3>Redis Key Map</h3>
      <div class="redis-row"><span class="rkey">messages</span><span class="rkey">LIST</span><span class="rval"><?= $messageCount ?> items</span></div>
      <div class="redis-row"><span class="rkey">visit_count</span><span class="rkey">STRING</span><span class="rval"><?= $visitCount ?></span></div>
    </div>
    <div class="info-card">
      <h3>Architecture</h3>
      <div class="redis-row"><span class="rkey">App 1 (producer)</span><span class="rval">:5001</span></div>
      <div class="redis-row"><span class="rkey">App 2 (observer)</span><span class="rval">:5002</span></div>
      <div class="redis-row"><span class="rkey">Redis (shared DB)</span><span class="rval">:6379</span></div>
    </div>
  </div>

  <div class="preview-section">
    <div class="section-header">
      <span class="section-title">Recent Messages (last 5)</span>
      <a class="app1-link" href="http://localhost:5001">→ Go to App 1</a>
    </div>
    <?php if ($recentMessages): ?>
      <?php foreach ($recentMessages as $msg): ?>
        <div class="preview-item">
          <span class="arrow">▶</span>
          <span><?= htmlspecialchars($msg) ?></span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">No messages yet. Visit App 1 to submit some!</div>
    <?php endif; ?>
  </div>

  <footer>
    App 2 — Dashboard · Port 5002 · Redis Host: redis:6379<br>
    Auto-refreshes every 10 seconds. Read-only — no writes to Redis.
  </footer>
</body>
</html>