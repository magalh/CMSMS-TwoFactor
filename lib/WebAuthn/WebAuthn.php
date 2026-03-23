<?php
/**
 * WebAuthn Server Library
 * Minimal implementation for FIDO2/WebAuthn passkey and security key support.
 * Based on W3C Web Authentication specification.
 * 
 * @license MIT
 */

namespace TwoFactor\WebAuthn;

class WebAuthn
{
    private $rpName;
    private $rpId;
    private $rpIdHash;

    /**
     * @param string $rpName Relying Party display name
     * @param string $rpId   Relying Party ID (domain, e.g. "example.com")
     */
    public function __construct($rpName, $rpId)
    {
        $this->rpName = $rpName;
        $this->rpId = $rpId;
        $this->rpIdHash = hash('sha256', $rpId, true);
    }

    /**
     * Generate a random challenge for registration or authentication.
     * @param int $length
     * @return string Base64url-encoded challenge
     */
    public function createChallenge($length = 32)
    {
        $challenge = random_bytes($length);
        return self::base64UrlEncode($challenge);
    }

    /**
     * Build the options object for navigator.credentials.create()
     *
     * @param string $userId        Unique user identifier
     * @param string $userName      Username / display name
     * @param string $challenge     Base64url challenge from createChallenge()
     * @param array  $excludeCredentialIds  Array of base64url credential IDs to exclude
     * @param string $authenticatorAttachment 'platform'|'cross-platform'|null
     * @param string $residentKey   'required'|'preferred'|'discouraged'
     * @param string $userVerification 'required'|'preferred'|'discouraged'
     * @return array Options array (JSON-encode for JS)
     */
    public function getCreateArgs($userId, $userName, $challenge, $excludeCredentialIds = [], $authenticatorAttachment = null, $residentKey = 'preferred', $userVerification = 'preferred')
    {
        $args = [
            'rp' => [
                'name' => $this->rpName,
                'id'   => $this->rpId,
            ],
            'user' => [
                'id'          => self::base64UrlEncode($userId),
                'name'        => $userName,
                'displayName' => $userName,
            ],
            'challenge' => $challenge,
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],   // ES256
                ['type' => 'public-key', 'alg' => -257],  // RS256
            ],
            'timeout' => 60000,
            'attestation' => 'none',
            'authenticatorSelection' => [
                'residentKey'      => $residentKey,
                'userVerification' => $userVerification,
            ],
        ];

        if ($authenticatorAttachment) {
            $args['authenticatorSelection']['authenticatorAttachment'] = $authenticatorAttachment;
        }

        if (!empty($excludeCredentialIds)) {
            $args['excludeCredentials'] = [];
            foreach ($excludeCredentialIds as $credId) {
                $args['excludeCredentials'][] = [
                    'type' => 'public-key',
                    'id'   => $credId,
                ];
            }
        }

        return $args;
    }

    /**
     * Build the options object for navigator.credentials.get()
     *
     * @param string $challenge           Base64url challenge
     * @param array  $allowCredentialIds  Array of base64url credential IDs
     * @param string $userVerification    'required'|'preferred'|'discouraged'
     * @return array Options array (JSON-encode for JS)
     */
    public function getGetArgs($challenge, $allowCredentialIds = [], $userVerification = 'preferred')
    {
        $args = [
            'challenge'        => $challenge,
            'rpId'             => $this->rpId,
            'timeout'          => 60000,
            'userVerification' => $userVerification,
        ];

        if (!empty($allowCredentialIds)) {
            $args['allowCredentials'] = [];
            foreach ($allowCredentialIds as $credId) {
                $args['allowCredentials'][] = [
                    'type' => 'public-key',
                    'id'   => $credId,
                ];
            }
        }

        return $args;
    }

    /**
     * Process and validate a registration (attestation) response.
     *
     * @param string $clientDataJSON    Base64url-encoded clientDataJSON
     * @param string $attestationObject Base64url-encoded attestationObject
     * @param string $challenge         The original challenge (base64url)
     * @return object { credentialId, credentialPublicKey, signCount }
     * @throws \Exception on validation failure
     */
    public function processCreate($clientDataJSON, $attestationObject, $challenge)
    {
        // 1. Decode and validate clientDataJSON
        $clientDataRaw = self::base64UrlDecode($clientDataJSON);
        $clientData = json_decode($clientDataRaw);
        if (!$clientData) {
            throw new \Exception('Invalid clientDataJSON');
        }
        if ($clientData->type !== 'webauthn.create') {
            throw new \Exception('Invalid type in clientDataJSON');
        }
        if ($clientData->challenge !== $challenge) {
            throw new \Exception('Challenge mismatch');
        }

        // 2. Decode attestationObject (CBOR)
        $attestationRaw = self::base64UrlDecode($attestationObject);
        $attestation = CborDecoder::decode($attestationRaw);
        if (!isset($attestation['authData'])) {
            throw new \Exception('Missing authData in attestation');
        }

        // 3. Parse authenticator data
        $authData = $attestation['authData'];
        if (is_string($authData)) {
            $authDataBin = $authData;
        } else {
            throw new \Exception('Invalid authData format');
        }

        $parsed = $this->parseAuthData($authDataBin);

        // 4. Verify RP ID hash
        if ($parsed['rpIdHash'] !== $this->rpIdHash) {
            throw new \Exception('RP ID hash mismatch');
        }

        // 5. Check user present flag
        if (!($parsed['flags'] & 0x01)) {
            throw new \Exception('User not present');
        }

        // 6. Extract credential data
        if (!$parsed['credentialId'] || !$parsed['credentialPublicKey']) {
            throw new \Exception('Missing credential data in authData');
        }

        // 7. Decode the COSE public key and convert to PEM
        $publicKeyPem = $this->coseKeyToPem($parsed['credentialPublicKey']);

        return (object)[
            'credentialId'        => self::base64UrlEncode($parsed['credentialId']),
            'credentialPublicKey' => $publicKeyPem,
            'signCount'           => $parsed['signCount'],
        ];
    }

    /**
     * Process and validate an authentication (assertion) response.
     *
     * @param string $clientDataJSON    Base64url-encoded
     * @param string $authenticatorData Base64url-encoded
     * @param string $signature         Base64url-encoded
     * @param string $credentialPublicKey PEM public key
     * @param string $challenge         Original challenge (base64url)
     * @param int    $prevSignCount     Previous sign count
     * @return object { signCount }
     * @throws \Exception on validation failure
     */
    public function processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge, $prevSignCount = 0)
    {
        // 1. Decode and validate clientDataJSON
        $clientDataRaw = self::base64UrlDecode($clientDataJSON);
        $clientData = json_decode($clientDataRaw);
        if (!$clientData) {
            throw new \Exception('Invalid clientDataJSON');
        }
        if ($clientData->type !== 'webauthn.get') {
            throw new \Exception('Invalid type in clientDataJSON');
        }
        if ($clientData->challenge !== $challenge) {
            throw new \Exception('Challenge mismatch');
        }

        // 2. Parse authenticator data
        $authDataBin = self::base64UrlDecode($authenticatorData);
        $parsed = $this->parseAuthData($authDataBin);

        // 3. Verify RP ID hash
        if ($parsed['rpIdHash'] !== $this->rpIdHash) {
            throw new \Exception('RP ID hash mismatch');
        }

        // 4. Check user present flag
        if (!($parsed['flags'] & 0x01)) {
            throw new \Exception('User not present');
        }

        // 5. Verify signature
        $clientDataHash = hash('sha256', $clientDataRaw, true);
        $signedData = $authDataBin . $clientDataHash;
        $sigBin = self::base64UrlDecode($signature);

        $valid = openssl_verify($signedData, $sigBin, $credentialPublicKey, OPENSSL_ALGO_SHA256);
        if ($valid !== 1) {
            throw new \Exception('Signature verification failed');
        }

        // 6. Check sign count
        if ($parsed['signCount'] > 0 && $parsed['signCount'] <= $prevSignCount) {
            throw new \Exception('Sign count not incremented - possible cloned authenticator');
        }

        return (object)[
            'signCount' => $parsed['signCount'],
        ];
    }

    /**
     * Parse authenticator data binary.
     */
    private function parseAuthData($authData)
    {
        if (strlen($authData) < 37) {
            throw new \Exception('AuthData too short');
        }

        $rpIdHash = substr($authData, 0, 32);
        $flags = ord($authData[32]);
        $signCount = unpack('N', substr($authData, 33, 4))[1];

        $credentialId = null;
        $credentialPublicKey = null;

        // Attested credential data present (bit 6)
        if ($flags & 0x40) {
            $offset = 37;
            // AAGUID (16 bytes)
            $offset += 16;
            // Credential ID length (2 bytes, big-endian)
            $credIdLen = unpack('n', substr($authData, $offset, 2))[1];
            $offset += 2;
            // Credential ID
            $credentialId = substr($authData, $offset, $credIdLen);
            $offset += $credIdLen;
            // COSE public key (remaining bytes, CBOR-encoded)
            $credentialPublicKey = CborDecoder::decode(substr($authData, $offset));
        }

        return [
            'rpIdHash'            => $rpIdHash,
            'flags'               => $flags,
            'signCount'           => $signCount,
            'credentialId'        => $credentialId,
            'credentialPublicKey' => $credentialPublicKey,
        ];
    }

    /**
     * Convert a COSE key (decoded from CBOR) to PEM format.
     */
    private function coseKeyToPem($coseKey)
    {
        $kty = $coseKey[1] ?? null; // 1 = key type
        $alg = $coseKey[3] ?? null; // 3 = algorithm

        // EC2 key type (kty=2), ES256 (alg=-7)
        if ($kty == 2) {
            $x = $coseKey[-2] ?? null;
            $y = $coseKey[-3] ?? null;
            if (!$x || !$y) {
                throw new \Exception('Missing EC key coordinates');
            }
            // Build uncompressed EC point: 0x04 || x || y
            $point = "\x04" . $x . $y;
            // Wrap in ASN.1 SubjectPublicKeyInfo for P-256
            $der = $this->ecPointToDer($point);
            return $this->derToPem($der, 'PUBLIC KEY');
        }

        // RSA key type (kty=3), RS256 (alg=-257)
        if ($kty == 3) {
            $n = $coseKey[-1] ?? null;
            $e = $coseKey[-2] ?? null;
            if (!$n || !$e) {
                throw new \Exception('Missing RSA key components');
            }
            $der = $this->rsaPublicKeyToDer($n, $e);
            return $this->derToPem($der, 'PUBLIC KEY');
        }

        throw new \Exception('Unsupported key type: ' . $kty);
    }

    /**
     * Wrap an EC P-256 uncompressed point in ASN.1 DER SubjectPublicKeyInfo.
     */
    private function ecPointToDer($point)
    {
        // OID for EC public key + P-256 curve
        $ecOid = hex2bin('06072a8648ce3d0201');       // 1.2.840.10045.2.1
        $p256Oid = hex2bin('06082a8648ce3d030107');    // 1.2.840.10045.3.1.7
        $algId = $this->asn1Sequence($ecOid . $p256Oid);
        $bitString = "\x00" . $point; // prepend unused-bits byte
        $bitStringDer = "\x03" . $this->asn1Length(strlen($bitString)) . $bitString;
        return $this->asn1Sequence($algId . $bitStringDer);
    }

    /**
     * Build RSA SubjectPublicKeyInfo DER from n and e.
     */
    private function rsaPublicKeyToDer($n, $e)
    {
        $nInt = $this->asn1UnsignedInteger($n);
        $eInt = $this->asn1UnsignedInteger($e);
        $rsaKey = $this->asn1Sequence($nInt . $eInt);
        $rsaOid = hex2bin('06092a864886f70d010101'); // 1.2.840.113549.1.1.1
        $nullParam = hex2bin('0500');
        $algId = $this->asn1Sequence($rsaOid . $nullParam);
        $bitString = "\x00" . $rsaKey;
        $bitStringDer = "\x03" . $this->asn1Length(strlen($bitString)) . $bitString;
        return $this->asn1Sequence($algId . $bitStringDer);
    }

    private function asn1Sequence($data)
    {
        return "\x30" . $this->asn1Length(strlen($data)) . $data;
    }

    private function asn1Length($length)
    {
        if ($length < 128) return chr($length);
        if ($length < 256) return "\x81" . chr($length);
        return "\x82" . pack('n', $length);
    }

    private function asn1UnsignedInteger($data)
    {
        // Ensure positive integer (prepend 0x00 if high bit set)
        if (ord($data[0]) & 0x80) {
            $data = "\x00" . $data;
        }
        return "\x02" . $this->asn1Length(strlen($data)) . $data;
    }

    private function derToPem($der, $type)
    {
        return "-----BEGIN {$type}-----\n" .
               chunk_split(base64_encode($der), 64, "\n") .
               "-----END {$type}-----\n";
    }

    public static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
