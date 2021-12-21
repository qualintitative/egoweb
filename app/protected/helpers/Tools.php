<?php
namespace app\helpers;

use Yii;

class Tools
{
    public static function test()
    {
        echo 'test ...';
    }

    public static function encrypt($data)
    {
        $key = Yii::$app->params['encryptionKey'];
        $algo = 'AES-128-CBC';
        if(isset(Yii::$app->params['encryptionAlgo']) && Yii::$app->params['encryptionAlgo'])
            $algo = Yii::$app->params['encryptionAlgo'];
        if ($algo == 'BF-CBC') {
            $string_padded = $data;
            if (strlen($string_padded) % 8) {
                $string_padded = str_pad(
                    $string_padded,
                    strlen($string_padded) + 8 - strlen($string_padded) % 8,
                    "\0"
                );
            }
            $ivSize = openssl_cipher_iv_length($algo);
            $iv = openssl_random_pseudo_bytes($ivSize);
            $enc = openssl_encrypt($string_padded, $algo, $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
            $encrypted = base64_encode($iv.$enc);
        }else{
            $ivSize = 16;
            $iv = openssl_random_pseudo_bytes($ivSize);
            $ops_encrypt_data = @openssl_encrypt($data, 'AES-128-CBC', $key, false, $iv);
            $encrypted = base64_encode($iv . base64_decode($ops_encrypt_data));
        }
        return $encrypted;
    }

    public static function decrypt($data)
    {
        $key = Yii::$app->params['encryptionKey'];
        $algo = 'AES-128-CBC';
        if(isset(Yii::$app->params['encryptionAlgo']) && Yii::$app->params['encryptionAlgo'])
            $algo = Yii::$app->params['encryptionAlgo'];
        $data = base64_decode($data);
        if ($algo == 'BF-CBC') {
            $ivSize = 8;
            $decrypted = substr(openssl_decrypt($data, 'BF-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING | OPENSSL_DONT_ZERO_PAD_KEY), $ivSize);
        }else{
            $ivSize = 16;
            $iv = substr($data, 0, $ivSize);
            $ops_data = substr($data, $ivSize, strlen($data));
            $decrypted = @openssl_decrypt($ops_data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
        }
        return Tools::ops_pkcs5_unpad($decrypted);
    }

    /**
     * need mbstring module
     */
    private static function ops_pkcs5_unpad($text)
    {
        $text_len = mb_strlen($text, "8bit");
        if($text_len == 0)
            return false;
        $pad = ord($text {$text_len - 1});
        if ($text_len && ! $pad) {
            return rtrim($text, "\0");
        }
        if ($pad > $text_len) {
            return false;
        }
        if (strspn($text, chr($pad), $text_len - $pad) != $pad) {
            return false;
        }
        return mb_substr($text, 0, - 1 * $pad, "8bit");
    }

    public static function mToA($models)
    {
        if (is_array($models)) {
            $arrayMode = true;
        } else {
            $models = array($models);
            $arrayMode = false;
        }
        $result = array();
        foreach ($models as $model) {
            if (!is_object($model)) {
                return false;
            }
            $all = array();
            $attributes = array_keys($model->attributeLabels());
            foreach ($attributes as $key) {
                $all[strtoupper($key)] = $model->$key;
            }    
            if ($arrayMode) {
                array_push($result, $all);
            } else {
                $result = $all;
            }
        }
        return $result;
    }

    public static function sanitizeXml($string)
    {
        return htmlspecialchars(trim(preg_replace('/\s+|&nbsp;/', ' ', $string)), ENT_QUOTES, "UTF-8", false);
    }
}
