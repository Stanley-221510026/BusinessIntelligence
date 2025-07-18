<?php
if (!isset($_SESSION)) session_start();

include 'includes/auth.php';
include 'includes/db.php';

$data = $_SESSION['csv_data'] ?? [];
$filterKategori = $_GET['kategori'] ?? '';

// Inisialisasi rentang dan kategori
$rangeLabels = ['0-10%', '11-20%', '21-30%', '31-40%', '41-50%', '51-60%', '61-70%', '71-80%', '81-90%', '91-100%'];
$rangeCounts = array_fill(0, count($rangeLabels), 0);
$kategoriList = [];

foreach ($data as $row) {
    $kategori = strtoupper(trim($row['KATEGORI'] ?? 'Tidak Diketahui'));
    if (!in_array($kategori, $kategoriList)) {
        $kategoriList[] = $kategori;
    }

    if ($filterKategori === '' || $kategori === strtoupper($filterKategori)) {
        $percent = isset($row['PERSENTASE PENJUALAN KOSONG']) ? floatval(str_replace(['%', ','], '', $row['PERSENTASE PENJUALAN KOSONG'])) : 0;
        $index = min(floor($percent / 10), 9);
        $rangeCounts[$index]++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chart Persentase Kosong</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .chart-container {
            width: 80%;
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        canvas {
            max-width: 100%;
        }
        form {
            text-align: center;
            margin-bottom: 20px;
        }
        select {
            padding: 8px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<?php include 'template/sidebar.php'; ?>
<div class="chart-container">
    <h2 align="center">Distribusi Persentase Penjualan Kosong</h2>
    <form method="get">
        <label for="kategori">Filter Kategori: </label>
        <select name="kategori" onchange="this.form.submit()">
            <option value="">Semua</option>
            <?php foreach ($kategoriList as $kategori): ?>
                <option value="<?= htmlspecialchars($kategori) ?>" <?= $filterKategori === $kategori ? 'selected' : '' ?>><?= htmlspecialchars($kategori) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <canvas id="barChart"></canvas>
</div>
<script>
    const ctx = document.getElementById('barChart').getContext('2d');
    const barChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($rangeLabels) ?>,
            datasets: [{
                label: 'Jumlah Item',
                data: <?= json_encode($rangeCounts) ?>,
                backgroundColor: '#00bbbb',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    formatter: function(value) {
                        return value;
                    },
                    font: {
                        weight: 'bold'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Jumlah: ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: Math.max(...<?= json_encode($rangeCounts) ?>) + 10,
                    ticks: {
                        stepSize: 20
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
</script>
</body>
</html>
