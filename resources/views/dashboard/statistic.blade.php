
<div class="chart-area">
	<div style="height: 100%;">
		<div class="chartjs-size-monitor">
			<div class="chartjs-size-monitor-expand">
				<div></div>
			</div>
			<div class="chartjs-size-monitor-shrink">
				<div></div>
			</div>
		</div>
		<canvas id="{{ $id }}" width="2992" height="1000" class="chartjs-render-monitor" style="display: block; height: 400px; width: 1197px;"></canvas>
	</div>
</div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
<script type="text/javascript">
	var ctx = document.getElementById('{{ $id }}').getContext('2d');
	new Chart(ctx, {
		type: 'line',
		data: {
			datasets: [
				@foreach($stats as $mail => $stat)
				{
					fill: false,
					borderColor: '{{ \Santwer\DeveloperAnalyst\Dashboard\Http\Helpers\ViewHelper::generateVisibleColor($mail) }}',
					borderWidth: 2,
					borderDash: [],
					borderDashOffset: 0.0,
					pointBackgroundColor: '{{ \Santwer\DeveloperAnalyst\Dashboard\Http\Helpers\ViewHelper::generateVisibleColor($mail) }}',
					pointBorderColor: 'rgba(255,255,255,0)',
					pointHoverBackgroundColor: '{{ \Santwer\DeveloperAnalyst\Dashboard\Http\Helpers\ViewHelper::generateVisibleColor($mail) }}',
					pointBorderWidth: 20,
					pointHoverRadius: 4,
					pointHoverBorderWidth: 15,
					pointRadius: 4,
					data: {!!  json_encode($stat) !!},
					label: '{{ $mail }}'
				},
				@endforeach
			],
			labels: {!! json_encode($labels) !!}
		},
		options: {
			maintainAspectRatio: false,
			legend: {
				display: true,
				position: 'bottom',
			},
			responsive: true,
			tooltips: {
				backgroundColor: '#f5f5f5',
				titleFontColor: '#333',
				bodyFontColor: '#666',
				bodySpacing: 4,
				xPadding: 12,
				mode: 'nearest',
				intersect: 0,
				position: 'nearest'
			},
			scales: {
				yAxes: [{
					barPercentage: 1.6,
					gridLines: {
						drawBorder: false,
						color: 'rgba(29,140,248,0.0)',
						zeroLineColor: 'transparent'
					},
					ticks: {
						padding: 20,
						fontColor: '#9a9a9a'
					}
				}],
				xAxes: [{
					barPercentage: 1.6,
					gridLines: {
						drawBorder: false,
						color: 'rgba(225,78,202,0.1)',
						zeroLineColor: 'transparent'
					},
					ticks: {
						padding: 20,
						fontColor: '#9a9a9a'
					}
				}],
			}
		}
	});
</script>