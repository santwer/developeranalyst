<?php

namespace Santwer\DeveloperAnalyst\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Santwer\DeveloperAnalyst\Statistics\GitStatistic;
use Santwer\DeveloperAnalyst\Statistics\GitProjectStatistic;
use Santwer\DeveloperAnalyst\Dashboard\Charts\GitCommitUserChart;

class DashboardController extends Controller
{
	public function index()
	{
		$performers = cache()->driver(config('developerAnalyst.cache.driver'))->get('stats', []);
		$totalCommits = collect($performers)->sum('total_commits');
		$avgMPC = round(collect($performers)->avg('mistakes_per_commit') * 100) / 100;

		return view('developerAnalyst::dashboard.index', [
			'performers'   => $performers,
			'totalCommits' => $totalCommits,
			'avgMPC'       => $avgMPC,
		]);
	}

	public function gitStatistics()
	{
		$perUser = collect(GitProjectStatistic::getCommitsPerUser());
		$stats = GitProjectStatistic::getTotalStatisticCommitsPerMonth();
		$tables = $this->getLabels($stats);

		$statistics = [];
		$statisticsLastTwoYears = [];
		$yesteryear = date('Y') - 1;
		$labelsyesteryear = [];

		$allTime = [];
		$authorSum = [];
		foreach ($tables as $label) {
			$year = substr($label, 0, 4);
			foreach ($stats as $mail => $userStats) {
				$author = $perUser->where('mail', $mail)->first();
				if($author) {
					$mail = $author['name'];
				}
				$commits = isset($userStats[$label]) ? $userStats[$label] : 0;
				if($year == $yesteryear) {
					$statisticsLastTwoYears[$mail][] = $commits;
				}
				$statistics[$mail][] = $commits;
				if(!isset($authorSum[$mail])) {
					$authorSum[$mail] = 0;
				}
				$authorSum[$mail] += $commits;
				$allTime[$mail][] = $authorSum[$mail];

			}
			if($year == $yesteryear) {
				$labelsyesteryear[] = $label;
			}
		}

		//filter empty values
		$statisticsLastTwoYears = collect($statisticsLastTwoYears)->filter(function($item) {
			return collect($item)->sum() > 0;
		})->toArray();


		return view('developerAnalyst::dashboard.git-statitics', [
			'chart' => [],
			'stats' => $statistics,
			'labels' => $tables,
			'allTime' => $allTime,
			'statisticsLastTwoYears' => $statisticsLastTwoYears,
			'labelsyesteryear' => $labelsyesteryear,
		]);


	}

	private function getLabels($stats)
	{
		$labels = [];
		foreach ($stats as $mail => $userStats) {
			foreach ($userStats as $label => $stat) {
				if (in_array($label, $labels)) {
					continue;
				}
				$labels[] = $label;
			}
		}


		return collect($labels)->sort()->values()->toArray();
	}

}
