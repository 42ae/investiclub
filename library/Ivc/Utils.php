<?php

/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	Ivc
 * @package		Ivc_Error
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */
/**
 * Utils
 * 
 * @author		Alexandre Esser
 * @category	Ivc
 * @package		Ivc_Utils
 */
class Ivc_Utils
{

    public static function convertNavigationAclToObject($config)
    {
        foreach ($config as $key => $value) {
            if (is_string($value) AND $key === "resource") {
                switch ($value) {
                    case 'null':
                        $config[$key] = null;
                        break;
                    default:
                        $config[$key] = new $value();
                        break;
                }
            } elseif (is_array($value)) {
                $config[$key] = self::convertNavigationAclToObject($value);
            }
        }
        return $config;
    }

    public static function generateRandomPassword($lenght = 8)
    {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
        srand((double) microtime() * 1000000);
        $password = '';
        for ($i = 0; $i < $lenght; ++$i) {
            $num = rand() % 33; 
            $tmp = substr($chars, $num, 1); 
            $password .= $tmp;
        } 
        return $password;
    }
    
    public function base64url_encode($data) { 
    	//return (base64_encode($data));
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    }
    
    public function base64url_decode($data) {
    	//return (base64_decode($data, true));
      return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    } 
    
    /**
     * Encrypt a string or text decryptable with decryptText() function
     * String returned is URL safe
     * 
     * @param mixed $dataToEncrypt
     * @return string Encrypted String
     */
    public static function encryptText($dataToEncrypt) {
        return urlencode(self::base64url_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(APPLICATION_SALT), $dataToEncrypt, MCRYPT_MODE_CBC, md5(md5(APPLICATION_SALT)))));
    }

    /**
     * Decrypt a string or text previously encrypted with encryptText() function
     * 
     * @param string $encryptedString
     * @return mixed Decrypted data
     */
    public static function decryptText($encryptedString) {
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(APPLICATION_SALT), self::base64url_decode(urldecode($encryptedString)), MCRYPT_MODE_CBC, md5(md5(APPLICATION_SALT))), "\0");
    }
    
    public static function encryptPassword($password)
    {
        return hash('sha512', $password . md5(APPLICATION_SALT));
    }
    
    public static function getActivationToken($email)
    {
        return  hash('md5', $email . md5(APPLICATION_SALT));
    }
    
    /**
     * A function which take an array as keys
     * Same than array_key_exist but with an array of keys
     * 
     * @param array $array
     * @param array $keys
     * @return boolean
     */
    public static function array_keys_exists($keys, $array)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }
        return true;
    }
}