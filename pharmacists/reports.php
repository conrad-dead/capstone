<?php
    session_start();
    $roleName = isset($_SESSION['user_role_name']) ? strtolower($_SESSION['user_role_name']) : '';
    if (!isset($_SESSION['user_id']) || !in_array($roleName, ['pharmacist','pharmacists'], true)) {
        header('Location: ../login.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist - Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { font-family: 'Inter', sans-serif; } </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <div class="flex flex-col h-screen bg-gray-800 text-white w-64 text-lg">
            <div class="mb-8 py-4">
                <h1 class="text-3xl font-extrabold text-white tracking-wide leading-none px-6 py-4 border-b border-gray-700">RHU GAMU</h1>
            </div>
            <nav class="flex-1 overflow-y-auto">
                <div class="px-2 py-4 space-y-1">
                    <a href="./pharmacists_dashboard.php" class="flex items-center space-x-2 py-2.5 px-4 rounded text-gray-300 hover:bg-gray-700">Dashboard</a>
                    <a href="./manage_inventory.php" class="flex items-center space-x-2 py-2.5 px-4 rounded text-gray-300 hover:bg-gray-700">Manage Inventory</a>
                    <div class="flex items-center space-x-2 py-2.5 px-4 rounded bg-gray-900 text-white">Reports</div>
                </div>
            </nav>
            <div class="px-2 border-t border-gray-700">
                <a href="../logout.php" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700">Logout</a>
            </div>
        </div>
        <div class="flex-1 p-6">
            <header class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Reports</h1>
            </header>
            <main class="space-y-8">
                <section class="bg-white rounded-lg shadow-xl p-8">
                    <h2 class="text-xl font-semibold mb-4">Top Distributed Drugs</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">This Month</h3>
                            <canvas id="chartTopMonth" height="200"></canvas>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2">This Year</h3>
                            <canvas id="chartTopYear" height="200"></canvas>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
    <script>
        async function fetchTop(period) {
            const response = await fetch('../api/drug_api.php?resource=distributions&aggregate=top&period=' + period);
            return response.json();
        }
        (async () => {
            const [m, y] = await Promise.all([fetchTop('month'), fetchTop('year')]);
            const monthLabels = (m.data || []).map(r => r.name);
            const monthData = (m.data || []).map(r => parseInt(r.total_given));
            const yearLabels = (y.data || []).map(r => r.name);
            const yearData = (y.data || []).map(r => parseInt(r.total_given));
            const ctxM = document.getElementById('chartTopMonth').getContext('2d');
            const ctxY = document.getElementById('chartTopYear').getContext('2d');
            new Chart(ctxM, { type: 'bar', data: { labels: monthLabels, datasets: [{ label: 'Qty Given (Month)', data: monthData, backgroundColor: '#60a5fa' }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
            new Chart(ctxY, { type: 'bar', data: { labels: yearLabels, datasets: [{ label: 'Qty Given (Year)', data: yearData, backgroundColor: '#34d399' }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
        })();
    </script>
</body>
</html>


