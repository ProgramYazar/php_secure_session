<?php

namespace SecureSessionNS;


class Compressor {
	public static function compress($str) {
		return gzdeflate(gzdeflate($str, 9), 9);
	}

	public static function decompress($str) {
		$data = @gzinflate($str);
		if ($data != false) {
			return gzinflate($data);
		}
		return false;
	}
}