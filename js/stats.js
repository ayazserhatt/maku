/**
 * Statistics and Chart functionality for MAKÜ Online Learning Platform
 * Author: MAKÜ IT Department
 */

document.addEventListener('DOMContentLoaded', function() {
    // Function to generate random colors for charts
    function generateColors(count) {
        const colors = [];
        const baseColors = [
            '#1A3C34', // MAKÜ Green
            '#800020', // MAKÜ Maroon
            '#3498db', // Blue
            '#f1c40f', // Yellow
            '#e74c3c', // Red
            '#2ecc71', // Green
            '#9b59b6', // Purple
            '#1abc9c', // Turquoise
            '#d35400', // Orange
            '#34495e'  // Dark Blue
        ];
        
        // Use base colors if count is less than or equal to baseColors length
        if (count <= baseColors.length) {
            return baseColors.slice(0, count);
        }
        
        // Add base colors first
        colors.push(...baseColors);
        
        // Generate additional colors if needed
        for (let i = baseColors.length; i < count; i++) {
            const r = Math.floor(Math.random() * 200);
            const g = Math.floor(Math.random() * 200);
            const b = Math.floor(Math.random() * 200);
            colors.push(`rgb(${r}, ${g}, ${b})`);
        }
        
        return colors;
    }
    
    // Function to get chart data from HTML elements
    function getDataFromElements(selector, attribute) {
        const elements = document.querySelectorAll(selector);
        const data = [];
        
        elements.forEach(element => {
            if (element.hasAttribute(attribute)) {
                data.push(element.getAttribute(attribute));
            } else {
                data.push(element.textContent.trim());
            }
        });
        
        return data;
    }
    
    // Function to create percentage distribution chart
    function createDistributionChart(canvas, labels, values) {
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const colors = generateColors(labels.length);
        
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 12
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.formattedValue;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((context.raw / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Function to create bar chart
    function createBarChart(canvas, labels, values, label, color) {
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label || 'Data',
                    data: values,
                    backgroundColor: color || '#1A3C34',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + (label.includes('%') ? '%' : '');
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: label ? true : false
                    }
                }
            }
        });
    }
    
    // Function to create line chart
    function createLineChart(canvas, labels, values, label, color) {
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label || 'Data',
                    data: values,
                    fill: true,
                    backgroundColor: color ? `${color}20` : 'rgba(26, 60, 52, 0.2)',
                    borderColor: color || '#1A3C34',
                    tension: 0.4,
                    pointBackgroundColor: color || '#1A3C34',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + (label.includes('%') ? '%' : '');
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Function to create radar chart
    function createRadarChart(canvas, labels, datasets) {
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const colors = generateColors(datasets.length);
        
        const chartDatasets = datasets.map((dataset, index) => {
            return {
                label: dataset.label,
                data: dataset.data,
                fill: true,
                backgroundColor: `${colors[index]}40`,
                borderColor: colors[index],
                pointBackgroundColor: colors[index],
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: colors[index]
            };
        });
        
        return new Chart(ctx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: chartDatasets
            },
            options: {
                elements: {
                    line: {
                        borderWidth: 2
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0
                    }
                }
            }
        });
    }
    
    // Function to create stacked bar chart
    function createStackedBarChart(canvas, labels, datasets) {
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const colors = generateColors(datasets.length);
        
        const chartDatasets = datasets.map((dataset, index) => {
            return {
                label: dataset.label,
                data: dataset.data,
                backgroundColor: colors[index]
            };
        });
        
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: chartDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Initialize charts if elements exist
    // Student Dashboard - Quiz Performance Chart
    const performanceChart = document.getElementById('quizPerformanceChart');
    if (performanceChart) {
        // Data should already be initialized in the PHP file
        // Chart.js will be initialized with the data provided in the page
    }
    
    // Admin Dashboard - User Distribution Chart
    const userDistributionChart = document.getElementById('userDistributionChart');
    if (userDistributionChart) {
        // Data should already be initialized in the PHP file
    }
    
    // Student Stats - Performance by Course
    const coursePerformanceData = document.querySelectorAll('.course-stat-item');
    if (coursePerformanceData.length > 0) {
        const courseCanvas = document.getElementById('coursePerformanceChart');
        if (courseCanvas) {
            const labels = [];
            const successRates = [];
            const completionRates = [];
            
            coursePerformanceData.forEach(course => {
                const courseName = course.querySelector('h3').textContent;
                const progressBars = course.querySelectorAll('.progress-bar');
                
                labels.push(courseName);
                
                if (progressBars.length >= 2) {
                    const completionWidth = progressBars[0].style.width;
                    const successWidth = progressBars[1].style.width;
                    
                    completionRates.push(parseFloat(completionWidth));
                    successRates.push(parseFloat(successWidth));
                }
            });
            
            if (labels.length > 0) {
                createStackedBarChart(courseCanvas, labels, [
                    { label: 'Tamamlama Oranı', data: completionRates },
                    { label: 'Başarı Oranı', data: successRates }
                ]);
            }
        }
    }
    
    // Create animated progress bars
    function animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        
        progressBars.forEach(bar => {
            const targetWidth = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.width = targetWidth;
            }, 100);
        });
    }
    
    // Call the function when DOM is loaded
    animateProgressBars();
    
    // Student Stats - Overall Success Rate Comparison
    const studentSuccessRates = document.querySelectorAll('.student-success-rate');
    if (studentSuccessRates.length > 0) {
        const comparisonCanvas = document.getElementById('successRateComparisonChart');
        if (comparisonCanvas) {
            const names = [];
            const rates = [];
            
            studentSuccessRates.forEach(student => {
                const nameElement = student.querySelector('.student-name');
                const rateElement = student.querySelector('.success-rate');
                
                if (nameElement && rateElement) {
                    names.push(nameElement.textContent);
                    rates.push(parseFloat(rateElement.textContent));
                }
            });
            
            if (names.length > 0) {
                createBarChart(comparisonCanvas, names, rates, 'Başarı Oranı (%)', '#28a745');
            }
        }
    }
    
    // Teacher Dashboard - Course Completion Rates
    const teacherCoursesData = document.querySelectorAll('.teacher-course-stats');
    if (teacherCoursesData.length > 0) {
        const teacherCanvas = document.getElementById('courseCompletionChart');
        if (teacherCanvas) {
            const courseNames = [];
            const completionRates = [];
            
            teacherCoursesData.forEach(course => {
                const courseName = course.querySelector('.course-name').textContent;
                const rateElement = course.querySelector('.completion-rate');
                
                if (rateElement) {
                    courseNames.push(courseName);
                    completionRates.push(parseFloat(rateElement.textContent));
                }
            });
            
            if (courseNames.length > 0) {
                createBarChart(teacherCanvas, courseNames, completionRates, 'Tamamlanma Oranı (%)', '#3498db');
            }
        }
    }
    
    // Admin Stats - Course Success Distribution
    const courseSuccessChart = document.getElementById('courseSuccessChart');
    if (courseSuccessChart) {
        // Data should already be initialized in the PHP file
    }
});
