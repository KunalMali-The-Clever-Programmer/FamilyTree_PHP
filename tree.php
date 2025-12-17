<?php 
include 'db.php';

// Fetch all members
$result = $conn->query("SELECT id, name, relation_type, profile_image, parent_id FROM members ORDER BY id ASC");
$members = [];
while($row = $result->fetch_assoc()){
    $members[$row['id']] = $row;
}

// Get children for a given member
function getChildren($members, $parentId){
    $children = [];
    foreach($members as $m){
        if(isset($m['parent_id']) && $m['parent_id'] == $parentId){
            $children[] = $m;
        }
    }
    return $children;
}

// Recursive tree display
function displayTree($members, $member){
    $children = getChildren($members, $member['id']);
    $hasChildren = !empty($children);

    echo '<li>';
        echo '<div class="node">';
            echo '<div class="profile" style="background-image:url(\'uploads/' . htmlspecialchars($member['profile_image']) . '\');"></div>';
            echo '<div class="info">';
                echo '<div class="name">' . htmlspecialchars($member['name']) . '</div>';
                echo '<div class="relation">' . htmlspecialchars($member['relation_type']) . '</div>';
            echo '</div>';
            if($hasChildren){
                echo '<button class="toggle-btn">+</button>';
            }
        echo '</div>';

        if($hasChildren){
            echo '<ul class="children">';
                foreach($children as $child){
                    displayTree($members, $child);
                }
            echo '</ul>';
        }
    echo '</li>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Family Tree – FamilyTree Pro</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root{
  --bg-dark: #041026;
  --glass-weak: rgba(255,255,255,0.04);
  --glass-strong: rgba(255,255,255,0.06);
  --accent-a: #3b82f6;
  --accent-b: #60a5fa;
  --accent-c: #7c3aed;
  --muted: rgba(255,255,255,0.6);
  --card-radius: 12px;
  --shadow-color: rgba(0,0,0,0.35);
}

*{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',sans-serif;}
body{
  min-height:100vh;
  background: linear-gradient(180deg, #041026 0%, #071228 50%, #0a1630 100%);
  display:flex;
  color:white;
}

/* Layout */
.app{display:grid;grid-template-columns:84px 1fr;width:100%;}

/* Sidebar compact */
.sidebar-compact{
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
  border-right:1px solid rgba(255,255,255,0.03);
  display:flex;flex-direction:column;align-items:center;padding:12px;
}
.logo{
  width:56px;height:56px;border-radius:14px;
  background:linear-gradient(135deg,var(--accent-a),var(--accent-b));
  display:flex;align-items:center;justify-content:center;
  font-weight:700;font-size:18px;color:white;cursor:pointer;
  box-shadow:0 10px 30px rgba(59,130,246,0.18);
}
.side-icons{display:flex;flex-direction:column;gap:10px;margin-top:8px;width:100%;}
.side-icons button{
  width:56px;height:56px;border-radius:12px;
  background:transparent;border:1px solid rgba(255,255,255,0.03);
  color:#dff2ff;font-size:18px;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  transition: transform 160ms ease, background 160ms ease, box-shadow 160ms ease;
}
.side-icons button:hover{
  transform: translateX(6px) scale(1.03);
  background: linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0.03));
  box-shadow: 0 8px 20px rgba(0,0,0,0.4);
}

/* Main content */
main{
  padding:40px;
  width:100%;
  display:flex;
  justify-content:center;
  overflow-x:auto;
}

/* Tree container */
.tree, .tree ul {
    padding:0;
    margin:0;
    list-style:none;
    display:flex;
    justify-content:center;
    position:relative;
    flex-wrap: wrap;
}

/* Tree nodes */
.tree li {
    display:flex;
    flex-direction:column;
    align-items:center;
    position:relative;
    padding:30px 5px 0 5px;
    transition: all 0.3s ease;
}

/* Connector lines */
.tree li::before, .tree li::after {
    content:"";
    position:absolute;
    top:0;
    width:50%;
    height:40px;
    border-top:2px solid white; /* visible on dark bg */
}
.tree li::before {
    left:50%;
    border-left:2px solid white;
    border-radius:0 0 0 10px;
}
.tree li::after {
    right:50%;
    border-right:2px solid white;
    border-radius:0 0 10px 0;
}
.tree li:only-child::before, .tree li:only-child::after { display:none; }

/* Vertical line to parent */
.tree ul::before {
    content:"";
    position:absolute;
    top:0;
    left:50%;
    width:0;
    height:40px;
    border-left:2px solid white;
}

/* Node styling */
.node {
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:10px;
    background: transparent;
    padding:12px 16px;
    border-radius:12px;
    box-shadow:none;
    position: relative;
    transition: transform 0.3s ease;
}
.node:hover { transform: translateY(-5px) scale(1.05); }

/* Profile circle */
.profile {
    width:90px;
    height:90px;
    border-radius:50%;
    background-size:cover;
    background-position:center;
    border:3px solid var(--accent-a);
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
}

/* Info text */
.info {
    display:flex;
    flex-direction:column;
    align-items:center;
    text-align:center;
    padding:6px 10px;
    min-width:100px;
}
.info .name { font-weight:700; font-size:16px; color:white; }
.info .relation { font-weight:bold; font-size:14px; color:var(--accent-b); }

/* Toggle button */
.toggle-btn {
    margin-top:6px;
    padding:2px 6px;
    border:none;
    border-radius:6px;
    background:var(--accent-a);
    color:white;
    cursor:pointer;
    font-weight:bold;
    transition: background 0.3s ease;
}
.toggle-btn:hover { background:var(--accent-b); }
.children { display:block; transition: max-height 0.5s ease; }
.collapsed > .children { display:none; }

/* Responsive */
@media(max-width:1200px){ main{overflow-x:auto;} }
@media(max-width:800px){
    .tree, .tree ul { flex-direction:column; align-items:center; }
    .tree li::before, .tree li::after, .tree ul::before { display:none; }
}
</style>
</head>
<body>
<div class="app">
  <!-- Sidebar -->
  <aside class="sidebar-compact">
      <div class="logo">FT</div>
      <div class="side-icons">
        <button onclick="location.href='index.php'"><i class="fa fa-chart-column"></i></button>
        <button onclick="location.href='add_member.php'"><i class="fa fa-user-plus"></i></button>
        <button onclick="location.href='tree.php'"><i class="fa fa-sitemap"></i></button>
        <button onclick="exportJSON()" data-label="Export JSON" title="Export JSON"><i class="fa fa-file-export"></i></button>
        <button onclick="exportPNG()" data-label="Export PNG" title="Export PNG"><i class="fa fa-image"></i></button>
        <button onclick="location.href='Home.php'"><i class="fa fa-home"></i></button>
      </div>
  </aside>

  <!-- Main section -->
  <main>
    <ul class="tree">
    <?php
    // Display root nodes (no parent)
    foreach($members as $member){
        if(!isset($member['parent_id']) || $member['parent_id']==0 || $member['parent_id']===null){
            displayTree($members, $member);
        }
    }
    ?>
    </ul>
  </main>
</div>

<script>
// Toggle children display
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const parentLi = btn.closest('li');
        parentLi.classList.toggle('collapsed');
        btn.textContent = parentLi.classList.contains('collapsed') ? '+' : '−';
    });
});
</script>
</body>
</html>
