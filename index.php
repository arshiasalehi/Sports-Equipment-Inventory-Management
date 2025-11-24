<?php

$dsn = 'mysql:host=localhost;dbname=sport;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    die('Database connection failed: ' . $e->getMessage());
}


$pdo->exec("
    CREATE TABLE IF NOT EXISTS equipment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(64) NOT NULL UNIQUE
    );
    CREATE TABLE IF NOT EXISTS stock (
        equipment_id INT NOT NULL,
        quarter VARCHAR(3) NOT NULL,
        quantity INT NOT NULL,
        PRIMARY KEY (equipment_id, quarter),
        CONSTRAINT fk_stock_equipment FOREIGN KEY (equipment_id)
            REFERENCES equipment(id) ON DELETE CASCADE
    );
");


function loadInventoryFromStock(PDO $pdo): array
{
    $inventory = [];
    $quarterIndex = ['Q1' => 0, 'Q2' => 1, 'Q3' => 2, 'Q4' => 3];
    $stmt = $pdo->query("
        SELECT e.name AS item, s.quarter, s.quantity
        FROM equipment e
        JOIN stock s ON s.equipment_id = e.id
        ORDER BY e.name, s.quarter
    ");

    foreach ($stmt as $row) {
        $item = $row['item'];
        $quarter = $row['quarter'];
        if (!isset($quarterIndex[$quarter])) {
            continue;
        }
        if (!isset($inventory[$item])) {
            $inventory[$item] = [0, 0, 0, 0];
        }
        $inventory[$item][$quarterIndex[$quarter]] = (int) $row['quantity'];
    }

    return $inventory;
}


// Load inventory from normalized tables only.
$inventory = loadInventoryFromStock($pdo);
if (empty($inventory)) {
    http_response_code(500);
    die('No inventory data found.');
}

$totals = [0, 0, 0, 0];
foreach ($inventory as $data) {
    foreach ($data as $q => $qty) {
        $totals[$q] += $qty;
    }
}

$quarterLabels = ['Q1', 'Q2', 'Q3', 'Q4'];
$highestQuarterIndex = array_search(max($totals), $totals, true);
$lowestQuarterIndex  = array_search(min($totals), $totals, true);
$highestQuarterLabel = $quarterLabels[$highestQuarterIndex];
$lowestQuarterLabel  = $quarterLabels[$lowestQuarterIndex];

$averages = [];
foreach ($inventory as $item => $data) {
    $averages[$item] = array_sum($data) / count($data);
}
$equipmentHighestAvg = array_search(max($averages), $averages, true);

$sortedInventory = $inventory;
ksort($sortedInventory);

$averageRanking = $averages;
arsort($averageRanking);

$overallAverage = array_sum($totals) / (count($inventory) * count($quarterLabels));

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sport Inventory Analytics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { margin-bottom: 0.2em; }
        table { border-collapse: collapse; width: 100%; margin-top: 1em; }
        th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: center; }
        th { background: #f3f3f3; }
        .avg-high { background: #e6f6e6; color: #0a7a0a; }
        .avg-low { background: #fff2e0; color: #c76b00; }
        .summary { margin-top: 0.5em; }
        .ranking { margin-top: 1em; }
    </style>
</head>
<body>
    <h1>Sport Inventory Analytics</h1>

    <div class="summary">
        <div><strong>Highest total quarter:</strong> <?= htmlspecialchars($highestQuarterLabel) ?> </div>
        <div><strong>Lowest total quarter:</strong> <?= htmlspecialchars($lowestQuarterLabel) ?> </div>
        <div><strong>Overall average inventory:</strong> <?= number_format($overallAverage, 2) ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Equipment</th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                <th>Average</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sortedInventory as $item => $data): ?>
                <?php
                    $avg = $averages[$item];
                    $avgClass = $avg >= 150 ? 'avg-high' : 'avg-low';
                ?>
                <tr class="<?= $avgClass ?>">
                    <td><?= htmlspecialchars($item) ?></td>
                    <td><?= $data[0] ?></td>
                    <td><?= $data[1] ?></td>
                    <td><?= $data[2] ?></td>
                    <td><?= $data[3] ?></td>
                    <td><?= number_format($avg, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="ranking">
        <strong>Average inventory ranking (desc):</strong>
        <ol>
            <?php foreach ($averageRanking as $item => $avg): ?>
                <li><?= htmlspecialchars($item) ?> — <?= number_format($avg, 2) ?></li>
            <?php endforeach; ?>
        </ol>
    </div>
</body>
</html>
