<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/auth.php';
include 'includes/db.php';

$data = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== false) {
        // Skip BOM if present
        $firstLine = fgets($handle);
        if (substr($firstLine, 0, 3) === "\xEF\xBB\xBF") {
            $firstLine = substr($firstLine, 3);
        }
        $header = str_getcsv($firstLine);
        $header = array_map(function ($h) {
            return strtoupper(trim($h));
        }, $header);

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map('trim', $row);
            $rowAssoc = array_combine($header, $row);
            $data[] = array_change_key_case($rowAssoc, CASE_UPPER);
        }
        fclose($handle);
        $_SESSION['csv_data'] = $data;
    } else {
        $errors[] = "Gagal membuka file CSV.";
    }
} elseif (isset($_SESSION['csv_data'])) {
    $data = $_SESSION['csv_data'];
}

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard Penjualan</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
</head>
<body>
<div class="content" align="center">
    <h2>Dashboard Penjualan</h2>

    <?php if ($errors): ?>
        <div class="alert danger"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <?php if (!empty($data)): ?>

        <!-- Filter dan Search -->
        <form method="get" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Cari nama produk atau SKU..." value="<?= htmlspecialchars($search) ?>" style="padding: 8px; border-radius: 6px; width: 40%;">
            <select name="filter" style="padding: 8px; border-radius: 6px;">
                <option value="">Semua</option>
                <option value="danger" <?= $filter === 'danger' ? 'selected' : '' ?>>Kosong > 50% & < 90 hari</option>
                <option value="warning" <?= $filter === 'warning' ? 'selected' : '' ?>>Kosong > 50% / < 90 hari</option>
                <option value="safe" <?= $filter === 'safe' ? 'selected' : '' ?>>Aman</option>
            </select>
            <button type="submit" style="padding: 8px 16px; border: none; background-color: teal; color: white; border-radius: 6px;">Terapkan</button>
        </form>

        <table>
            <tr>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Stok Perjalanan</th>
                <th>Total Penjualan</th>
                <th>Persen Kosong</th>
                <th>Perkiraan Habis</th>
                <th>Notifikasi</th>
            </tr>
            <?php foreach ($data as $row): 
                $sku = $row['SKU'] ?? '-';

                $nama = '-';
                foreach ($row as $key => $val) {
                    if (trim(strtoupper($key)) === 'NAMA PRODUK') {
                        $nama = $val;
                        break;
                    }
                }

                $kategori = '-';
                foreach ($row as $key => $val) {
                    if (trim(strtoupper($key)) === 'KATEGORI') {
                        $kategori = $val;
                        break;
                    }
                }

                $stok = isset($row['STOK']) ? (int) str_replace([',', '.'], '', $row['STOK']) : 0;
                $stok_perjalan = isset($row['STOK PERJALANAN']) ? (int) str_replace([',', '.'], '', $row['STOK PERJALANAN']) : 0;
                $total = isset($row['TOTAL PENJUALAN']) ? (int) str_replace([',', '.'], '', $row['TOTAL PENJUALAN']) : 0;
                $persen_kosong = isset($row['PERSENTASE PENJUALAN KOSONG']) ? (float) str_replace(['%', ','], '', $row['PERSENTASE PENJUALAN KOSONG']) : 0;
                $avg = $total / 31;
                $hari_habis = $avg > 0 ? floor(($stok + $stok_perjalan) / $avg) : '-';

                $notif = 'safe';
                if ($persen_kosong > 50 && is_numeric($hari_habis) && $hari_habis < 90) {
                    $notif = 'danger';
                } elseif ($persen_kosong > 50 || (is_numeric($hari_habis) && $hari_habis < 90)) {
                    $notif = 'warning';
                }

                if (
                    ($search && stripos($nama, $search) === false && stripos($sku, $search) === false) ||
                    ($filter && $notif !== $filter)
                ) {
                    continue;
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($sku) ?></td>
                <td><?= htmlspecialchars($nama) ?></td>
                <td><?= htmlspecialchars($kategori) ?></td>
                <td><?= $stok ?></td>
                <td><?= $stok_perjalan ?></td>
                <td><?= $total ?></td>
                <td><?= $persen_kosong ?>%</td>
                <td><?= is_numeric($hari_habis) ? $hari_habis . " hari" : "-" ?></td>
                <td>
                    <?php
                    if ($notif === 'danger') {
                        echo "<span class='alert danger'>⚠️ Kosong > 50% & < 90 hari</span>";
                    } elseif ($notif === 'warning') {
                        echo "<span class='alert warning'>❗ Kosong > 50% atau < 90 hari</span>";
                    } else {
                        echo "<span class='alert success'>✅ Aman</span>";
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Silakan upload file melalui <a href="upload.php">halaman upload</a>.</p>
    <?php endif; ?>
</div>
<?php include 'template/sidebar.php'; ?>
</body>
</html>
