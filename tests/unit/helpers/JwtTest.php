<?php

namespace rjapitest\unit\helpers;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use rjapi\helpers\Jwt;
use rjapitest\unit\TestCase;


class JwtTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'example.com';
    }

    /**
     * @test
     */
    public function it_creates_jwt_token() : array
    {
        /** @var Token $token */
        $id        = random_int(1, 1000);
        $uniqueId  = uniqid('', true);
        $jwtString = Jwt::create($id, $uniqueId);
        $token     = (new Parser())->parse($jwtString);
        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals($token->getClaim('uid'), $id);
        return [$token, $uniqueId];
    }

    /**
     * @test
     * @depends it_creates_jwt_token
     * @param array $data
     */
    public function it_verifies_jwt_token(array $data)
    {
        $this->assertTrue(Jwt::verify($data[0], $data[1]));
    }
}
