<?php

namespace Santwer\DeveloperAnalyst\Statistics;

use Illuminate\Support\Carbon;
use Santwer\DeveloperAnalyst\Dashboard\Http\Models\Files;
use Santwer\DeveloperAnalyst\Dashboard\Http\Models\Developer;
use Santwer\DeveloperAnalyst\Dashboard\Http\Models\GitStatistics;

class ProcessStatistics
{
	protected $developers;
	protected $files;

	public function __construct()
	{
		$this->developers = Developer::all();
	}

	public function saveDevs(array $devs): bool
	{
		$newdevs = collect($devs)->mapWithKeys(function ($name, $mail) {
			return [
				$mail => [
					'mail' => $mail,
					'name' => $name,
				],
			];
		})->whereNotIn('mail', $this->developers->pluck('mail'));
		$return = true;
		if ($newdevs->count() > 0) {
			$return = Developer::insert($newdevs->toArray());
			$this->developers = Developer::all();
		}

		return $return;
	}

	public function saveStats(array $stats): bool
	{
		$return = true;
		$database = GitStatistics::orderBy('date', 'DESC')->first();
		$stats = collect($stats)->mapWithKeys(function ($items, $mail) use ($database) {
			if ($dev = $this->developers->where('mail', $mail)->first()) {
				return [$mail => $this->getNewStatEntries($database?->date, $items, $dev->id)];
			}

			return [null => null];
		})->filter()->flatten(1);
		if ($stats->count() > 0) {
			$return = GitStatistics::insert($stats->toArray());
		}

		return $return;
	}

	public function saveFiles(array $allFiles, array $filesTransMistakes = [], array $filesHtmlMistakes = [])
	{
		Files::where('batch_date', today())->delete();
		$return = true;
		$insert = collect($allFiles)->map(function ($filepath) use($filesTransMistakes, $filesHtmlMistakes) {
			return [
				'filepath' => $filepath,
				'translation_mistakes' => isset($filesTransMistakes[$filepath]) ? (int)$filesTransMistakes[$filepath] : 0,
				'html_mistakes' => isset($filesHtmlMistakes[$filepath]) ? (int)$filesHtmlMistakes[$filepath] : 0,
				'batch_date' => today()->format('Y-m-d'),
				'created_at' => today()->format('Y-m-d H:i:s'),
				'updated_at' => today()->format('Y-m-d H:i:s'),
			];
		});
		dd($allFiles);
		if($insert->count() > 0)
			$return =Files::insert($insert);
		$this->files = Files::all();
		return $return;
	}

	public function saveUserFiles(array $filesPerUser = [])
	{
		collect($filesPerUser)->mapWithKeys(function ($mail, $files) {
			if (($dev = $this->developers->where('mail', $mail)->first()) ) {
				$files = collect($files)->map(function ($filepath) use($dev) {
					$file = $this->files->where('filepath', $filepath)->first();
					if(null === $file) return null;
					return $file->id;
				})->filter();
				$dev->files()->attach($files);
				return [$mail => $files->toArray()];
			}

			return [null => null];

		})->filter()->toArray();
		return true;
	}

	private function getNewStatEntries(?Carbon $date, array $dateValues, int $user_id): array
	{
		$return = [];
		foreach ($dateValues as $commitDate => $value) {
			if ($date === null || Carbon::parse($commitDate)->gt($date)) {
				$return[] = [
					'user_id' => $user_id,
					'date'    => $commitDate,
					'commits' => $value,
				];
			}
		}

		return $return;
	}
}