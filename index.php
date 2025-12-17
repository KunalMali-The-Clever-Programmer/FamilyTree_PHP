<?php
// index.php — Family Tree (Frosted Blue Glass — Modern Dashboard)
// keep your existing db.php that defines $conn (mysqli)
include 'db.php';

// Fetch members
$result = $conn->query("SELECT m1.id, m1.name, m1.relation_type, m1.gender, m1.profile_image, 
                        m1.birth_date, m1.notes, m2.name as parent_name
                        FROM members m1
                        LEFT JOIN members m2 ON m1.parent_id = m2.id
                        ORDER BY m1.id DESC");
$members = [];
while($row = $result->fetch_assoc()) $members[] = $row;

// Summary counts
$totalMembers = count($members);
$totalParents = count(array_filter($members, fn($m)=> strtolower($m['relation_type'])=='parent'));
$totalChildren = count(array_filter($members, fn($m)=> strtolower($m['relation_type'])=='child'));
$totalSiblings = count(array_filter($members, fn($m)=> strtolower($m['relation_type'])=='sibling'));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Family Tree — Pro (Frosted Blue)</title>

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <!-- html2canvas for PNG export -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

  <style>
  /* ---------- Base ---------- */
  :root{
    --bg-dark: #071228;
    --glass-weak: rgba(255,255,255,0.04);
    --glass-strong: rgba(255,255,255,0.06);
    --accent-a: #3b82f6; /* blue */
    --accent-b: #60a5fa; /* lighter blue */
    --accent-c: #7c3aed; /* purple */
    --muted: rgba(255,255,255,0.6);
    --card-radius: 12px;
  }
  *{ box-sizing: border-box; }
  body {
    margin:0; min-height:100vh;
    font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    background: linear-gradient(180deg, #041026 0%, #071228 50%, #0a1630 100%);
    color: #e8f0ff;
    -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
  }

  /* ---------- App layout (compact icon sidebar) ---------- */
  .app {
    display: grid;
    grid-template-columns: 84px 1fr;
    min-height:100vh;
  }

  /* ---------- Sidebar compact (icons) ---------- */
  .sidebar-compact {
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    border-right: 1px solid rgba(255,255,255,0.03);
    padding: 12px;
    display:flex; flex-direction:column; align-items:center; gap:14px;
    position:relative;
  }
  .sidebar-compact .logo {
    width:56px; height:56px; border-radius:14px;
    background: linear-gradient(135deg,var(--accent-a), var(--accent-b));
    display:flex; align-items:center; justify-content:center; font-weight:700; font-size:18px; color:white;
    box-shadow: 0 10px 30px rgba(59,130,246,0.18);
    transform: translateY(4px);
  }
  .side-icons { display:flex; flex-direction:column; gap:10px; margin-top:8px; align-items:center; width:100%; }
  .side-icons button {
    width:56px; height:56px; border-radius:12px; background:transparent; border:1px solid rgba(255,255,255,0.03);
    display:flex; align-items:center; justify-content:center; color:#dff2ff; cursor:pointer;
    transition: transform 160ms ease, background 160ms ease, box-shadow 160ms ease;
  }
  .side-icons button:hover { transform: translateX(6px) scale(1.03); background: linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0.03)); box-shadow: 0 8px 20px rgba(0,0,0,0.4); }
  .side-icons button i { font-size:18px; }

  /* tooltip label when hovered (expanded) */
  .tooltip-label {
    position: absolute; left:92px; padding:8px 12px; border-radius:10px; background: rgba(8,18,34,0.8);
    color: #dff2ff; font-weight:600; box-shadow: 0 8px 30px rgba(2,6,23,0.6); display:none;
    transform-origin:left center;
  }

  /* On large screens we show tooltip on hover */
  .side-icons button[data-label]:hover + .tooltip-label { display:block; animation: slideIn .18s ease; }
  @keyframes slideIn { from { transform: translateX(-6px) scale(.98); opacity:0 } to { transform: translateX(0) scale(1); opacity:1 } }

  /* collapse toggle bottom */
  .sidebar-bottom { margin-top:auto; display:flex; flex-direction:column; gap:8px; align-items:center; padding-bottom:8px; }

  /* ---------- Main column ---------- */
  main {
    padding: 22px 28px;
  }

  .top {
    display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px;
  }

  .search {
    display:flex; align-items:center; gap:8px; background: rgba(255,255,255,0.02); padding:10px 12px; border-radius:12px;
    border: 1px solid rgba(255,255,255,0.03); min-width:420px;
  }
  .search input {
    background:transparent; border:0; color: #e8f0ff; outline:none; font-weight:600; width:100%;
  }
  .top-actions { display:flex; gap:10px; align-items:center; }

  /* ---------- Cards Row ---------- */
  .grid {
    display:grid;
    grid-template-columns: 1fr 360px;
    gap:20px;
  }

  .left-col { display:flex; flex-direction:column; gap:18px; }
  .right-col { display:flex; flex-direction:column; gap:18px; }

  .glass {
    background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
    border-radius: var(--card-radius);
    padding:16px;
    border: 1px solid rgba(255,255,255,0.04);
    box-shadow: 0 12px 40px rgba(2,6,23,0.6);
    position:relative;
    overflow:hidden;
  }

  .overview-tiles { display:flex; gap:12px; }
  .tile {
    flex:1; padding:14px; border-radius:12px; background: linear-gradient(90deg, rgba(255,255,255,0.01), rgba(255,255,255,0.005));
    transition: transform 220ms ease, box-shadow 220ms ease;
  }
  .tile:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.45); }
  .tile .num { font-size:24px; font-weight:800; color:#fff; }
  .tile .lbl { color:var(--muted); font-size:13px; margin-top:6px; }

  /* small accent gradient on tiles */
  .tile .accent-bar { height:4px; width:72px; border-radius:6px; margin-bottom:8px;
    background: linear-gradient(90deg,var(--accent-a), var(--accent-b)); box-shadow: 0 6px 18px rgba(59,130,246,0.12);
  }

  /* ---------- Table ---------- */
  .table-wrap { border-radius:10px; overflow:hidden; margin-top:6px; }
  table.clean {
    width:100%; border-collapse:collapse; color:#e8f0ff;
    min-width:760px;
  }
  table.clean thead th { text-align:left; font-weight:700; padding:12px 14px; font-size:13px; color:#dff2ff; background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.03); }
  table.clean tbody tr { transition: transform .18s ease, box-shadow .18s ease, background .18s ease; cursor:default; }
  table.clean tbody tr:hover { transform: translateY(-6px); background: rgba(255,255,255,0.02); box-shadow: 0 18px 40px rgba(2,6,23,0.55); }
  table.clean td { padding:12px 14px; border-bottom:1px solid rgba(255,255,255,0.02); vertical-align:middle; }

  .avatar-sm { width:44px; height:44px; border-radius:10px; object-fit:cover; box-shadow: 0 6px 18px rgba(2,6,23,0.6); }

  .actions { display:flex; gap:8px; align-items:center; }

  .btn-pro {
    background: linear-gradient(90deg, var(--accent-a), var(--accent-b));
    color:white; padding:8px 10px; border-radius:10px; border:0; font-weight:700; cursor:pointer;
    transition: transform .12s ease;
  }
  .btn-pro:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(59,130,246,0.18); }

  .btn-ghost {
    background: transparent; color:#cfe8ff; border: 1px solid rgba(255,255,255,0.03); padding:8px 10px; border-radius:10px; cursor:pointer;
  }

  /* Right widgets */
  .compact-chart { height:220px; display:flex; align-items:center; justify-content:center; padding:12px; }

  /* detail drawer (client-side) */
  /* .drawer {
    position: fixed; right:20px; top:80px; width:40px; background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    border-radius:12px; padding:14px; box-shadow: 0 30px 80px rgba(2,6,23,0.7); display:none; z-index:200; border:1px solid rgba(255,255,255,0.03);
  }
  .drawer.show { display:block; animation: slideUp .22s ease; }
  @keyframes slideUp { from { transform: translateY(12px) scale(.98); opacity:0 } to { transform: translateY(0) scale(1); opacity:1 } } */

  .drawer {
    position: fixed;
    right: 20px;
    top: 80px;
    width: 360px; /* enough for details */
    background: rgba(30, 40, 60, 0.85);
    background: linear-gradient(145deg, rgba(50, 60, 90, 0.9) 0%, rgba(70, 80, 110, 0.8) 100%);
    backdrop-filter: blur(18px) saturate(180%);
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12), 0 8px 20px rgba(0, 0, 0, 0.08);
    display: none;
    z-index: 200;
    border: 1px solid rgba(100, 110, 140, 0.5);
    color: #e8f0ff; /* light text for contrast */
    transition: all 0.3s ease;
}

.drawer.show {
    display: block;
    animation: slideUp 0.22s ease;
}

@keyframes slideUp {
    from { transform: translateY(12px) scale(.98); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

  /* Floating FAB */
  .fab {
    position: fixed; right:22px; bottom:22px; width:64px; height:64px; border-radius:14px; display:flex; align-items:center; justify-content:center;
    background: linear-gradient(135deg,var(--accent-a), var(--accent-b)); color:white; font-size:22px; box-shadow: 0 18px 48px rgba(59,130,246,0.18);
    border:0; cursor:pointer;
  }
  .fab:hover { transform: translateY(-6px); }

  /* Responsive */
  @media (max-width: 980px){
    .app { grid-template-columns: 64px 1fr; }
    .search { min-width:200px; }
    .grid { grid-template-columns: 1fr; }
    .right-col { order: 2; }
  }
  @media (max-width: 700px){
    .app { grid-template-columns: 1fr; }
    .sidebar-compact { position:fixed; left:0; top:0; bottom:0; transform: translateX(-120%); transition: transform .22s ease; z-index:120; }
    .sidebar-compact.open { transform: translateX(0); }
    .search { min-width:140px; }
  }

  /* small utility */
  .muted { color:var(--muted); font-size:13px; }
  mark { background: rgba(96,165,250,0.16); color:inherit; padding:2px 6px; border-radius:6px; }
  body.light {
  background: #f4f5f7;
  color: #1f2937;
}

body.light .glass {
  background: rgba(255,255,255,0.9);
  color: #1f2937;
  border: 1px solid rgba(0,0,0,0.08);
}

body.light table.clean th,
body.light table.clean td {
  color: #1f2937;
  border-color: rgba(0,0,0,0.06);
}

body.light .tile .num { color: #111827; }
body.light .tile .lbl { color: #4b5563; }

body.light .sidebar-compact {
  background: #e5e7eb;
  border-right: 1px solid #d1d5db;
}

body.light .side-icons button {
  color: #1f2937;
  border: 1px solid #d1d5db;
}

body.light .btn-ghost {
  color: #1f2937;
  border: 1px solid #d1d5db;
}

body.light .drawer {
  background: rgba(255,255,255,0.95);
  color: #1f2937;
  border: 1px solid rgba(0,0,0,0.08);
}

  </style>
</head>
<body>

<div class="app">

  <!-- Compact Sidebar (icons only) -->
  <aside class="sidebar-compact" id="sidebar">
    <div class="logo" title="FamilyTree">FT</div>

    <div class="side-icons" role="navigation" aria-label="Main navigation">
      <button onclick="navigate('index.php')" data-label="Dashboard" title="Dashboard"><i class="fa fa-chart-column"></i></button>
      <span class="tooltip-label" id="label-dashboard">Dashboard</span>

      <button onclick="navigate('add_member.php')" data-label="Add Member" title="Add Member"><i class="fa fa-user-plus"></i></button>
      <span class="tooltip-label" id="label-add">Add</span>

      <button onclick="navigate('tree.php')" data-label="View Tree" title="View Tree"><i class="fa fa-sitemap"></i></button>
      <span class="tooltip-label" id="label-tree">Tree</span>

      <button onclick="exportJSON()" data-label="Export JSON" title="Export JSON"><i class="fa fa-file-export"></i></button>
      <span class="tooltip-label" id="label-json">Export JSON</span>

      <button onclick="exportPNG()" data-label="Export PNG" title="Export PNG"><i class="fa fa-image"></i></button>
      <span class="tooltip-label" id="label-png">Export PNG</span>

      <button onclick="navigate('Home.php')" data-label="Export PNG" title="Export PNG"><i class="fa fa-home"></i></button>
      <span class="tooltip-label" id="label-png">Home</span>
    </div>

    <div class="sidebar-bottom">
      <button class="btn-ghost" id="openMenu" title="Open menu"><i class="fa fa-bars"></i></button>
    </div>
  </aside>

  <!-- MAIN -->
  <main>
    <div class="top">
      <div style="display:flex; gap:12px; align-items:center;">
        <div style="font-size:20px; font-weight:700;">Dashboard</div>

        <div class="search" role="search" aria-label="Search members">
          <i class="fa fa-search" style="opacity:.8"></i>
          <input id="searchInput" placeholder="Search members, relation, gender, parent..." onkeyup="searchTable()">
          <button class="btn-ghost" onclick="focusSearch()" title="Focus search"><i class="fa fa-magnifying-glass"></i></button>
        </div>
      </div>

      <div class="top-actions">
        <div class="muted">Signed in as <strong>Kunal</strong></div>
        <button class="btn-ghost" id="themeBtn" title="Toggle theme"><i class="fa fa-moon"></i></button>
      </div>
    </div>

    <div class="grid">
      <div class="left-col">
        <!-- Overview -->
        <div class="glass">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-weight:800; font-size:16px;">Overview</div>
              <div class="muted" style="margin-top:4px;">Quick snapshot of family members</div>
            </div>
            <div class="muted">Updated now</div>
          </div>

          <div style="margin-top:14px;" class="overview-tiles">
            <div class="tile">
              <div class="accent-bar"></div>
              <div class="num" id="membersCount">0</div>
              <div class="lbl">Total Members</div>
            </div>
            <div class="tile">
              <div class="accent-bar" style="background:linear-gradient(90deg,#10b981,#60a5fa)"></div>
              <div class="num" id="parentsCount">0</div>
              <div class="lbl">Parents</div>
            </div>
            <div class="tile">
              <div class="accent-bar" style="background:linear-gradient(90deg,#f59e0b,#60a5fa)"></div>
              <div class="num" id="childrenCount">0</div>
              <div class="lbl">Children</div>
            </div>
            <div class="tile">
              <div class="accent-bar" style="background:linear-gradient(90deg,#ef4444,#60a5fa)"></div>
              <div class="num" id="siblingsCount">0</div>
              <div class="lbl">Siblings</div>
            </div>
          </div>
        </div>

        <!-- Members Table -->
        <div class="glass">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="font-weight:800; font-size:16px;">All Family Members</div>
              <div class="muted" style="margin-top:4px;">Click a row for quick details</div>
            </div>
            <div class="muted">Total: <?= $totalMembers ?></div>
          </div>

          <div class="table-wrap" style="margin-top:12px;">
            <table class="clean" id="membersTable" aria-describedby="tableDescription">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Photo</th>
                  <th>Name</th>
                  <th>Gender</th>
                  <th>Birth Date</th>
                  <th>Relation</th>
                  <th>Parent</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="membersTbody">
                <?php foreach($members as $m): ?>
                <tr onclick='openDrawer(<?= json_encode($m, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' role="button" aria-pressed="false">
                  <td><?= htmlspecialchars($m['id']) ?></td>
                  <td>
                    <?php if(!empty($m['profile_image']) && file_exists('uploads/'.$m['profile_image'])): ?>
                      <img src="uploads/<?= htmlspecialchars($m['profile_image']) ?>" class="avatar-sm" alt="profile">
                    <?php else: ?>
                      <div class="avatar-sm" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#172554,#1e293b);font-weight:700;color:#dff2ff;">
                        <?= strtoupper(substr($m['name'],0,1) ?: 'U') ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($m['name']) ?></td>
                  <td><?= htmlspecialchars($m['gender'] ?? '—') ?></td>
                  <td><?= htmlspecialchars($m['birth_date'] ?? '—') ?></td>
                  <td><?= $m['relation_type']=='root' ? 'Root' : htmlspecialchars($m['relation_type']) ?></td>
                  <td><?= htmlspecialchars($m['parent_name'] ?? '—') ?></td>
                  <td>
                    <div class="actions">
                      <a class="btn-ghost" href="edit_member.php?id=<?= urlencode($m['id']) ?>" title="Edit"><i class="fa fa-edit"></i></a>
                      <a class="btn-ghost" href="delete_member.php?id=<?= urlencode($m['id']) ?>" onclick="return confirm('Are you sure?')" title="Delete"><i class="fa fa-trash"></i></a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>

                <?php if(empty($members)): ?>
                <tr><td colspan="8" style="text-align:center; padding:20px; color:var(--muted)">No members found</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="right-col">
        <div class="glass compact-chart">
          <div style="width:100%; height:100%;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <div style="font-weight:800;">Relations</div>
              <div class="muted">By type</div>
            </div>
            <div style="height:160px; margin-top:10px;">
              <canvas id="relationChart" style="width:100%; height:100%;"></canvas>
            </div>
          </div>
        </div>

        <div class="glass">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div style="font-weight:800;">Tools</div>
            <div class="muted">Quick exports</div>
          </div>
          <div style="margin-top:12px; display:flex; gap:8px;">
            <button class="btn-pro" onclick="exportJSON()"><i class="fa fa-file-export me-2"></i>Export JSON</button>
            <button class="btn-pro" onclick="exportPNG()"><i class="fa fa-image me-2"></i>Export PNG</button>
            <a class="btn-pro" href="add_member.php" style="margin-left:auto; display:flex; align-items:center;"><i class="fa fa-plus me-2"></i>Add</a>
          </div>
        </div>
      </div>
    </div>

  </main>

</div>

<!-- Floating Add -->
<button class="fab" title="Add Member" onclick="location.href='add_member.php'"><i class="fa fa-plus"></i></button>

<!-- Drawer for details -->
<div class="drawer" id="drawer" role="dialog" aria-hidden="true">
  <div style="display:flex; align-items:center; gap:12px;">
    <div id="drAvatar" style="width:64px; height:64px; border-radius:10px; background:#0b1b2b; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:20px; color:#dff2ff;"></div>
    <div>
      <div id="drName" style="font-weight:800; font-size:16px;"></div>
      <div id="drRel" class="muted" style="margin-top:4px;"></div>
    </div>
    <button onclick="closeDrawer()" class="btn-ghost" style="margin-left:auto;"><i class="fa fa-xmark"></i></button>
  </div>
  <hr style="border-color: rgba(255,255,255,0.03); margin:12px 0;">
  <div style="display:flex; gap:12px;">
    <div style="flex:1;">
      <div class="muted">Gender</div>
      <div id="drGender" style="font-weight:700; margin-bottom:8px;"></div>

      <div class="muted">Birth Date</div>
      <div id="drBirth" style="font-weight:700; margin-bottom:8px;"></div>

      <div class="muted">Parent</div>
      <div id="drParent" style="font-weight:700; margin-bottom:8px;"></div>
    </div>
    <div style="width:100px;">
      <div class="muted">Actions</div>
      <div style="display:flex; flex-direction:column; gap:8px; margin-top:8px;">
        <a id="drEdit" class="btn-pro" href="#"><i class="fa fa-edit me-2"></i>Edit</a>
        <a id="drDelete" class="btn-pro" href="#" style="background:linear-gradient(90deg,#ff7b7b,#fb7185)"><i class="fa fa-trash me-2"></i>Delete</a>
      </div>
    </div>
  </div>
  <div style="margin-top:12px;">
    <div class="muted">Notes</div>
    <div id="drNotes" style="margin-top:6px; color:var(--muted); font-size:13px;"></div>
  </div>
</div>

<script>
/* ---------- Data from server ---------- */
const MEMBERS = <?= json_encode($members, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;

/* utilities */
function navigate(url){ location.href = url; }
function focusSearch(){ document.getElementById('searchInput').focus(); }

/* theme toggling (light/dark very subtle) */
/* ---------- Theme toggling (light/dark) ---------- */
const themeBtn = document.getElementById('themeBtn');

// Function to apply theme
function setTheme(theme) {
  if (theme === 'light') {
    document.body.classList.add('light');
    document.body.classList.remove('dark');
    localStorage.setItem('ft_theme', 'light');
    themeBtn.innerHTML = '<i class="fa fa-sun"></i>';
  } else {
    document.body.classList.add('dark');
    document.body.classList.remove('light');
    localStorage.setItem('ft_theme', 'dark');
    themeBtn.innerHTML = '<i class="fa fa-moon"></i>';
  }
}

// Toggle theme on button click
if (themeBtn) {
  themeBtn.addEventListener('click', () => {
    const currentTheme = localStorage.getItem('ft_theme') || 'dark';
    setTheme(currentTheme === 'dark' ? 'light' : 'dark');
  });

  // Apply theme on page load
  setTheme(localStorage.getItem('ft_theme') || 'dark');
}


/* animated counters */
function animateTo(el, to, duration=900){
  const start = 0, startTime = performance.now();
  function tick(now){
    const progress = Math.min((now-startTime)/duration, 1);
    const val = Math.floor(start + (to-start) * (1 - Math.pow(1-progress,3)));
    el.textContent = val;
    if(progress < 1) requestAnimationFrame(tick);
  }
  requestAnimationFrame(tick);
}
animateTo(document.getElementById('membersCount'), <?= (int)$totalMembers ?>);
animateTo(document.getElementById('parentsCount'), <?= (int)$totalParents ?>);
animateTo(document.getElementById('childrenCount'), <?= (int)$totalChildren ?>);
animateTo(document.getElementById('siblingsCount'), <?= (int)$totalSiblings ?>);

/* search with highlight */
function escapeHtml(s){ return s.replace(/[&<>"'`=\/]/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#96;','=':'&#61;'}[c]; }); }
function searchTable(){
  const q = document.getElementById('searchInput').value.trim().toLowerCase();
  const rows = document.querySelectorAll('#membersTbody tr');
  rows.forEach(r=>{
    const text = r.innerText.toLowerCase();
    if(!q){ r.style.display=''; // remove highlights
      r.querySelectorAll('td').forEach(td => td.innerHTML = td.textContent);
      return;
    }
    if(text.includes(q)){
      r.style.display='';
      r.querySelectorAll('td').forEach(td=>{
        const raw = td.textContent;
        const idx = raw.toLowerCase().indexOf(q);
        if(idx>-1){
          const before = escapeHtml(raw.slice(0,idx));
          const match = escapeHtml(raw.slice(idx, idx+q.length));
          const after = escapeHtml(raw.slice(idx+q.length));
          td.innerHTML = before + '<mark>' + match + '</mark>' + after;
        } else {
          td.innerHTML = escapeHtml(raw);
        }
      });
    } else {
      r.style.display='none';
    }
  });
}

/* export JSON */
function exportJSON(){
  const blob = new Blob([JSON.stringify(MEMBERS, null, 2)], {type:'application/json'});
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'family_members.json'; a.click();
  URL.revokeObjectURL(a.href);
}

/* export PNG of table */
function exportPNG(){
  const el = document.querySelector('.table-wrap');
  html2canvas(el, {scale:2, useCORS:true}).then(canvas => {
    const a = document.createElement('a'); a.href = canvas.toDataURL('image/png'); a.download = 'family_table.png'; a.click();
  }).catch(err => alert('Export failed: ' + err.message));
}

/* build relation donut chart */
function buildChart(){
  const cnt = {parent:0, child:0, sibling:0, root:0, other:0};
  MEMBERS.forEach(m=>{
    const t = (m.relation_type || 'other').toString().toLowerCase();
    if(cnt.hasOwnProperty(t)) cnt[t]++; else cnt.other++;
  });
  const labels = ['Parents','Children','Siblings','Root','Other'];
  const data = [cnt.parent, cnt.child, cnt.sibling, cnt.root, cnt.other];
  const ctx = document.getElementById('relationChart').getContext('2d');
  if(window._relationChart) window._relationChart.destroy();
  window._relationChart = new Chart(ctx, {
    type:'doughnut',
    data: {
      labels, datasets: [{
        data,
        backgroundColor: [
          'rgba(59,130,246,0.92)',
          'rgba(96,165,250,0.92)',
          'rgba(99,102,241,0.92)',
          'rgba(14,165,233,0.92)',
          'rgba(148,163,184,0.6)'
        ],
        borderWidth:0
      }]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      cutout:'62%',
      plugins: { legend: { position:'bottom', labels:{ color:'#dff2ff' } } }
    }
  });
}
buildChart();

/* drawer open */
const drawer = document.getElementById('drawer');
function openDrawer(obj){
  const m = obj;
  document.getElementById('drName').textContent = m.name || '—';
  document.getElementById('drRel').textContent = (m.relation_type || '—').toString();
  document.getElementById('drGender').textContent = m.gender || '—';
  document.getElementById('drBirth').textContent = m.birth_date || '—';
  document.getElementById('drParent').textContent = m.parent_name || '—';
  document.getElementById('drNotes').textContent = m.notes || '—';
  const avatar = document.getElementById('drAvatar');
  if(m.profile_image && m.profile_image.length){
    avatar.style.background = 'url("uploads/' + m.profile_image + '") center/cover no-repeat';
    avatar.textContent = '';
  } else {
    avatar.style.background = 'linear-gradient(135deg,#07203b,#18304b)';
    avatar.textContent = (m.name || 'U').slice(0,1).toUpperCase();
  }
  document.getElementById('drEdit').href = 'edit_member.php?id=' + encodeURIComponent(m.id);
  document.getElementById('drDelete').href = 'delete_member.php?id=' + encodeURIComponent(m.id);
  drawer.classList.add('show'); drawer.setAttribute('aria-hidden','false');
}
function closeDrawer(){ drawer.classList.remove('show'); drawer.setAttribute('aria-hidden','true'); }

/* animate table rows and fade-in */
window.addEventListener('load', ()=>{
  const rows = document.querySelectorAll('#membersTbody tr');
  rows.forEach((r,i)=>{
    r.style.opacity = 0; r.style.transform = 'translateY(10px)';
    setTimeout(()=>{ r.style.transition = 'opacity .45s ease, transform .45s cubic-bezier(.2,.9,.3,1)'; r.style.opacity = 1; r.style.transform = 'translateY(0)'; }, 70*i);
  });
});

/* small: open/close sidebar on mobile (openMenu) */
const openMenuBtn = document.getElementById('openMenu');
if(openMenuBtn){
  openMenuBtn.addEventListener('click', ()=>{
    const sb = document.querySelector('.sidebar-compact');
    sb.classList.toggle('open');
  });
}

/* keyboard escape closes drawer */
document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape'){ closeDrawer(); } });
</script>

</body>
</html>
