<?php
class MessageEncryption {
    private $cipher = "aes-256-gcm";
    private $keyLength = 32; // 256 bits
    private $tagLength = 16;
    private $ivLength = 12;

    /**
     * Generate a new encryption key
     */
    public function generateKey() {
        return base64_encode(random_bytes($this->keyLength));
    }

    /**
     * Encrypt a message
     * @param string $message The message to encrypt
     * @param string $key The encryption key
     * @return array Encrypted data with IV and tag
     */
    public function encrypt($message, $key) {
        $key = base64_decode($key);
        $iv = random_bytes($this->ivLength);
        
        // Encrypt the message
        $encrypted = openssl_encrypt(
            $message,
            $this->cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($encrypted === false) {
            throw new Exception('Encryption failed');
        }

        return [
            'encrypted' => base64_encode($encrypted),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag)
        ];
    }

    /**
     * Decrypt a message
     * @param string $encrypted The encrypted message
     * @param string $key The encryption key
     * @param string $iv The initialization vector
     * @param string $tag The authentication tag
     * @return string The decrypted message
     */
    public function decrypt($encrypted, $key, $iv, $tag) {
        try {
            $key = base64_decode($key);
            $iv = base64_decode($iv);
            $tag = base64_decode($tag);
            $encrypted = base64_decode($encrypted);

            if ($key === false || $iv === false || $tag === false || $encrypted === false) {
                throw new Exception('Invalid base64 data');
            }

            $decrypted = openssl_decrypt(
                $encrypted,
                $this->cipher,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($decrypted === false) {
                throw new Exception('Decryption failed: ' . openssl_error_string());
            }

            return $decrypted;
        } catch (Exception $e) {
            error_log('Decryption error: ' . $e->getMessage());
            return '[Encrypted Message]';
        }
    }

    /**
     * Generate a key pair for asymmetric encryption
     * @return array Public and private key pair
     */
    public function generateKeyPair() {
        $config = array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        
        $res = openssl_pkey_new($config);
        
        // Get private key
        openssl_pkey_export($res, $privateKey);
        
        // Get public key
        $publicKey = openssl_pkey_get_details($res)['key'];
        
        return [
            'private_key' => $privateKey,
            'public_key' => $publicKey
        ];
    }
}