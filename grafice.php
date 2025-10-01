<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$rol = $_SESSION['rol'];

$dataStart = $_GET['start'] ?? null;
$dataEnd = $_GET['end'] ?? null;

$conditiePerioada = '';
if ($dataStart && $dataEnd) {
    $dataStartSQL = $conn->real_escape_string($dataStart);
    $dataEndSQL = $conn->real_escape_string($dataEnd);
    $conditiePerioada = "WHERE data_checkin BETWEEN '$dataStartSQL' AND '$dataEndSQL'";
}

$result = $conn->query("
    SELECT DATE_FORMAT(data_checkin, '%Y-%m') as luna, SUM(total) as venit_total
    FROM rezervari
    $conditiePerioada
    GROUP BY luna
    ORDER BY luna ASC
");

$data = [];
while($row = $result->fetch_assoc()) {
    $data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Vizualizare Grafice</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="dashboard.css">
<style>
.grafice-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    text-align: center;
    font-family: 'Poppins', sans-serif;
}

.grafice-container h2 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 28px;
    font-weight: 700;
    color: #00796b;
}

form {
    margin-bottom: 20px;
}

#chartType, input[type="date"], button {
    margin: 10px 5px;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 15px;
    border: 1px solid #ccc;
    
}
input[type="date"], button {
    margin: 10px 5px;
}

#chartType {
    margin: 20px 5px 30px 5px;
}

button {
    background-color: #00796b;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

button:hover {
    background-color: #004d40;
}

canvas {
    width: 80% !important;
    max-width: 900px;  
    height: 400px !important;
    margin: 0 auto;
}

</style>
</head>
<body>

<nav class="navbar">
    <div class="logo"><img src="imagini/logo.png" alt="logo"></div>
    <ul class="menu">
        <li><a href="destinatii.php">Destinații</a></li>
        <li><a href="comentarii.php">De ce să ne alegi?</a></li>
        <li><a href="rezervari.php">Rezervări</a></li>

        <?php if ($rol === 'admin'): ?>
        <li class="dropdown">
            <a href="#">Vizualizare</a>
            <div class="dropdown-content">
                <a href="view_destinatie.php">Destinație</a>
                <a href="view_hotel.php">Hotel</a>
                <a href="view_oferta.php">Oferta</a>
                <a href="view_rezervare.php">Rezervare</a>
            </div>
        </li>
        <li class="dropdown">
            <a href="#">Inserare</a>
            <div class="dropdown-content">
                <a href="inserare_destinatie.php">Destinație</a>
                <a href="inserare_hotel.php">Hotel</a>
                <a href="inserare_oferta.php">Oferta</a>
                <a href="inserare_rezervare.php">Rezervare</a>
            </div>
        </li>
        <li class="dropdown">
            <a href="#">Modificare</a>
            <div class="dropdown-content">
                <a href="edit_destinatie.php">Destinație</a>
                <a href="edit_hotel.php">Hotel</a>
                <a href="edit_oferta.php">Oferta</a>
                <a href="edit_rezervare.php">Rezervare</a>
            </div>
        </li>
        <li class="dropdown">
            <a href="#">Ștergere</a>
            <div class="dropdown-content">
                <a href="delete_destinatie.php">Destinație</a>
                <a href="delete_hotel.php">Hotel</a>
                <a href="delete_oferta.php">Oferta</a>
                <a href="delete_rezervare.php">Rezervare</a>
            </div>
        </li>

        <li><a href="rapoarte.php">Rapoarte</a></li>
        <li><a href="grafice.php">Grafice</a></li>
        <li class="dropdown">
            <a href="#">Export</a>
            <div class="dropdown-content">
                <a href="export_pdf.php">Exportă în PDF</a>
                <a href="export_excel.php">Exportă în XLS</a>
            </div>
        </li>
        <li><a href="import.php">Import</a></li>
        <?php endif; ?>

        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="grafice-container">
    <h2>Vizualizare Grafice</h2>

    <form method="GET">
        <label>De la: </label>
        <input type="date" name="start" value="<?= htmlspecialchars($dataStart) ?>" required>
        <label>Până la: </label>
        <input type="date" name="end" value="<?= htmlspecialchars($dataEnd) ?>" required>
        <button type="submit">Filtrează</button>
    </form>

    <?php if ($dataStart && $dataEnd): ?>
        <h4 style="margin-top: 10px;">Perioadă selectată: <?= htmlspecialchars($dataStart) ?> - <?= htmlspecialchars($dataEnd) ?></h4>
    <?php endif; ?>

    <select id="chartType">
        <option value="bar">Bar</option>
        <option value="histogram">Histogram</option>
        <option value="line">Line</option>
        <option value="pie">Pie</option>
    </select>

    <canvas id="myChart"></canvas>
</div>

<script>
const rawData = <?= json_encode($data); ?>;
const ctx = document.getElementById('myChart').getContext('2d');
let chart;

function createChart(type) {
    if (chart) chart.destroy();

    const labels = rawData.map(item => item.luna);
    const data = rawData.map(item => item.venit_total);

    const config = {
        type: type === 'histogram' ? 'bar' : type,
        data: {
            labels: labels,
            datasets: [{
                label: type === 'histogram' ? 'Frecvența' : 'Venituri (€)',
                data: data,
                backgroundColor: type === 'pie' ? [
                    'rgba(0, 150, 136, 0.6)',
                    'rgba(0, 188, 212, 0.6)',
                    'rgba(0, 102, 204, 0.6)',
                    'rgba(255, 193, 7, 0.6)',
                    'rgba(244, 67, 54, 0.6)',
                    'rgba(103, 58, 183, 0.6)'
                ] : 'rgba(0, 150, 136, 0.7)',
                borderWidth: type === 'histogram' ? 1 : 2,
                borderColor: 'rgba(0, 0, 0, 0.5)',
                barPercentage: type === 'histogram' ? 1.0 : 0.5,
                categoryPercentage: type === 'histogram' ? 1.0 : 0.7,
                fill: false,
                pointRadius: type === 'line' ? 5 : 0,
                tension: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: type !== 'line'
                }
            },
            scales: (type !== 'pie') ? {
                x: {
                    title: {
                        display: true,
                        text: type === 'histogram' ? 'Intervale total €' : 'Lună'
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: type === 'histogram' ? 'Frecvența' : 'Valori €'
                    },
                    beginAtZero: true
                }
            } : {}
        }
    };

    chart = new Chart(ctx, config);
}

document.getElementById('chartType').addEventListener('change', function() {
    createChart(this.value);
});

createChart('bar');
</script>

</body>
</html>
