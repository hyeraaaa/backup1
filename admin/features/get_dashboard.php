<script>
    // Global chart instances
    let trendChart, smsChart, deptChart, yearLevelChart;

    // Chart color scheme
    const chartColors = {
        purple: '#6c5ce7',
        blue: '#0984e3',
        green: '#00b894',
        orange: '#e17055',
        red: '#d63031',
        yellow: '#fdcb6e'
    };

    // Common chart options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                titleColor: '#2d3436',
                bodyColor: '#2d3436',
                borderColor: '#dfe6e9',
                borderWidth: 1,
                padding: 12,
                displayColors: true,
                callbacks: {
                    label: function(context) {
                        return ` ${context.parsed}`;
                    }
                }
            }
        }
    };

    // Initialize all charts
    function initializeCharts() {
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function ($item) {
                            return date('M Y', strtotime($item['month']));
                        }, $monthly_stats)); ?>,
                datasets: [{
                    label: 'Announcements',
                    data: <?php echo json_encode(array_map(function ($item) {
                                return $item['count'];
                            }, $monthly_stats)); ?>,
                    borderColor: chartColors.green,
                    backgroundColor: `${chartColors.green}20`,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // SMS Chart
        const smsCtx = document.getElementById('smsChart').getContext('2d');
        smsChart = new Chart(smsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sent', 'Failed'],
                datasets: [{
                    data: [
                        <?php echo (isset($sms_stats[0]['count']) ? $sms_stats[0]['count'] : 0); ?>,
                        <?php echo (isset($sms_stats[1]['count']) ? $sms_stats[1]['count'] : 0); ?>
                    ],
                    backgroundColor: [chartColors.green, chartColors.red]
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    tooltip: {
                        ...commonOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.label}: ${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Department Chart
        const deptCtx = document.getElementById('deptChart').getContext('2d');
        deptChart = new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function ($item) {
                            return isset($item['department_name']) ? $item['department_name'] : 'Unknown';
                        }, $dept_stats)); ?>,
                datasets: [{
                    label: 'Announcements',
                    data: <?php echo json_encode(array_map(function ($item) {
                                return isset($item['count']) ? intval($item['count']) : 0;
                            }, $dept_stats)); ?>,
                    backgroundColor: chartColors.blue,
                    borderRadius: 6
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: '#f0f0f0'
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });

        // Year Level Chart
        const yearLevelCtx = document.getElementById('yearLevelChart').getContext('2d');
        yearLevelChart = new Chart(yearLevelCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_map(function ($item) {
                            return $item['year_level'];
                        }, $year_level_stats)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function ($item) {
                                return $item['count'];
                            }, $year_level_stats)); ?>,
                    backgroundColor: Object.values(chartColors)
                }]
            },
            options: commonOptions
        });
    }

    // Refresh dashboard data
    function refreshDashboard() {
        const refreshBtn = document.querySelector('.refresh-button button');
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise animate-spin"></i> Refreshing...';

        fetch('dashboard_data.php')
            .then(response => response.json())
            .then(data => {
                updateCharts(data);
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Data';
            })
            .catch(error => {
                console.error('Error refreshing dashboard:', error);
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Data';
            });
    }

    // Update trend chart period
    function updateTrendChart(period) {
        const buttons = document.querySelectorAll('[data-period]');
        buttons.forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-period="${period}"]`).classList.add('active');

        fetch(`dashboard_data.php?period=${period}`)
            .then(response => response.json())
            .then(data => {
                trendChart.data.labels = data.labels;
                trendChart.data.datasets[0].data = data.values;
                trendChart.update();
            });
    }

    // Initialize everything when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        document.querySelector('.loading-overlay').style.display = 'none';
    });

    // Add resize handler for responsive charts
    window.addEventListener('resize', function() {
        if (trendChart) trendChart.resize();
        if (smsChart) smsChart.resize();
        if (deptChart) deptChart.resize();
        if (yearLevelChart) yearLevelChart.resize();
    });
</script>