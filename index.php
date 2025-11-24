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

/**
 * Render a table of equipment whose quarterly inventory is all at or above the threshold.
 */
function renderAboveThresholdTable(array $inventory, int $threshold = 100): string
{
    $rows = [];
    foreach ($inventory as $item => $data) {
        $allAbove = count(array_filter($data, fn($qty) => $qty >= $threshold)) === count($data);
        if (!$allAbove) {
            continue;
        }
        $cells = '<td class="py-3 px-4 font-semibold text-white">' . htmlspecialchars($item) . '</td>';
        foreach ($data as $qty) {
            $cells .= '<td class="py-3 px-4 text-center text-slate-200">' . (int) $qty . '</td>';
        }
        $rows[] = '<tr class="hover:bg-white/5 transition">' . $cells . '</tr>';
    }

    if (empty($rows)) {
        return '<p class="text-slate-400">No equipment meets the threshold.</p>';
    }

    return '
        <table class="min-w-full text-sm text-left text-slate-200 border border-white/10 rounded-2xl overflow-hidden">
            <thead class="text-xs uppercase tracking-wide text-slate-400 bg-white/5">
                <tr>
                    <th class="py-3 px-4">Equipment</th>
                    <th class="py-3 px-4 text-center">Q1</th>
                    <th class="py-3 px-4 text-center">Q2</th>
                    <th class="py-3 px-4 text-center">Q3</th>
                    <th class="py-3 px-4 text-center">Q4</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10 bg-white/5">' . implode('', $rows) . '</tbody>
        </table>
    ';
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sport Inventory Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dusk: '#0f172a',
                        sky: '#38bdf8',
                        sun: '#f97316',
                        mint: '#22c55e',
                        sand: '#f8fafc'
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-dusk via-slate-900 to-black text-sand">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <header class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <p class="text-sky font-semibold uppercase tracking-[0.2em] text-sm">Sports Equipment</p>
                <h1 class="text-4xl sm:text-5xl font-bold text-white mt-2">Inventory Analytics</h1>
                <p class="text-slate-300 mt-2">Monitor quarterly supply health with real-time insights.</p>
            </div>
            <div class="flex items-center gap-2 bg-white/5 border border-white/10 text-sky rounded-full px-4 py-2 shadow-lg shadow-sky/10 backdrop-blur">
                <span class="text-lg">📊</span>
                <span class="text-sm font-semibold">Live Snapshot</span>
            </div>
        </header>

        <section class="grid md:grid-cols-3 gap-4 mt-8">
            <div class="bg-white/5 border border-white/10 rounded-2xl p-5 shadow-lg shadow-sky/10">
                <p class="text-slate-400 text-sm">Highest Quarter</p>
                <p class="text-2xl font-semibold text-white mt-1"><?= htmlspecialchars($highestQuarterLabel) ?></p>
                <div class="mt-3 h-2 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-sky w-10/12"></div>
                </div>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-5 shadow-lg shadow-sky/10">
                <p class="text-slate-400 text-sm">Lowest Quarter</p>
                <p class="text-2xl font-semibold text-white mt-1"><?= htmlspecialchars($lowestQuarterLabel) ?></p>
                <div class="mt-3 h-2 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-sun w-5/12"></div>
                </div>
            </div>
            <div class="bg-white/5 border border-white/10 rounded-2xl p-5 shadow-lg shadow-sky/10">
                <p class="text-slate-400 text-sm">Overall Average</p>
                <p class="text-2xl font-semibold text-white mt-1"><?= number_format($overallAverage, 2) ?></p>
                <div class="mt-3 h-2 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-mint w-8/12"></div>
                </div>
            </div>
        </section>

        <section class="bg-white/5 border border-white/10 rounded-3xl p-6 sm:p-8 mt-8 shadow-xl shadow-sky/10 backdrop-blur">
            <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
                <div>
                    <p class="text-slate-400 text-sm">Quarterly Snapshot</p>
                    <h2 class="text-2xl font-semibold text-white">Equipment Overview</h2>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-sky/20 text-sky border border-sky/30">Auto-sorted A → Z</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-slate-200">
                    <thead class="text-xs uppercase tracking-wide text-slate-400 border-b border-white/10">
                        <tr>
                            <th class="py-3 pr-4">Equipment</th>
                            <th class="py-3 px-4 text-center">Q1</th>
                            <th class="py-3 px-4 text-center">Q2</th>
                            <th class="py-3 px-4 text-center">Q3</th>
                            <th class="py-3 px-4 text-center">Q4</th>
                            <th class="py-3 pl-4 text-center">Average</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($sortedInventory as $item => $data): ?>
                            <?php
                                $avg = $averages[$item];
                                $avgClass = $avg >= 150 ? 'bg-mint/10 text-mint border-mint/30' : 'bg-sun/10 text-sun border-sun/30';
                            ?>
                            <tr class="hover:bg-white/5 transition">
                                <td class="py-3 pr-4 font-semibold text-white"><?= htmlspecialchars($item) ?></td>
                                <td class="py-3 px-4 text-center"><?= $data[0] ?></td>
                                <td class="py-3 px-4 text-center"><?= $data[1] ?></td>
                                <td class="py-3 px-4 text-center"><?= $data[2] ?></td>
                                <td class="py-3 px-4 text-center"><?= $data[3] ?></td>
                                <td class="py-3 pl-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-semibold <?= $avgClass ?>">
                                        <?= number_format($avg, 2) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid lg:grid-cols-2 gap-6 mt-8">
            <div class="bg-white/5 border border-white/10 rounded-3xl p-6 shadow-xl shadow-sky/10">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-slate-400 text-sm">Leaders</p>
                        <h3 class="text-xl font-semibold text-white">Average Inventory Ranking</h3>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full bg-white/10 text-slate-200 border border-white/10">High → Low</span>
                </div>
                <ol class="space-y-2 text-slate-100 list-decimal list-inside">
                    <?php foreach ($averageRanking as $item => $avg): ?>
                        <li class="flex items-center justify-between bg-white/5 px-3 py-2 rounded-xl border border-white/5">
                            <span class="font-semibold"><?= htmlspecialchars($item) ?></span>
                            <span class="text-slate-300"><?= number_format($avg, 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-3xl p-6 shadow-xl shadow-sky/10">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-slate-400 text-sm">Reliability Check</p>
                        <h3 class="text-xl font-semibold text-white">Stock ≥ 100 Every Quarter</h3>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full bg-mint/10 text-mint border border-mint/30">Consistent</span>
                </div>
                <div class="text-slate-200">
                    <?= renderAboveThresholdTable($sortedInventory, 100); ?>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
