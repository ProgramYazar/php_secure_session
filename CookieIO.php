<?php

namespace SecureSessionNS;

class Cookie {
	// Cookie settings
	const DOMAINNAME = '.deneme.com';
	const SITEPATH = '/';
	const IS_SECURE = false;
	const HTTPONLY = false;
	const EXPIREHOUR = 5;
	// Cookie props
	private $expireTime;
	private $firstInit;
	private $cookie_name;

	private $crpyter;
	private $data;


	public function __construct($cookieName) {
		$this->crpyter = new Crypt();
		$this->expireTime = time() + (60 * 60 * self::EXPIREHOUR); // after 5 hours
		$this->cookie_name = $cookieName;
		$this->data = array();
		if (!self::has($cookieName)) {
			$this->firstInit = true;
			$this->__create();
		}
		else {
			$this->firstInit = false;
			$this->data = $this->load();

		}
	}

	public function serialize() {
		return json_encode($this->data);
	}

	public static function has($cookieName): bool {
		return isset($_COOKIE[$cookieName]);
	}

	public function write($key, $value) {
		$this->data[$key] = $value;
	}

	public function read($key) {
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	public function isFirstTime(): bool {
		return $this->firstInit;
	}

	private function __create() {
		setcookie(
			$this->cookie_name,
			$this->crpyter->encrypt($this->data),
			$this->expireTime,
			self::SITEPATH,
			self::DOMAINNAME,
			self::IS_SECURE,
			self::HTTPONLY
		);
	}

	public function delete() {
		setcookie(
			$this->cookie_name,
			'',
			time() - 3600,
			self::SITEPATH,
			self::DOMAINNAME,
			self::IS_SECURE,
			self::HTTPONLY
		);
	}


	public function save() {
		$this->__create();

	}

	private function load() {
		return $this->crpyter->decrypt($_COOKIE[$this->cookie_name]);
	}


}
