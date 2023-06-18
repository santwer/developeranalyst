<!DOCTYPE html>
<html lang="en" class="has-aside-left has-aside-mobile-transition has-navbar-fixed-top has-aside-expanded">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ config('developerAnalyst.dashboard.title') }}</title>
	<!-- Bulma is included -->
	<link rel="stylesheet" href="{{ asset('vendor/developer-analyst/css/developer-analyst.css') }}">

	<!-- Fonts -->
	<link rel="dns-prefetch" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
</head>
<body>
<div id="app">

	@include('developerAnalyst::dashboard.aside')
	<section class="section is-title-bar">
		<div class="level">
			<div class="level-left">
				<div class="level-item">
					<ul>
						<li>{{ config('developerAnalyst.dashboard.title') }}</li>
						<li>Dashboard</li>
					</ul>
				</div>
			</div>
			<div class="level-right">
				<div class="level-item">
					<div class="buttons is-right">
						<a href="https://github.com/santwer/developeranalyst" target="_blank"
						   class="button is-primary">
							<span class="icon"><i class="mdi mdi-github-circle"></i></span>
							<span>GitHub</span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>
	<section class="hero is-hero-bar">
		<div class="hero-body">
			<div class="level">
				<div class="level-left">
					<div class="level-item"><h1 class="title">
							Dashboard
						</h1></div>
				</div>
				<div class="level-right" style="display: none;">
					<div class="level-item"></div>
				</div>
			</div>
		</div>
	</section>
	<section class="section is-main-section">
		<div class="tile is-ancestor">
			<div class="tile is-parent">
				<div class="card tile is-child">
					<div class="card-content">
						<div class="level is-mobile">
							<div class="level-item">
								<div class="is-widget-label"><h3 class="subtitle is-spaced">
										Developers
									</h3>
									<h1 class="title">
										0
									</h1>
								</div>
							</div>
							<div class="level-item has-widget-icon">
								<div class="is-widget-icon"><span class="icon has-text-primary is-large"><i
												class="mdi mdi-account-multiple mdi-48px"></i></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tile is-parent">
				<div class="card tile is-child">
					<div class="card-content">
						<div class="level is-mobile">
							<div class="level-item">
								<div class="is-widget-label"><h3 class="subtitle is-spaced">
										Commits
									</h3>
									<h1 class="title">
										0
									</h1>
								</div>
							</div>
							<div class="level-item has-widget-icon">
								<div class="is-widget-icon"><span class="icon has-text-info is-large"><i
												class="mdi mdi-source-commit mdi-48px"></i></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tile is-parent">
				<div class="card tile is-child">
					<div class="card-content">
						<div class="level is-mobile">
							<div class="level-item">
								<div class="is-widget-label"><h3 class="subtitle is-spaced">
										Performance
									</h3>
									<h1 class="title">
										0
									</h1>
								</div>
							</div>
							<div class="level-item has-widget-icon">
								<div class="is-widget-icon"><span class="icon has-text-success is-large"><i
												class="mdi mdi-poll mdi-48px"></i></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="card">
			<header class="card-header">
				<p class="card-header-title">
					<span class="icon"><i class="mdi mdi-finance"></i></span>
					Performance Last 2 Years
				</p>
			</header>
			<div class="card-content">
				@include('developerAnalyst::dashboard.statistic',
				['labels' =>  $labelsyesteryear, 'stats' => $statisticsLastTwoYears, 'id' => 'chart-yesteryear' ])
			</div>
		</div>
		<div class="card">
			<header class="card-header">
				<p class="card-header-title">
					<span class="icon"><i class="mdi mdi-finance"></i></span>
					Performance All Time
				</p>

			</header>
			<div class="card-content">
				@include('developerAnalyst::dashboard.statistic', ['id' => 'chart-alltime'])
			</div>
		</div>
		<div class="card">
			<header class="card-header">
				<p class="card-header-title">
					<span class="icon"><i class="mdi mdi-finance"></i></span>
					Sum Commits All Time
				</p>

			</header>
			<div class="card-content">
				@include('developerAnalyst::dashboard.statistic', ['id' => 'chart-alltime-commits', 'stats' => $allTime ])
			</div>
		</div>


		<div class="card has-table has-mobile-sort-spaced">
			<header class="card-header">
				<p class="card-header-title">
					<span class="icon"><i class="mdi mdi-account-multiple"></i></span>
					Developers
				</p>
				<a href="#" class="card-header-icon">
					<span class="icon"><i class="mdi mdi-reload"></i></span>
				</a>
			</header>

			<div class="card-content">
				<div class="b-table has-pagination">
					<div class="table-wrapper has-mobile-cards">
						<table class="table is-fullwidth is-striped is-hoverable is-sortable is-fullwidth">
							<thead>
							<tr>
								<th>#</th>
								<th></th>
								<th>Author</th>
								<th>Mail</th>
								<th>Translation Mistakes</th>
								<th>HTML Mistakes</th>
								<th>Files</th>
								<th>Total Commits</th>
								<th>Mistakes / Commits</th>
							</tr>
							</thead>
							<tbody>

							</tbody>
						</table>
					</div>

				</div>
			</div>
		</div>
	</section>

</div>


<!-- Scripts below are for demo only -->
<script type="text/javascript" src="{{ asset('vendor/developer-analyst/js/main.js') }}"></script>



<!-- Icons below are for demo only. Feel free to use any icon pack. Docs: https://bulma.io/documentation/elements/icon/ -->
<link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
</body>
</html>
