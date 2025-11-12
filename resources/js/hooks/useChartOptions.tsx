export const useChartOptions = (title?: string, type: 'doughnut' | 'horizontalBar' | 'verticalBar' | 'line') => {
    const rootStyles = getComputedStyle(document.documentElement);
    const gridColor = rootStyles.getPropertyValue('--sidebar-accent').trim();
    const textColor = rootStyles.getPropertyValue('--foreground').trim();
    const chart1 = rootStyles.getPropertyValue('--chart-1').trim();
    const chart2 = rootStyles.getPropertyValue('--chart-2').trim();
    const chart3 = rootStyles.getPropertyValue('--chart-3').trim();
    const chart4 = rootStyles.getPropertyValue('--chart-4').trim();
    const chart5 = rootStyles.getPropertyValue('--chart-5').trim();

    const indexAxis = type === 'verticalBar' || type === 'line' ? 'x' : 'y';

    const datasetStyle = {
        borderColor: 'oklch(42.935% 0.11812 258.322)',
        backgroundColor: [chart1, chart2, chart3, chart4, chart5],
        hoverOffset: 4,
        pointRadius: 6,
        pointBackgroundColor: '#3b82f6',
    };

    const baseOptions = {
        indexAxis: indexAxis as 'x' | 'y',
        responsive: true,
        plugins: {
            legend: {
                display: false,
                position: 'top' as const,
            },
            title: {
                display: true,
                text: title,
                color: textColor,
            },
        },
        scales: {
            x: {
                grid: {
                    color: gridColor, // Couleur des lignes verticales
                    lineWidth: 1, // Épaisseur
                    display: true, // Afficher ou non
                },
                ticks: {
                    color: textColor, // Couleur du texte des labels
                },
            },
            y: {
                // min: 0,
                grid: {
                    color: gridColor, // Couleur des lignes horizontales
                    lineWidth: 1,
                    display: true,
                },
                ticks: {
                    color: textColor,
                    stepSize: 1,
                },
            },
        },
    };

    return { datasetStyle, baseOptions };
};
