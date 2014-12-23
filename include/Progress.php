<?php

/**
 * Progress Log Class
 */
class Progress {

	const SMOOTHING_FACTOR = .1;
	const BAR_WIDTH = 40;

	protected static $steps = array();

	/**
	 * Write a progress step to the log
	 */
	public static function writeLog($step, $n, $d, $mode = 'ascii') {

		if (!in_array($mode, array('ascii', 'html'))) {
			$mode = 'ascii';
		}

		$now = microtime(true);
		if (!isset(self::$steps[$step])) {
			self::$steps[$step] = array('lastN' => 0, 'lastTime' => $now - 1, 'total' => $d, 'startTime' => $now);
		}

		$method = "writeLog".$mode;
		self::$method($step, $n, $d);
	}

	/**
	 * Get average speed
	 *
	 * @param   int     $lastN      Number completed as of last update
	 * @param   int     $n          Number completed as of this update
	 * @param   float   $lastTime   Time of last update
	 * @param   float   $now        Time of this update
	 * @param   float   $start      Start time
	 *
	 * @return  float
	 */
	protected static function getAvgSpeed($lastN, $n, $lastTime, $now, $start) {

		$avgSpeed = $n / ($now - $start);
		$instSpeed = ($n - $lastN) / ($now - $lastTime);

		$avgSpeed = self::SMOOTHING_FACTOR * $instSpeed + (1 - self::SMOOTHING_FACTOR) * $avgSpeed;

		//prevent divide by zeros
		if (!$avgSpeed) {
			return .0000001;
		}

		return $avgSpeed;
	}

	/**
	 * Get formatted size in K, M
	 *
	 * @param   int   $size   Size
	 *
	 * @return  string
	 */
	protected static function prettySize($size) {
		$pow = ($size < pow(10, 3) ? 0 : ($size < pow(10, 6) ? 3 : 6));
		$size = round($size / pow(10, $pow), 1);
		$size .= ($pow == 0 ? '' : ($pow == 3 ? 'K' : 'M'));
		return $size;
	}

	/**
	 * Write a progress step to the log in ascii
	 */
	protected static function writeLogAscii($step, $n, $d) {

		$now = microtime(true);
		$out = 'Log updated at ' . date('H:i:s m/d/Y') . "\n";

		foreach (self::$steps as $v => &$sObj) {

			if ($v == $step) {

				$percent = $n / $d;
				$avgSpeed = self::getAvgSpeed($sObj['lastN'], $n, $sObj['lastTime'], $now, $sObj['startTime']);
				$timeLeft = ($d - $n) / $avgSpeed;

				$sObj['avgSpeed'] = $avgSpeed;
				$sObj['lastTime'] = $now;
				$sObj['lastN'] = $n;

			} else {
				$percent = 1;
				$timeLeft = ($sObj['lastTime'] - $sObj['startTime']);
				$avgSpeed = $sObj['avgSpeed'];
			}

			$write = round($percent * self::BAR_WIDTH);
			$percent = round($percent * 100);

			$perc = $percent . "%";

			$bar = str_repeat('=', $write) . str_repeat(' ', self::BAR_WIDTH - $write);
			$bar = substr_replace($bar, $perc, floor((self::BAR_WIDTH - strlen($perc)) / 2), strlen($perc));

			$out .= "$v \n[" . $bar . "]";

			$out .= " " . self::prettySize($sObj['lastN']) . " / " . self::prettySize($sObj['total']);
			$out .= " " . self::prettySize(round($avgSpeed)) . "/s";
			$out .= sprintf(" %s:%02s",  floor($timeLeft / 60), $timeLeft % 60);

			$out .= "\n";
		}

		file_put_contents(dirname(__FILE__) . '/../data/log.txt', $out);
	}


	/**
	 * Write a progress step to the log in formatted html
	 */
	protected static function writeLogHtml($step, $n, $d) {

		$now = microtime(true);
		$out = '<h4>Log updated at ' . date('H:i:s m/d/Y') . "</h4><hr />";

		foreach (self::$steps as $v => &$sObj) {

			if ($v == $step) {

				$percent = $n / $d;
				$avgSpeed = self::getAvgSpeed($sObj['lastN'], $n, $sObj['lastTime'], $now, $sObj['startTime']);
				$timeLeft = ($d - $n) / $avgSpeed;

				$sObj['avgSpeed'] = $avgSpeed;
				$sObj['lastTime'] = $now;
				$sObj['lastN'] = $n;

			} else {
				$percent = 1;
				$timeLeft = ($sObj['lastTime'] - $sObj['startTime']);
				$avgSpeed = $sObj['avgSpeed'];
			}

			$percent = $percent * 100;
			$perc = round($percent, 1) . "%";

			$out .= '<div style="margin-top: 20px;">';
			$out .= "<h4>$v</h4><br />\n";
			$out .= '<div class="progress ' . (($v == $step && $percent != 100) ? 'active' : '') . '"><div class="progress-bar" style="width: ' . $percent . '%;">' . $perc . '</div></div>';

			$out .= '<span class="label label-info" style="text-align:center; width:100px">' . self::prettySize($sObj['lastN']) . " / " . self::prettySize($sObj['total']) . '</span> ';
			$out .= '<span class="label label-info" style="text-align:center; width:100px">' . self::prettySize(round($sObj['avgSpeed'])) . "/s</span> ";
			$out .= '<span class="label label-info" style="text-align:center; width:100px">' . sprintf(" %s:%02s",  floor($timeLeft / 60), $timeLeft % 60) . '</span>';

			$out .= "</div>\n";
		}

		file_put_contents(dirname(__FILE__) . '/../var/log/log.txt', $out);
	}

	/**
	 * Get the progress log
	 */
	public function readLog() {

		$file = dirname(__FILE__) . '/../var/log/log.txt';
		$text = "";

		if (file_exists($file)) {

			$text = @file_get_contents($file);

			if (@filemtime($file) < time() - 10) {
				$text = '<div class="alert alert-warning" style="margin-top: 20px;">Stopped.</div>' . $text;
			}
		}

		if (!$text) {
			$text = '<div class="alert alert-warning" style="margin-top: 20px;">No data.</div>';
		}

		return $text;
	}
}

