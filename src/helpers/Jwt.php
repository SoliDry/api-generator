<?php

namespace rjapi\helpers;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use rjapi\types\ConfigInterface;

class Jwt
{
    private const JWT_SECRETE_KEY = 'app.jwt_secret';

    /**
     * Fulfills the token with data and signs it with key
     * @param int $uid
     * @param string $generatedId
     *
     * @return string
     */
    public static function create(int $uid, string $generatedId) : string
    {
        $signer = new Sha256();

        return (new Builder())->setIssuer($_SERVER['HTTP_HOST'])// Configures the issuer (iss claim)
        ->setAudience($_SERVER['HTTP_HOST'])// Configures the audience (aud claim)
        ->setId($generatedId, true)// Configures the id (jti claim), replicating as a header item
        ->setIssuedAt(time())// Configures the time that the token was issue (iat claim)
        ->setNotBefore(time() + ConfigHelper::getNestedParam(ConfigInterface::JWT, ConfigInterface::ACTIVATE))// Configures the time that the token can be used (nbf claim)
        ->setExpiration(time() + ConfigHelper::getNestedParam(ConfigInterface::JWT, ConfigInterface::EXPIRES))// Configures the expiration time of the token (nbf claim)
        ->set('uid', $uid)// Configures a new claim, called "uid"
        ->sign($signer, $generatedId . config(self::JWT_SECRETE_KEY) . $uid)// glue uniqid + uid
        ->getToken();
    }

    /**
     * Verifies token data and key
     * @param Token $token
     * @param string $generatedId
     *
     * @return bool
     */
    public static function verify(Token $token, string $generatedId)
    {
        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer($_SERVER['HTTP_HOST']);
        $data->setAudience($_SERVER['HTTP_HOST']);
        $data->setId($generatedId);
        $signer = new Sha256();
        $uid    = $token->getClaim('uid');
        return $token->validate($data) && $token->verify($signer, $generatedId . config(self::JWT_SECRETE_KEY) . $uid);
    }
}