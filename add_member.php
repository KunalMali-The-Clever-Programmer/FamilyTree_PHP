<?php
include 'db.php';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $relation = $_POST['relation'];
    $relatedId = $_POST['relatedId'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $birth_date = $_POST['birth_date'] ?? null;
    $notes = $_POST['notes'] ?? null;
    $parentId = null;

    if($relation=='child' || $relation=='sibling'){
        $parentId = $relatedId;
        if($relation=='sibling'){
            $res = $conn->query("SELECT parent_id FROM members WHERE id=$relatedId");
            $parentId = $res->fetch_assoc()['parent_id'] ?? null;
        }
    } elseif($relation=='parent'){
        $parentId = null;
        if($relatedId){
            $conn->query("UPDATE members SET parent_id=LAST_INSERT_ID() WHERE id=$relatedId");
        }
    }

    $profile_image = null;
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error']==0){
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $profile_image = uniqid().'_'.time().'.'.$ext;
        move_uploaded_file($_FILES['profile_image']['tmp_name'], 'uploads/'.$profile_image);
    }

    $stmt = $conn->prepare("INSERT INTO members (name, parent_id, relation_type, gender, birth_date, notes, profile_image) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssss", $name, $parentId, $relation, $gender, $birth_date, $notes, $profile_image);
    $stmt->execute();
    header('Location: index.php');
}

$members = [];
$membersRes = $conn->query("SELECT * FROM members ORDER BY name ASC");
while($row = $membersRes->fetch_assoc()) $members[] = $row;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Member – FamilyTree Pro</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
:root{
  --bg-dark: #071228;
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
  display:flex;
  align-items:center;
  justify-content:center;
  padding:40px;
  width:100%;
}

.form-box{
  width:100%;
  max-width:540px;
  padding:50px;
  border-radius:var(--card-radius);
  background: var(--glass-strong);
  border:1px solid rgba(255,255,255,0.04);
  backdrop-filter:blur(25px);
  box-shadow:0 25px 90px var(--shadow-color);
  animation:fadeIn 0.9s ease;
}
@keyframes fadeIn { from{opacity:0;transform:translateY(20px);} to{opacity:1;transform:translateY(0);} }

.title{
  text-align:center;font-size:30px;font-weight:700;margin-bottom:35px;
  background:linear-gradient(90deg,var(--accent-a),var(--accent-b));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
}

/* Inputs */
label{
  font-weight:600;margin-top:20px;display:block;color:var(--muted);
}
input, select, textarea{
  width:100%;
  padding:14px 18px;
  margin-top:6px;
  border-radius:18px;
  background:var(--glass-weak);
  color:white;
  font-weight:500;
  border:none;
  outline:none;
  box-shadow:0 5px 15px rgba(0,0,0,0.2);
  transition:0.35s;
}
input:focus, select:focus, textarea:focus{
  background:var(--glass-strong);
  box-shadow:0 8px 25px rgba(66,211,255,0.5);
  transform:translateY(-2px);
}
textarea{height:110px;resize:none;}
select option{background: rgba(20,25,50,0.95);color:white;}

.save-btn{
  margin-top:30px;width:100%;padding:18px;
  background:linear-gradient(135deg,var(--accent-a),var(--accent-b));
  border:none;border-radius:25px;
  font-weight:700;font-size:18px;color:white;
  cursor:pointer;
  transition:0.35s;
  box-shadow:0 10px 25px rgba(66,211,255,0.3);
}
.save-btn:hover{
  transform:translateY(-3px) scale(1.05);
  box-shadow:0 14px 35px rgba(66,211,255,0.5);
}

/* Related field */
#relatedDiv{display:none;}
</style>

<script>
function toggleRelated(){
  const relation = document.getElementById("relation").value;
  document.getElementById("relatedDiv").style.display = 
     (relation === "root") ? "none" : "block";
}
</script>

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
      <form method="POST" enctype="multipart/form-data" class="form-box">

          <div class="title">Add New Family Member</div>

          <label>Name</label>
          <input type="text" name="name" required>

          <label>Relation</label>
          <select name="relation" id="relation" onchange="toggleRelated()">
              <option value="root">Root</option>
              <option value="parent">Parent</option>
              <option value="child">Child</option>
              <option value="sibling">Sibling</option>
          </select>

          <div id="relatedDiv">
              <label>Related To</label>
              <select name="relatedId">
                  <option value="">Select Member</option>
                  <?php foreach($members as $m): ?>
                      <option value="<?= $m['id'] ?>"><?= $m['name'] ?></option>
                  <?php endforeach; ?>
              </select>
          </div>

          <label>Gender</label>
          <select name="gender">
              <option value="">Select</option>
              <option value="M">Male</option>
              <option value="F">Female</option>
          </select>

          <label>Birth Date</label>
          <input type="date" name="birth_date">

          <label>Notes</label>
          <textarea name="notes"></textarea>

          <label>Profile Image</label>
          <input type="file" name="profile_image">

          <button class="save-btn">Save Member</button>
      </form>
  </main>

</div>
</body>
</html>
