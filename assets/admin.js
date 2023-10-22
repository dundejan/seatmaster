import './styles/admin.css';
import './bootstrap';

import zoomPlugin from 'chartjs-plugin-zoom';
import { Chart } from 'chart.js';
import annotationPlugin from 'chartjs-plugin-annotation';

Chart.register(annotationPlugin);

// register globally for all charts
document.addEventListener('chartjs:init', function (event) {
	const Chart = event.detail.Chart;
	Chart.register(zoomPlugin);
});