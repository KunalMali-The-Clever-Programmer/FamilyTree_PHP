<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Tree | Home</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        /* Gradient Background like Dashboard */
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            min-height: 100vh;
            color: white;
            overflow-x: hidden;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Frosted Glass Navbar */
        .navbar {
            width: 100%;
            padding: 18px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(14px);
            background: rgba(255,255,255,0.08);
            border-bottom: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 4px 20px rgba(0,0,0,0.35);
        }

        .navbar h2 {
            font-size: 32px;
            font-weight: 700;
            text-shadow: 0 0 8px rgba(0,255,255,0.5);
        }

        .navbar a {
            color: white;
            margin-left: 25px;
            font-size: 18px;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 12px;
            transition: 0.3s;
        }

        /* ACTIVE Link */
        .active-nav {
            background: #00eaff;
            color: black !important;
            box-shadow: 0 0 12px #00eaff;
        }

        .navbar a:hover {
            opacity: 0.8;
            transform: translateY(-2px);
        }

        /* Hero */
        .hero {
            text-align: center;
            margin-top: 70px;
            animation: fadeIn 1.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero h1 {
            font-size: 60px;
            font-weight: 700;
            line-height: 1.3;
            text-shadow: 0 0 14px rgba(0,255,255,0.5);
        }

        .hero p {
            font-size: 20px;
            margin-top: 18px;
            opacity: 0.9;
        }

        .hero-img {
            width: 420px;
            margin: 45px auto;
            animation: float 4s infinite ease-in-out;
        }

        /* Floating Animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0px); }
        }

        /* Feature Cards (from index theme) */
        .features {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 40px;
            padding: 20px;
            flex-wrap: wrap;
        }

        .card {
            width: 300px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 18px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.2);
            transition: 0.4s;
            box-shadow: 0 0 18px rgba(0,0,0,0.45);
        }

        .card:hover {
            transform: translateY(-10px) scale(1.06);
            box-shadow: 0 0 25px #00eaff;
        }

        .card i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #00eaff;
        }

        /* CTA Button */
        .cta-btn {
            display: inline-block;
            margin-top: 40px;
            padding: 15px 40px;
            font-size: 20px;
            color: black;
            background: #00eaff;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
            box-shadow: 0 0 18px #00eaff;
        }

        .cta-btn:hover {
            transform: scale(1.07);
            background: #00d4ff;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 80px;
            padding: 20px;
            opacity: 0.85;
            font-size: 15px;
        }
    </style>
</head>

<body>

    <!-- NAV -->
    <div class="navbar">
        <h2>👨‍👩‍👧 FamilyTree</h2>

        <div>
            <a href="home.php" class="active-nav">Home</a>
            <a href="index.php">Dashboard</a>
            <a href="add_member.php">Add Member</a>
            <a href="tree.php">View Tree</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- HERO -->
    <div class="hero">
        <h1>Explore & Build<br>Your Family Legacy</h1>
        <p>Track members, visualize relations, and grow your family digitally.</p>

        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="hero-img">
    </div>

    <!-- FEATURES -->
    <div class="features">

        <div class="card">
            <i class="fa fa-user-plus"></i>
            <h3>Add Members</h3>
            <p>Create parents, kids, siblings & more.</p>
        </div>

        <div class="card">
            <i class="fa fa-sitemap"></i>
            <h3>Visual Tree</h3>
            <p>Clean & modern relationship mapping.</p>
        </div>

        <div class="card">
            <i class="fa fa-download"></i>
            <h3>Backup</h3>
            <p>Export your full family database.</p>
        </div>

    </div>

    <!-- CTA -->
    <center>
        <a href="index.php" class="cta-btn">Go to Dashboard</a>
    </center>

    <!-- FOOTER -->
    <div class="footer">
        © 2025 FamilyTree App — Designed with ❤️ by Kunal Mali
    </div>

</body>
</html>
