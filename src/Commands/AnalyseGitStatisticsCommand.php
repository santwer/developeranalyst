<?php

namespace Santwer\DeveloperAnalyst\Commands;

use Illuminate\Console\Command;
use Santwer\DeveloperAnalyst\Statistics\GitStatistic;

class AnalyseGitStatisticsCommand extends Command
{
	protected $signature = 'analyse:git-statistics';

	protected $description = 'Command description';

	public function handle(): void
	{

		GitStatistic::calc($this->output);
	}
}
