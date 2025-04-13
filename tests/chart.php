<?php

?>

<!DOCTYPE html>
<html>
<head>
    <title>Line Chart with Title</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="./public/asset/js/chart.js/dist/chart.js"></script> -->
</head>
<body>
    <canvas id="myLineChart" width="400" height="200"></canvas>
    <script>
        const ctx = document.getElementById('myLineChart').getContext('2d');
        const myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May'],
                datasets: [{
                    label: 'Sales',
                    data: [10, 20, 30, 40, 50],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Sales Data',
                        color: 'blue',
                        font: {
                            size: 20,
                            family: 'Arial',
                            weight: 'bold'
                        },
                        position: 'top',
                        padding: {
                            top: 10,
                            bottom: 10
                        }
                    }
                },
                responsive: true
            }
        });
    </script>
</body>
</html>
