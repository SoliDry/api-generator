<?php

namespace SoliDryTest\Unit\Extensions;

use SoliDry\Extension\BaseModel;
use SoliDry\Extension\JWTTrait;
use SoliDry\Types\ModelsInterface;
use SoliDryTest\_data\UserFixture;
use SoliDryTest\Unit\TestCase;

class User extends BaseModel
{
    // >>>props>>>
    protected $primaryKey = 'id';
    protected $table      = 'user';
    public    $timestamps = false;
    // <<<props<<<
    // >>>methods>>>

    // <<<methods<<<
}

class JwtTraitTest extends TestCase
{
    use JWTTrait;

    private $model;

    public function setUp(): void
    {
        parent::setUp();
        $this->model           = new User();
        $this->model->id       = 1;
        $this->model->password = 'secret';
        $_SERVER['HTTP_HOST'] = 'example.com';
    }

    /**
     * @uses getEntity
     * @test
     */
    public function it_creates_jwt_user()
    {
        $this->createJwtUser();
        $this->assertEmpty($this->model->password);
        // 2nd call with empty password to emulate error
        // @todo: refactor die to returns Response architecture
//        $this->createJwtUser();
    }

    /**
     * @test
     */
    public function it_updates_jwt_user()
    {
        $user = $this->getEntity(1); // fake id
        $this->assertInstanceOf(BaseModel::class, $user);

        $user->password = password_hash($user->password, PASSWORD_DEFAULT);
        $this->updateJwtUser($user, ['password' => 'secret']);
        UserFixture::delete($user->id);
    }

    /**
     * Params needed for internal calls
     * @param $id
     * @param array $data
     * @return \Modules\V2\Entities\User
     */
    private function getEntity($id, array $data = ModelsInterface::DEFAULT_DATA)
    {
        return UserFixture::createAndGet();
    }
}