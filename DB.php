<?php

namespace SecureSessionNS;

require_once('Logger.php');

// create table if not exists
const CREATE_SQL = <<<EOF
CREATE TABLE IF NOT EXISTS sessions(
	id INTEGER PRIMARY KEY,
	sess_id TEXT NOT_NULL UNIQUE,
	val TEXT,
	createDate DATETIME DEFAULT CURRENT_TIMESTAMP
);
EOF;

const INSERT_UPDATE_SQL = <<<EOF
INSERT OR REPLACE INTO sessions
        (id, sess_id, val)
        VALUES (
        (SELECT id from sessions WHERE sess_id=:session_id), 
        :session_id, 
        :session_value);
EOF;


class DB {
	private const dbPATH = '/tmp/sessions.sqlite3'; # change here with your sqlite db
	private $db;

	public function __construct() {
		$this->db = new \PDO('sqlite:' . self::dbPATH);
		$this->createFirstTime();
	}

	private function createFirstTime() {
		try {
			$this->db->exec(CREATE_SQL);
		} catch (PDOException $pe) {
			Logger::error($pe->getMessage());
		}
	}

	public function deleteExpired($expireHours) {
		$expireHours = '-' . $expireHours . ' hours';
		$deleteExpireds = "DELETE FROM sessions WHERE createDate <= datetime('now','$expireHours')";
		try {
			$this->db->exec($deleteExpireds);
			return true;
		} catch (PDOException $e) {
			Logger::warning($e->getMessage());
			return false;

		}
	}

	public function destroySession($sessionID) {
		try {
			$stmt = $this->db->prepare("delete from sessions where sess_id=:session_id");
			return $stmt->execute([':session_id' => $sessionID]);

		} catch (PDOException $e) {
			Logger::warning($e->getMessage());
			return false;
		}

	}

	/**
	 * @param $sessionID
	 * @param $data -->  crypted data
	 * @return bool
	 */
	public function write($sessionID, $data) {
		try {
			$stmt = $this->db->prepare(INSERT_UPDATE_SQL);
			return $stmt->execute([':session_id' => $sessionID, ':session_value' => $data]);
		} catch
		(PDOException $e) {
			Logger::warning($e->getMessage());
			return false;
		}
	}

	/**
	 * @param $sessionID
	 * @return string --> crypted data
	 */
	public function read($sessionID) {
		$result = $this->db
			->query("select val from sessions where sess_id='{$sessionID}'")
			->fetch();
		if ($result) {
			return $result['val'];
		}

		return '';
	}


}