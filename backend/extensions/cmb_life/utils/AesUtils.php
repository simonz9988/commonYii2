<?php
namespace backend\extensions\cmb_life\utils;
class AesUtils{

    /**
     * 加密方法
     * 默认128位ECB加密,NoPadding
     * mode：支持ECB，CBC两种，默认ECB
     * padding：支持NoPadding，PKCS5，PKCS7三种
     * size：支持128,192,256三种
     * iv：位移向量，ECB模式时为空，CBC模式时不为空
     * @param string $plaintext
     * @return string
     */
    public function encrypt($plaintext, $secretKey, $mode, $padding, $iv, $size) {
        if (empty($plaintext) || empty($secretKey)) {
            return null;
        }
        $secretKey = base64_decode($secretKey);
        $plaintext = trim($plaintext);
        // 选择秘钥长度
        switch ($size) {
            case 192:
                $keySize = MCRYPT_RIJNDAEL_192;
                break;
            case 256:
                $keySize = MCRYPT_RIJNDAEL_256;
                break;
            default:
                $keySize = MCRYPT_RIJNDAEL_128;
        }
        // 选择加密模式
        if ($mode == 'CBC') {
            $mode = MCRYPT_MODE_CBC;
        } else {
            $mode = MCRYPT_MODE_ECB;
        }
        // PKCS5，PKCS7两种方式需要增加padding
        if ($padding == 'PKCS7' || $padding == 'PKCS5') {
            $plaintext = $this->addPadding($plaintext);
        }
        // 设置位移向量
        if (empty($iv)) {
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($keySize, $mode), MCRYPT_RAND);
        }
        
        $encrypt_str = mcrypt_encrypt($keySize, $secretKey, $plaintext, $mode, $iv);
        return base64_encode($encrypt_str);
    }
    
    /**
     * 解密方法
     * 默认128位ECB解密吗，NoPadding
     * mode：支持ECB，CBC两种，默认ECB
     * padding：支持NoPadding，PKCS5，PKCS7三种
     * size：支持128,192,256三种
     * iv：位移向量，ECB模式时为空，CBC模式时不为空
     * @param string $plaintext
     * @return string
     */
    public function decrypt($ciphertext, $secretKey, $mode, $padding, $iv, $size) {
        if (empty($ciphertext) || empty($secretKey)) {
            return null;
        }
        $ciphertext = base64_decode($ciphertext);
        $secretKey = base64_decode($secretKey);
        // 选择秘钥长度
        switch ($size) {
            case 192:
                $keySize = MCRYPT_RIJNDAEL_192;
                break;
            case 256:
                $keySize = MCRYPT_RIJNDAEL_256;
                break;
            default:
                $keySize = MCRYPT_RIJNDAEL_128;
        }
        // 选择加密模式
        if ($mode == 'CBC') {
            $mode = MCRYPT_MODE_CBC;
        } else {
            $mode = MCRYPT_MODE_ECB;
        }
        // 设置位移向量
        if (empty($iv)) {
            $iv = mcrypt_create_iv(mcrypt_get_iv_size($keySize, $mode), MCRYPT_RAND);
        }
        $encrypt_str = mcrypt_decrypt($keySize, $secretKey, $ciphertext, $mode, $iv);
        $encrypt_str = trim($encrypt_str);
        // PKCS5，PKCS7两种方式需要增加padding
        if ($padding == 'PKCS7' || $padding == 'PKCS5') {
            $encrypt_str = $this->stripPadding($encrypt_str);
        }
        return $encrypt_str;
    }
    
    /**
     * 填充算法 PKCS7Padding或PKCS5Padding
     * @param string $text
     * @return string
     */
    function addPadding($text) {
        $blocksize = mcrypt_get_block_size('rijndael-128', 'ecb');
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * 对解密后的已字符填充的明文进行去掉填充字符 PKCS7Padding或PKCS5Padding
     * @param string $text
     * @return string
     */
    function stripPadding($text) {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return $text;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return $text;
        }
        return substr($text, 0, -1*$pad);
    }

    /**
     * 生成AES密钥
     * @param string $size
     * @return string key
     */
     public function genKey($size) {
        $bytes = openssl_random_pseudo_bytes($size / 8);
        return base64_encode($bytes);
     }
    
}
