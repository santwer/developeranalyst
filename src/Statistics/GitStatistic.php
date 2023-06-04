<?php

namespace Santwer\DeveloperAnalyst\Statistics;

use Gitonomy\Git\Diff\File;
use Illuminate\Support\Str;
use Gitonomy\Git\Repository;
use mysql_xdevapi\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Console\View\Components\Task;
use Illuminate\Console\View\Components\Warn;
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

//		try {
//			$process = new ProcessStatistics();
//			$this->write(Task::class, 'Save Developers',
//				fn () => $process->saveDevs($this->authors));
//			$this->write(Task::class, 'Save Statistics',
//				fn () => $process->saveStats($this->stats));
//			$this->write(Task::class, 'Save Translation Mistakes',
//				fn () => $process->saveFiles($this->allFiles,
//					$translationChecker->getFiles(),
//					$translationChecker->getFilesHTML()));
//			$this->write(Task::class, 'Save Dev Mistakes',
//				fn () => $process->saveUserFiles($this->filesPerUser));
//		} catch (\Exception $exception) {
//			$this->write(Warn::class, 'Statistics not saved.');
//		}
		$this->createOutPut($translationChecker);
	}

	private function createOutPut(TranslationChecker $translationChecker)
	{
		$data = [];
		foreach ($this->authors as $mail => $author) {
			$entry = [
				'#' => '-',
				'author'               => $author,
				'mail'                 => $mail,
				'translation_mistakes' => $this->calcMistakes($mail,
					$translationChecker->getFiles()),
				'html_mistakes'        => $this->calcMistakes($mail,
					$translationChecker->getFilesHTML()),
				'files' => isset($this->filesPerUser[$mail]) ? count($this->filesPerUser[$mail]) : 0,
				'total_commits'        => isset($this->authorsCommits[$mail]) ? $this->authorsCommits[$mail] : 0,
			];
			$mistakes = $entry['translation_mistakes'] + $entry['html_mistakes'];
			$entry['mistakes_per_commit'] = $mistakes == 0 ? 0 :
				round((($mistakes) / $entry['total_commits']) *1000) / 1000;

			$data[] = $entry;
		}
		$data = collect($data)
			->where('total_commits', '>', 0)
			->sortBy('mistakes_per_commit')
			->values()
			->map(function ($x, $index) {
				$x['#'] = $index + 1;
				return $x;
			})
			->toArray();
		$this->output->table(['#', 'author', 'mail', 'translation_mistakes', 'html_mistakes', 'total_commits', 'files','mistakes per commit'], $data);

	}

	private function calcMistakes(string $mail, array $missinTranslations)
	{
		if (!isset($this->filesPerUser[$mail])) {
			return 0;
		}
		$sum = 0;
		foreach (collect($this->filesPerUser[$mail])->unique() as $file) {
			if (isset($missinTranslations[$file])) {
				$sum += $missinTranslations[$file];
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
			if(Str::startsWith($commit->getSubjectMessage(), 'Merge')) continue;
			//dd(,$commit->getDiff()->getFiles());
			$author = $commit->getAuthorEmail();
			$authors[$author] = $commit->getAuthorName();

			$date = Carbon::instance($commit->getAuthorDate());
			if ($date->gte($logstart)) {
				$files = $this->getFiles($commit->getHash());

				$this->addFiles($this->getFiles($commit->getHash()), $author);

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

	private function getFiles($commit)
	{
		$output = null;
		exec('git show --pretty="" --name-only '.$commit.'', $output);
		$max = 100;
		for($i = 0; $i < $max && $output === null; $i++) {
			sleep(1);
		}
		return $output;
	}

	private function getAuthor($commit)
	{
		$output = null;
		exec('git show --pretty="short" --name-only '.$commit.'', $output);
		$max = 100;
		for($i = 0; $i < $max && $output === null; $i++) {
			sleep(1);
		}
		foreach($output as $line) {
			if(strpos( $line, 'Author: ') !== false)
				return substr($line, 8);

		}
		return null;
	}

	private function addFiles(array $files, string $author)
	{
		/**
		 * @var File $file
		 */
		$files = array_map(fn ($file) => $file, $files);
		$this->allFiles = array_merge($this->allFiles, $files);

		if (empty($author)) {
			return;
		}
		if (!isset($this->filesPerUser[$author])) {
			$this->filesPerUser[$author] = [];
		}
		$this->filesPerUser[$author] = array_merge($this->filesPerUser[$author], $files);
	}
}