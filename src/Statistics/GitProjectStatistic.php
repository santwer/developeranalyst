<?php

namespace Santwer\DeveloperAnalyst\Statistics;

use Gitonomy\Git\Repository;

class GitProjectStatistic
{
	public static function getCommitsPerUser()
	{
		$authorStats = [];
		foreach (config('developerAnalyst.repository') as $respsitory => $branch) {
			// Git-Befehl ausführen und den Output abrufen
			$commitCountByAuthor = shell_exec('git shortlog -sne --all');

			// Zeilenumbruch als Trennzeichen verwenden, um die einzelnen Zeilen zu trennen
			$lines = explode("\n", $commitCountByAuthor);

			// Jede Zeile verarbeiten und die Daten in das Array einfügen
			foreach ($lines as $line) {
				// Name und E-Mail-Adresse extrahieren

				preg_match('/^\s*(\d+)\s+(.*?)\s+<([^>]+)>$/', $line, $matches);

				if (count($matches) === 4) {
					$commits = (int) $matches[1];
					$name = $matches[2];
					$email = $matches[3];
					$person = array_filter($authorStats, function ($author) use ($email) {
						return $author['mail'] === $email;
					});
					if($person) {
						//get key for person
						$key = array_search($person, $authorStats);
						$authorStats[$key]['commits'] = $commits + $authorStats[$key]['commits'];
					} else {
						$authorStats[] = [
							'name'    => $name,
							'mail'    => $email,
							'commits' => $commits,
						];
					}
				}
			}

		};
		return $authorStats;
	}

	public static function getTotalStatisticCommitsPerDay()
	{
		// Git-Befehl ausführen und den Output abrufen
		$logOutput = shell_exec('git log --format="%h|%an|%ae|%ad"');

// Array zur Speicherung der Commits pro Autor pro Tag
		$commitsByAuthorAndDate = [];

// Zeilenumbruch als Trennzeichen verwenden, um die einzelnen Commits zu trennen
		$commits = explode("\n", $logOutput);

// Jeden Commit verarbeiten
		foreach ($commits as $commit) {
			if (!empty($commit)) {
				// Commit-Informationen aufteilen
				[$commitHash, $authorName, $authorEmail, $commitDate] = explode('|', $commit);

				// Autor als Einheit behandeln (basierend auf der E-Mail-Adresse)
				$authorIdentifier = $authorEmail;

				// Datum ohne Zeitzone extrahieren (z.B. '2023-06-18')
				$commitDate = date('Y-m-d', strtotime($commitDate));

				// Anzahl der Commits pro Autor pro Tag erhöhen
				if (!isset($commitsByAuthorAndDate[$authorIdentifier][$commitDate])) {
					$commitsByAuthorAndDate[$authorIdentifier][$commitDate] = 1;
				} else {
					$commitsByAuthorAndDate[$authorIdentifier][$commitDate]++;
				}
			}
		}
		return $commitsByAuthorAndDate;

	}

	public static function getTotalStatisticCommitsPerMonth()
	{
		// Git-Befehl ausführen und den Output abrufen
		$logOutput = shell_exec('git log --format="%h|%an|%ae|%ad"');

// Array zur Speicherung der Commits pro Autor pro Tag
		$commitsByAuthorAndDate = [];

// Zeilenumbruch als Trennzeichen verwenden, um die einzelnen Commits zu trennen
		$commits = explode("\n", $logOutput);

// Jeden Commit verarbeiten
		foreach ($commits as $commit) {
			if (!empty($commit)) {
				// Commit-Informationen aufteilen
				[$commitHash, $authorName, $authorEmail, $commitDate] = explode('|', $commit);

				// Autor als Einheit behandeln (basierend auf der E-Mail-Adresse)
				$authorIdentifier = $authorEmail;

				// Datum ohne Zeitzone extrahieren (z.B. '2023-06-18')
				$commitDate = date('Y-m', strtotime($commitDate));

				// Anzahl der Commits pro Autor pro Tag erhöhen
				if (!isset($commitsByAuthorAndDate[$authorIdentifier][$commitDate])) {
					$commitsByAuthorAndDate[$authorIdentifier][$commitDate] = 1;
				} else {
					$commitsByAuthorAndDate[$authorIdentifier][$commitDate]++;
				}
			}
		}
		return $commitsByAuthorAndDate;

	}

	public static function getAmountCodeRows()
	{
		// Git-Befehl ausführen und den Output abrufen
		$files = shell_exec('git ls-files');

		// Array zur Speicherung der Codezeilen pro Entwickler
		$linesByAuthor = [];

		// Jede Datei verarbeiten
		$files = explode("\n", $files);
		foreach ($files as $file) {
			if (!empty($file)) {
				// Git blame Befehl ausführen, um die Codezeilen und die Autoren zu erhalten
				$output = shell_exec("git blame --line-porcelain $file");

				// Autor und Anzahl der Codezeilen extrahieren
				preg_match_all('/^author (.*?)(?:$|\n)/m', $output, $authors);
				preg_match_all('/^\d+/', $output, $lineNumbers);

				// Anzahl der Codezeilen pro Autor erhöhen
				foreach ($authors[1] as $key => $author) {
					$lines = count($lineNumbers[0]);

					if (!isset($linesByAuthor[$author])) {
						$linesByAuthor[$author] = $lines;
					} else {
						$linesByAuthor[$author] += $lines;
					}
				}
			}
		}


		// Das formatierte Array mit den Codezeilen pro Entwickler anzeigen
		dd($linesByAuthor);
	}
}