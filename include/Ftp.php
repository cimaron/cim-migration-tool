<?php

/**
 * Ftp Class
 */
class Ftp {

	/**
	 * @var   resource
	 */
	protected $res = NULL;

	/**
	 * @var   array
	 */
	public $errors = array();

	/**
	 * Constructor
	 *
	 * @param   string   $user       Username
	 * @param   string   $password   Password
	 * @param   string   $database   Root path
	 * @param   string   $host       Host
	 */
	public function __construct($user, $password, $host = 'localhost') {

		$this->root = $root;

		$this->res = @ftp_connect($host);

		if (!$this->res) {
			$this->setError();
			return;
		}

		$result = @ftp_login($this->res, $user, $password);
		if (!$result) {
			$this->setError();
			return;
		}

	}

	/**
	 * Set last error
	 */
	protected function setError() {

		$error = error_get_last();
		if ($error) {
			$this->errors[] = $error['message'];
		}
	}

	/**
	 * List contents of directory
	 */
	public function ls($path = '.', $match = '.*') {

		$contents = @ftp_nlist($this->res, $path);

		if ($path[strlen($path) - 1] != '/') {
			$path .= '/';
		}

		if (!$contents) {
			$this->setError();
			return false;
		}

		$results = array();

		if ($path != '.') {
			foreach ($contents as $file) {
				if (substr($file, 0, strlen($path)) == $path) {
					$file = substr($file, strlen($path));
					if (preg_match('/' . str_replace('/', '\/', $match) . '/', $file)) {
						$results[] = $file;
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Get mtime for file
	 *
	 * @param   string   $path   Remote path
	 *
	 * @return  int
	 */
	public function mtime($path) {

		$time = @ftp_mdtm($this->res, $path);
		
		if ($time == -1) {
			$this->setError();
			return false;
		}
	
		return $time;
	}

	/**
	 * Get filesize for file
	 *
	 * @param   string   $path   Remote path
	 *
	 * @return  int
	 */
	public function filesize($path) {

		$size = @ftp_size($this->res, $path);
		
		if ($size == -1) {
			$this->setError();
			return false;
		}
	
		return $size;
	}

	/**
	 * Get a file
	 *
	 * @param   string   $remote   Remote path
	 * @param   string   $local    Local path
	 * @param   bool     $block    Block or nonblock
	 *
	 * @return  mixed
	 */
	public function get($remote, $local, $block = true) {
		static $result;

		if ($block) {

			$result = @ftp_get($this->res, $local, $remote, FTP_BINARY);

			if (!$result) {
				$this->setError();
				return false;
			}

			return filesize($local);
		}

		if ($result === NULL) {
			$result = @ftp_nb_get($this->res, $local, $remote, FTP_BINARY);
		} elseif ($result === FTP_MOREDATA) {
			$result = @ftp_nb_continue($this->res);
		} else {
			$result = NULL;
		}

		if ($result === FTP_MOREDATA) {
			return true;
		}

		if ($result === FTP_FINISHED) {
			$result = NULL;
			return filesize($local);
		}

		$this->setError();
		$result = NULL;
		return false;
	}

}


