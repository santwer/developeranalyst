<?php

namespace Santwer\DeveloperAnalyst\Statistics;

use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class TranslationChecker
{

	protected $languageTexts = [];

	protected $files = [];

	protected $fileMissingTrans = [];

	protected $fileFoundHTMLInNonHTML = [];

	public function __construct(array $files)
	{
		$this->files = $this->subtractBase($files);
	}

	private function subtractBase(array|string $filePath) : array|string
	{
		if(is_array($filePath)) {
			return array_map(fn($x) => $this->subtractBase($x), $filePath);
		}
		$filePath = Str::replace('\\','/', $filePath);
		$base_path = Str::replace('\\','/', base_path());
		$filePath =  Str::replace($base_path,'', $filePath);
		if(Str::startsWith($filePath, '/')) {
			return ltrim($filePath, '/');
		}
		return $filePath;
	}

	public function checkMissingTranslations() : bool
	{
		$this->loadLanguageTexts();
		$this->searchMissingTranslations();
		$this->searchMissingBladeTranslations();
		return true;
	}

	public function getFiles() : array
	{
		return $this->fileMissingTrans;
	}

	public function getFilesHTML() : array
	{
		return $this->fileFoundHTMLInNonHTML;
	}

	protected function loadLanguageTexts()
	{
		$path = 'german/words.json';
		if (!\Storage::exists($path)) {
			$content = file_get_contents('https://raw.githubusercontent.com/creativecouple/all-the-german-words/master/woerter.json');
			\Storage::put($path, $content);
		} else {
			$content = \Storage::get($path);
			$this->languageTexts = json_decode($content);
		}
	}

	protected function searchMissingTranslations()
	{
		foreach (config('developerAnalyst.code_folders') as $folder) {
			$directory = new RecursiveDirectoryIterator($folder);
			$iterator = new RecursiveIteratorIterator($directory);
			$files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

			foreach ($files as $file) {
				$filePath = $this->subtractBase($file[0]);

				if (!empty($this->files) && !in_array($filePath, $this->files)) {
					continue;
				}

				$content = file_get_contents($filePath);

				preg_match_all('/(?<!__\()(?<!lang\()(["\'])(?:(?=(\\\\?))\2.)*?\1(?!__\()/', $content, $matches);

				foreach ($matches[0] as $i => $text) {
					$char = $this->getLastChar($content, $text);
					if (empty(trim($text)) || $char == '(' || $char == ',') {
						continue;
					}
					$text = trim($text, "'\"");
					$this->checkForHTML($text, $filePath);
					if ($this->containsLanguageWord($text)) {
						if (!isset($this->fileMissingTrans[$filePath])) {
							$this->fileMissingTrans[$filePath] = 0;
						}
						$this->fileMissingTrans[$filePath]++;
					}
				}
			}
		}
	}

	protected function searchMissingBladeTranslations()
	{
		foreach (config('developerAnalyst.blade_folders') as $folder) {
			$directory = new RecursiveDirectoryIterator($folder);
			$iterator = new RecursiveIteratorIterator($directory);
			$files = new RegexIterator($iterator, '/^.+\.blade.php$/i', RecursiveRegexIterator::GET_MATCH);

			foreach ($files as $file) {
				$filePath = $this->subtractBase($file[0]);
				if (!empty($this->files) && !in_array($filePath, $this->files)) {
					continue;
				}
				$content = file_get_contents($filePath);

				preg_match_all('/<(?!\s*\/?\s*(?:\(|\{\{))[^>]*>(.*?)<\/[^>]+>/', $content, $matches);

				foreach ($matches[0] as $i => $text) {
					$exclude = ['{{', '}}', '@lang', '@for', '@foreach', '@while', '@if', '@end', '@else', '$', '->'];
					if (empty(trim($text)) || Str::contains($text, $exclude)) {
						continue;
					}
					$text = trim(strip_tags($text), "'\"");
					if ($this->containsLanguageWord($text)) {

						if (!isset($this->fileMissingTrans[$filePath])) {
							$this->fileMissingTrans[$filePath] = 0;
						}
						$this->fileMissingTrans[$filePath]++;
					}
				}
			}
		}
	}

	private function getLastChar(string $string, string $keyword): ?string
	{
		$position = strpos($string, $keyword);
		if ($position !== false && $position > 0) {
			return $this->getCharByIndex($string, $position - 1);
		} else {
			return null;
		}
	}

	private function getCharByIndex(string $string, int $index)
	{
		$char = trim($string[$index]);
		if ($index > 0 && empty($char)) {
			return $this->getCharByIndex($string, $index - 1);
		}

		return $char;
	}

	protected function containsLanguageWord(string $string): bool
	{
		$words = explode(' ', $string);
		if ($this->containsScript($string)) {
			return false;
		}
		foreach ($words as $word) {
			$word = $this->extractLettersFromWord($word);
			if (empty($word) || strlen($word) < 3) {
				continue;
			}
			if (in_array($word, $this->languageTexts)) {
				return true;
			}
		}

		return false;
	}

	private function containsScript(string $string): bool
	{
		$words = ['null', ' as ', 'SELECT', 'double', 'float', 'int', 'string', 'update', 'insert', 'delete'];

		return Str::contains($string, $words);
	}

	protected function extractLettersFromWord($text)
	{
		// Regulärer Ausdruck, um nur Buchstaben zu extrahieren
		$pattern = '/[^\p{L}]/u';

		// Entferne alle Nicht-Buchstaben aus dem Text
		$letters = preg_replace($pattern, '', $text);

		return $letters;
	}

	protected function checkForHTML(string $string, string $file)
	{
		if (preg_match('/<[^>]+>/', $string)) {
			if (!isset($this->fileFoundHTMLInNonHTML[$file])) {
				$this->fileFoundHTMLInNonHTML[$file] = 0;
			}
			$this->fileFoundHTMLInNonHTML[$file]++;
		}
	}
}