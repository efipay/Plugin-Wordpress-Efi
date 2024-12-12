<?php

class Efi_Cypher {
    public static function encrypt_data($data) {
        $key = AUTH_KEY; 
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    public static function decrypt_data($encrypted_data) {
        $key = AUTH_KEY; 
        $decoded = base64_decode($encrypted_data);
        $parts = explode('::', $decoded);
        if (count($parts) != 2) {
            throw new Exception('Dados inválidos');
        }
        list($encrypted, $iv) = $parts;
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
};    