<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload CSV</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="content" align="center">
    <h2>Upload File Penjualan (.csv)</h2>
    <form action="dashboard.php" method="post" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required><br><br>
        <button type="submit">Upload & Analisis</button>
    </form>
</div>
<?php include 'template/sidebar.php'; ?>
</body>
</html>
