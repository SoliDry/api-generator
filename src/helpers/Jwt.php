<?php
namespace rjapi\helpers;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use rjapi\types\ConfigInterface;

class Jwt
{
    public static function create($uid, $generatedId): string
    {
        $signer = new Sha256();
        return (new Builder())->setIssuer($_SERVER['HTTP_HOST']) // Configures the issuer (iss claim)
        ->setAudience($_SERVER['HTTP_HOST']) // Configures the audience (aud claim)
        ->setId($generatedId, true) // Configures the id (jti claim), replicating as a header item
        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
        ->setNotBefore(time() + ConfigHelper::getJwtParam(ConfigInterface::ACTIVATE)) // Configures the time that the token can be used (nbf claim)
        ->setExpiration(time() + ConfigHelper::getJwtParam(ConfigInterface::EXPIRES)) // Configures the expiration time of the token (nbf claim)
        ->set('uid', $uid) // Configures a new claim, called "uid"
        ->sign($signer, $generatedId . $uid) // glue uniqid + uid
        ->getToken();
    }

    public static function verify(Token $token, string $generatedId)
    {
        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer($_SERVER['HTTP_HOST']);
        $data->setAudience($_SERVER['HTTP_HOST']);
        $data->setId($generatedId);
        return $token->validate($data);
    }
}