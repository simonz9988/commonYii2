<?php
namespace backend\extensions\cmb_life\utils;
use backend\extensions\cmb_life\lib\phpseclib\Crypt\Crypt_RSA;
/**
 * Rsa加解密类
 * @author dy CMB_CCD_Developer
 * V1.0.1
 *
 */

class RsaUtils
{
    protected $rsa;

    /**
     * X constructor.
     */
    public function __construct()
    {
        $this->rsa = new Crypt_RSA();
    }

    public function encrypt($plaintext, $encryptionMode, $pubKey) 
    {
        $this->rsa->loadKey($pubKey);
        $this->rsa->setEncryptionMode($encryptionMode);
        $ciphertext = $this->rsa->encrypt($plaintext);

        return base64_encode($ciphertext);
    }

    public function decrypt($ciphertext, $encryptionMode, $priKey) 
    {
        $this->rsa->loadKey($priKey);
        $this->rsa->setEncryptionMode($encryptionMode);
        $plaintext = $this->rsa->decrypt(base64_decode($ciphertext));

        return $plaintext;
    }

    public function genKey($size) 
    {
        return $this->rsa->createKey($size);
    }
}

?>