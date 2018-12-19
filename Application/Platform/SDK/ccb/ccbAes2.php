<?php
    // ECB加密不需要vi
    class CcbAes2 {

        private static $iv = "0102030405060708";

        public static function encrypt($input, $key) {
            /*$key = base64_decode($key);
            $localIV = Security::$iv;
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
            mcrypt_generic_init($module, $key, $localIV);
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $input = Security::pkcs5_pad($input, $size);
            $data = mcrypt_generic($module, $input);

            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
            $data = base64_encode($data);
            return $data;*/
			
            $encrypted = openssl_encrypt($input, 'AES-128-ECB', $key, true);
            $data = bin2hex($encrypted);
            return $data;
        }
		/*
        private static function pkcs5_pad ($text, $blocksize) {
            $pad = $blocksize - (strlen($text) % $blocksize);
            return $text . str_repeat(chr($pad), $pad);
        }
		*/
        public static function decrypt($sStr, $key) {
            /*$key = base64_decode($key);
            $localIV = Security::$iv;
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
            mcrypt_generic_init($module, $key, $localIV);
            $encryptedData = base64_decode($sStr);
            $encryptedData = mdecrypt_generic($module, $encryptedData);

            $dec_s = strlen($encryptedData);
            $padding = ord($encryptedData[$dec_s-1]);
            $decrypted = substr($encryptedData, 0, -$padding);

            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);

            return $decrypted;*/
			if (!extension_loaded("openssl"))
			{
				logx("openssl is false.");
			}
            $encrypted_data = base64_decode($sStr);
            $encrypted_data = self::hex2bin($sStr);
            $decrypted = openssl_decrypt($encrypted_data, 'AES-128-ECB', $key, true);

            return $decrypted;
        }

        public static function hex2bin($hexdata) {
            $bindata = '';
            $length = strlen($hexdata);
            for ($i=0; $i < $length; $i += 2)
            {
                $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
            }
            return $bindata;
        }
    }
?>