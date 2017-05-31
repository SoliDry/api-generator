<?php
namespace rjapi\extension;

use rjapi\helpers\Json;
use rjapi\helpers\Jwt;
use rjapi\types\JwtInterface;

/**
 * Class JWTTrait
 *
 * @package rjapi\extension
 *
 * @property ApiController model
 */
trait JWTTrait
{
    /**
     *  Creates new user with JWT + password hashed
     */
    private function createJwtUser()
    {
        if(empty($this->model->password)) {
            Json::outputErrors(
                [
                    [
                        JSONApiInterface::ERROR_TITLE  => 'Password should be provided',
                        JSONApiInterface::ERROR_DETAIL => 'To get refreshed token in future usage of application - user password should be provided',
                    ],
                ]
            );
        }
        $uniqId          = uniqid();
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model           = $this->getEntity($this->model->id);
        $model->jwt      = Jwt::create($this->model->id, $uniqId);
        $model->password = password_hash($this->model->password, PASSWORD_DEFAULT);
        $model->save();
        $this->model = $model;
        unset($this->model->password);
    }

    /**
     * @param $model
     * @param $jsonApiAttributes
     */
    private function updateJwtUser(&$model, $jsonApiAttributes)
    {
        if(password_verify($jsonApiAttributes[JwtInterface::PASSWORD], $model->password) === false) {
            Json::outputErrors(
                [
                    [
                        JSONApiInterface::ERROR_TITLE  => 'Password is invalid.',
                        JSONApiInterface::ERROR_DETAIL => 'To get refreshed token - pass the correct password',
                    ],
                ]
            );
        }
        $uniqId     = uniqid();
        $model->jwt = Jwt::create($model->id, $uniqId);
        unset($model->password);
    }
}