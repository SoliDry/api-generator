<?php

namespace rjapitest\unit\extensions;

use rjapi\extension\BaseModel;
use rjapi\extension\JWTTrait;
use rjapi\types\ModelsInterface;
use rjapitest\_data\UserFixture;
use rjapitest\unit\TestCase;

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

    public function setUp()
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
        $this->createJwtUser();
    }

    /**
     * @test
     */
    public function it_updates_jwt_user()
    {
        $user = $this->getEntity(1); // fake id
        $this->assertInstanceOf(BaseModel::class, $user);
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