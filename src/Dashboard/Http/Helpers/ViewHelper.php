<?php

namespace Santwer\DeveloperAnalyst\Dashboard\Http\Helpers;

class ViewHelper
{
	public static function generateVisibleColor($string) {
		// Wandelt den String in einen Hashwert um
		$hash = md5($string);

		// Extrahiert die RGB-Werte aus dem Hash
		$red = hexdec(substr($hash, 0, 2));
		$green = hexdec(substr($hash, 2, 2));
		$blue = hexdec(substr($hash, 4, 2));

		// Berechnet die Helligkeit der Farbe
		$brightness = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

		// Wählt die Farbe basierend auf der Helligkeit
		if ($brightness > 128) {
			// Dunkle Schriftfarbe für helle Hintergrundfarben
			$textColor = '#000000'; // Schwarz
		} else {
			// Helle Schriftfarbe für dunkle Hintergrundfarben
			$textColor = '#FFFFFF'; // Weiß
		}

		// Generiert eine bunte Farbe
		$color = sprintf('#%02x%02x%02x', $red, $green, $blue);

		return $color;
	}
}