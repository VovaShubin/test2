<?php
use yii\helpers\Html;
$this->title = 'График баланса';
?>
<h1><?= Html::encode($this->title) ?></h1>
<canvas id="balanceChart" width="800" height="400"></canvas>
<a href="<?= \yii\helpers\Url::to(['index']) ?>" class="btn btn-secondary mt-3">Загрузить другой файл</a>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const balances = <?= json_encode($balances) ?>;
const ctx = document.getElementById('balanceChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: balances.map((_, i) => i + 1),
        datasets: [{
            label: 'Баланс',
            data: balances,
            borderColor: 'blue',
            fill: false,
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: { enabled: true },
            legend: { display: true }
        },
        scales: {
            x: { title: { display: true, text: 'Сделка' } },
            y: { title: { display: true, text: 'Баланс' }, beginAtZero: true }
        }
    }
});
</script> 