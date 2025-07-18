<?php
if (!isset($_SESSION)) session_start();
?>
<nav class="main-menu">
    <div class="logo" style="text-align: center; font-size: 20px; font-weight: bold; padding: 10px 0;">
        <a href="dashboard.php" style="color: inherit; text-decoration: none;">Monitoring</a>
    </div>
    <div class="scrollbar" id="style-1">
        <ul>
            <li><a href="dashboard.php"><i class="fa fa-table fa-lg"></i><span class="nav-text">Dashboard</span></a></li>
            <li><a href="upload.php"><i class="fa fa-upload fa-lg"></i><span class="nav-text">Upload Data</span></a></li>
            <li><a href="chart.php"><i class="fa fa-chart-bar fa-lg"></i><span class="nav-text">Grafik</span></a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out-alt fa-lg"></i><span class="nav-text">Logout</span></a></li>
        </ul>
    </div>
</nav>
