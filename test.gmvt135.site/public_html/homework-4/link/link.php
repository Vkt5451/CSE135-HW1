<?php
session_start(); 

if (!isset($_SESSION['isLoggedIn']) || $_SESSION['isLoggedIn'] !== true) {
    header("Location: ../login/login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework 4</title>
    <link href="link.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
    <script type="module" src="link.js"></script>
</head>
<body>
    <nav class="sidebar">
        <div class="logo-menu">
            <h2 class="logo">Hub</h2>
            <i class='bx bx-menu toggle-btn'></i>
        </div>
        <ul class="list">

            <li class="list-item">
                <a href="../dashboard/dashboard.php">
                    <i class='bx bx-user'></i>
                    <span class="link-name" style="--i:1;">Dashboard</span>
                </a>
            </li>
            <li class="list-item active">
                <a href="../link/link.php">
                    <i class='bx bx-analyse'></i>
                    <span class="link-name" style="--i:2;">Links</span>
                </a>
            </li>

            <li class="list-item">
                <a href="../login/logout.php">
                    <i class='bx bx-exit'></i>
                    <span class="link-name" style="--i:4;">Sign Out</span> 
                </a>
            </li>
            
            <li class="list-item">
                <a href="">
                    <i class='bx bx-git-commit'></i>
                    <span class="link-name" style="--i:5;">TBD</span>
                </a>
            </li>

        </ul>
    </nav>
    <main class="main-content">
        <div class="cardContainer">

            <div class="cardBox">
                <div class="cardContent">
                    <div class="numbers"></div>
                    <a href="https://test.gmvt135.site">
                        <div class="cardName">https://test.gmvt135.site/</div>
                    </a>
                </div>
                <div class="iconBx">
                    <i class='bx bx-link'></i>
                </div>
            </div>

            <div class="cardBox">
                <div class="cardContent">
                    <div class="numbers"></div>
                    <a href="https://collector.gmvt135.site/report.php">
                        <div class="cardName">https://collector.gmvt135.site/report.php</div>
                    </a>
                </div>
                <div class="iconBx">
                    <i class='bx bx-link'></i>
                </div>
            </div>

            <div class="cardBox">
                <div class="cardContent">
                    <div class="numbers"></div>
                    <a href="https://gmvt135.site/">
                        <div class="cardName">https://gmvt135.site/</div>
                    </a>
                </div>
                <div class="iconBx">
                    <i class='bx bx-link'></i>
                </div>
            </div>


        </div>


    </main>

</body>

</html>
