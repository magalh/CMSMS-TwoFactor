<?php
/**
 * Minimal CBOR decoder for WebAuthn attestation/COSE key parsing.
 * Supports the subset of CBOR needed for FIDO2.
 * 
 * @license MIT
 */

namespace TwoFactor\WebAuthn;

class CborDecoder
{
    /**
     * Decode a CBOR binary string.
     * @param string $data
     * @return mixed
     */
    public static function decode($data)
    {
        $offset = 0;
        return self::parseItem($data, $offset);
    }

    private static function parseItem($data, &$offset)
    {
        if ($offset >= strlen($data)) {
            throw new \Exception('CBOR: unexpected end of data');
        }

        $initial = ord($data[$offset]);
        $majorType = $initial >> 5;
        $additionalInfo = $initial & 0x1F;
        $offset++;

        $value = self::parseAdditionalInfo($data, $offset, $additionalInfo);

        switch ($majorType) {
            case 0: // unsigned integer
                return $value;

            case 1: // negative integer
                return -1 - $value;

            case 2: // byte string
                $bytes = substr($data, $offset, $value);
                $offset += $value;
                return $bytes;

            case 3: // text string
                $text = substr($data, $offset, $value);
                $offset += $value;
                return $text;

            case 4: // array
                $arr = [];
                for ($i = 0; $i < $value; $i++) {
                    $arr[] = self::parseItem($data, $offset);
                }
                return $arr;

            case 5: // map
                $map = [];
                for ($i = 0; $i < $value; $i++) {
                    $key = self::parseItem($data, $offset);
                    $val = self::parseItem($data, $offset);
                    $map[$key] = $val;
                }
                return $map;

            case 6: // tagged value (ignore tag, return value)
                return self::parseItem($data, $offset);

            case 7: // simple/float
                if ($additionalInfo === 20) return false;
                if ($additionalInfo === 21) return true;
                if ($additionalInfo === 22) return null;
                return $value;

            default:
                throw new \Exception('CBOR: unsupported major type ' . $majorType);
        }
    }

    private static function parseAdditionalInfo($data, &$offset, $info)
    {
        if ($info < 24) return $info;
        if ($info === 24) {
            $val = ord($data[$offset]);
            $offset++;
            return $val;
        }
        if ($info === 25) {
            $val = unpack('n', substr($data, $offset, 2))[1];
            $offset += 2;
            return $val;
        }
        if ($info === 26) {
            $val = unpack('N', substr($data, $offset, 4))[1];
            $offset += 4;
            return $val;
        }
        if ($info === 27) {
            $high = unpack('N', substr($data, $offset, 4))[1];
            $low = unpack('N', substr($data, $offset + 4, 4))[1];
            $offset += 8;
            return ($high << 32) | $low;
        }
        return 0;
    }
}
