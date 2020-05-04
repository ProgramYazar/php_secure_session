<?php

namespace SecureSessionNS;
require_once('Compressor.php');


class Crypt {

	// security setting constants
	const ENCRYPT_METHOD = "AES-256-CBC";
	const SHA256KEYSIZE = 32;
	const IVSIZE = 16;

	private $secret_key = '048c%b61c7!!06949e*a59?dd98e4b9f2628b684';
	private $secret_iv = '7706f95^7959a4)b28(a0fe[e9764229df]3*679';
	private $encKey;
	private $iv;


	public function __construct() {
		$this->encKey = hash('sha256', $this->secret_key);
		$this->iv = substr($this->secret_iv, 0, 16);
	}

	public function encrypt($data) {
		$crypted = openssl_encrypt(
			Compressor::compress(json_encode($data)), // data -> json(encode) -> compress
			self::ENCRYPT_METHOD,
			$this->encKey,
			0,
			$this->iv
		);
		return base64_encode($crypted); // data -> json(encode) -> compress -> b64

	}

	public function decrypt($data) {
		$decrypted = openssl_decrypt(
			base64_decode($data), // data -> b64
			self::ENCRYPT_METHOD,
			$this->encKey,
			0,
			$this->iv
		);
		$decompressed = Compressor::decompress($decrypted); // data -> b64 -> decompress
		if(!$decompressed) {
			return array();
		}
		return json_decode($decompressed, true); // data -> b64 -> decompress  -> json(decode)

	}

	public static function createUniqString($length): string {
		$token = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
		$codeAlphabet .= "!*?.;:+^[]";
		$codeAlphabet .= "0123456789";
		$max = strlen($codeAlphabet);
		for ($i = 0; $i < $length; $i++) {
			$token .= $codeAlphabet[random_int(0, $max - 1)];
		}

		return base64_encode($token);
	}

}
