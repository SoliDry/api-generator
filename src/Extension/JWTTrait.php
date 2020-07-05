<?php
namespace SoliDry\Extension;

use SoliDry\Helpers\Json;
use SoliDry\Helpers\Jwt;
use SoliDry\Types\JwtInterface;

/**
 * Class JWTTrait
 *
 * @package SoliDry\Extension
 *
 * @property ApiController model
 */
trait JWTTrait
{
    /**
     *  Creates new user with JWT + password hashed
     */
    protected function createJwtUser()
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

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model           = $this->getEntity($this->model->id);
        $model->jwt      = Jwt::create($this->model->id);
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

        $model->jwt = Jwt::create($model->id);
        unset($model->password);
    }
}