<?php

namespace Santwer\DeveloperAnalyst\Statistics;

use Gitonomy\Git\Diff\File;
use Gitonomy\Git\Repository;
use mysql_xdevapi\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Console\View\Components\Task;
use Symfony\Component\Console\Helper\ProgressBar;
use Illuminate\Console\View\Components\BulletList;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Santwer\DeveloperAnalyst\Dashboard\Http\Models\Developer;

class GitStatistic
{

	protected $respsitory;

	protected $branch;

	protected OutputInterface $output;

	protected $allFiles = [];

	protected $authors = [];
	protected $authorsCommits = [];

	protected $filesPerUser = [];

	protected $stats;


	public static function calc(?OutputInterface $output = null)
	{
		foreach (config('developerAnalyst.repository') as $respsitory => $branch) {
			$gitStatistic = new self($respsitory, $branch, $output);
			$gitStatistic->init();

		}

	}

	public function __construct(string $respsitory, string $branch, ?OutputInterface $output = null)
	{
		$this->respsitory = new Repository($respsitory);
		$this->branch = $branch;
		if ($output) {
			$this->output = $output;
		}
	}

	protected function write($component, ...$arguments)
	{
		if ($this->output && class_exists($component)) {
			(new $component($this->output))->render(...$arguments);
		} else {
			foreach ($arguments as $argument) {
				if (is_callable($argument)) {
					$argument();
				}
			}
		}
	}

	public function init()
	{
		$this->write(Task::class, 'get commits per user per day', function () {
			$this->stats = $this->getCommitsPerUserPerDay();

			return true;
		});

		$translationChecker = new TranslationChecker($this->allFiles);

		$this->write(Task::class, 'Check missing translations',
			fn () => $translationChecker->checkMissingTranslations());

		try {
			$process = new ProcessStatistics();
			$this->write(Task::class, 'Save Developers',
				fn () => $process->saveDevs($this->authors));
			$this->write(Task::class, 'Save Statistics',
				fn () => $process->saveStats($this->stats));
			$this->write(Task::class, 'Save Translation Mistakes',
				fn () => $process->saveFiles($this->allFiles,
					$translationChecker->getFiles(),
					$translationChecker->getFilesHTML()));
			$this->write(Task::class, 'Save Dev Mistakes',
				fn () => $process->saveUserFiles($this->filesPerUser));
		} catch (\Exception $exception) {

		}
		$this->createOutPut($translationChecker);
	}

	private function createOutPut(TranslationChecker $translationChecker)
	{
		$data = [];
		foreach ($this->authors as $mail => $author) {
			$data[] = [
				'author'               => $author,
				'mail'                 => $mail,
				'translation_mistakes' => $this->calcMistakes($mail,
					$translationChecker->getFiles()),
				'html_mistakes'        => $this->calcMistakes($mail,
					$translationChecker->getFilesHTML()),
				'total_commits'        => isset($this->authorsCommits[$author]) ? $this->authorsCommits[$author] : 0,
			];
		}
		$data = collect($data)->where('total_commits', '>', 0)->toArray();
		$this->output->table(['author', 'mail', 'translation_mistakes', 'html_mistakes', 'total_commits'], $data);
	}

	private function calcMistakes(string $mail, array $missinTranslations)
	{
		if (!isset($this->filesPerUser[$mail])) {
			return 0;
		}
		$sum = 0;
		foreach ($this->filesPerUser[$mail] as $file) {
			if (isset($missinTranslations[$file])) {
				$sum += $sum;
			}
		}

		return $sum;
	}


	public function getCommitsPerUserPerDay()
	{
		$commits = $this->respsitory->getLog($this->branch);

		$statistics = [];
		$authors = [];
		$logstart = Carbon::parse(config('developerAnalyst.log_start'));

		foreach ($commits->getCommits() as $commit) {
			$author = $commit->getAuthorEmail();
			$authors[$author] = $commit->getAuthorName();

			$date = Carbon::instance($commit->getAuthorDate());
			if ($date->gte($logstart)) {
				$this->addFiles($commit->getDiff()->getFiles(), $commit->getAuthorEmail());

				if(!isset($this->authorsCommits[$author])) {
					$this->authorsCommits[$author] = 0;
				}
				$this->authorsCommits[$author]++;
			}

			$formattedDate = $date->format('Y-m-d');
			if (!isset($statistics[$author])) {
				$statistics[$author] = [];
			}

			if (!isset($statistics[$author][$formattedDate])) {
				$statistics[$author][$formattedDate] = 0;
			}

			$statistics[$author][$formattedDate]++;

		}
		$this->authors = $authors;

		return $statistics;
	}

	private function addFiles(array $files, string $author)
	{
		/**
		 * @var File $file
		 */
		$files = array_map(fn ($file) => $file->getNewName(), $files);
		$this->allFiles = array_merge($this->allFiles, $files);

		if (empty($author)) {
			return;
		}
		if (!isset($this->filesPerUser[$author])) {
			$this->filesPerUser[$author] = [];
		}
		$this->filesPerUser[$author] = array_merge($this->allFiles, $files);
	}
}