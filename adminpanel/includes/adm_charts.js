//Управление графиками в админ-панели

document.addEventListener('DOMContentLoaded', function() {
    const chartsData = window.chartsData || {};
    
    document.querySelectorAll('.chart-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const wrapper = document.getElementById(targetId + 'Wrapper');
            const isVisible = wrapper.style.display === 'block';
            
            if (isVisible) {
                wrapper.style.display = 'none';
                this.innerHTML = '📊 Показать график';
            } else {
                wrapper.style.display = 'block';
                this.innerHTML = '📈 Скрыть график';
                
                if (!wrapper.hasAttribute('data-chart-created')) {
                    createChart(targetId);
                    wrapper.setAttribute('data-chart-created', 'true');
                }
            }
        });
    });
    
    function createChart(chartId) {
        switch(chartId) {
            case 'ratingChart':
                createLineChart(chartId, 'Рейтинг постов', ratingLabels, ratingData);
                break;
            case 'commentsChart':
                createLineChart(chartId, 'Количество комментариев', commentsLabels, commentsData);
                break;
            case 'authorsChart':
                createBarChart(chartId, 'Количество постов', authorsLabels, authorsData);
                break;
            case 'tagsChart':
                createPieChart(chartId, 'Популярность тегов', tagsLabels, tagsData);
                break;
        }
    }
    
    function createLineChart(chartId, title, labels, data) {
        const ctx = document.getElementById(chartId).getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: title,
                    data: data,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#ffc107',
                    pointBorderColor: '#1a1a1a',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: { color: '#ffffff' }
                    },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleColor: '#ffc107',
                        bodyColor: '#ffffff',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#333333' },
                        ticks: { color: '#b0b0b0' }
                    },
                    x: {
                        grid: { color: '#333333' },
                        ticks: { 
                            color: '#b0b0b0',
                            maxRotation: 15,
                            minRotation: 15
                        }
                    }
                }
            }
        });
    }
    
    function createBarChart(chartId, title, labels, data) {
        const ctx = document.getElementById(chartId).getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: title,
                    data: data,
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: '#ffc107',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: { color: '#ffffff' }
                    },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleColor: '#ffc107',
                        bodyColor: '#ffffff'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#333333' },
                        ticks: { color: '#b0b0b0' }
                    },
                    x: {
                        grid: { color: '#333333' },
                        ticks: { 
                            color: '#b0b0b0',
                            maxRotation: 15,
                            minRotation: 15
                        }
                    }
                }
            }
        });
    }
    
    function createPieChart(chartId, title, labels, data) {
        let displayLabels = labels;
        let displayData = data;
        
        if (labels.length > 10) {
            displayLabels = labels.slice(0, 10);
            displayData = data.slice(0, 10);
            const otherSum = data.slice(10).reduce((a, b) => a + b, 0);
            if (otherSum > 0) {
                displayData.push(otherSum);
            }
        }
        
        const trace = {
            labels: displayLabels,
            values: displayData,
            type: 'pie',
            marker: {
                colors: displayLabels.map((_, i) => `hsl(${i * 360 / displayLabels.length}, 70%, 55%)`)
            },
            textinfo: 'label+percent',
            textposition: 'auto',
            hoverinfo: 'label+value+percent',
            hole: 0.3
        };
        
        const layout = {
            title: {
                text: title,
                font: { color: '#ffffff', size: 16 }
            },
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor: 'rgba(0,0,0,0)',
            font: { color: '#b0b0b0' },
            margin: { t: 50, l: 30, r: 30, b: 30 },
            height: 450,
            width: null,
            showlegend: true,
            legend: {
                font: { color: '#b0b0b0', size: 11 },
                orientation: 'v',
                x: 1.02,
                y: 0.5,
                xanchor: 'left'
            }
        };
        
        Plotly.newPlot(chartId, [trace], layout, { responsive: true });
    }
});