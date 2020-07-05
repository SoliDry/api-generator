<?php

namespace SoliDryTest\Unit\Helpers;

use Illuminate\Support\Facades\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use SoliDry\Extension\BaseJwt;
use SoliDry\Helpers\Jwt;
use SoliDryTest\Unit\TestCase;

class JwtTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'example.com';
        $this->createConfig();
    }

    /**
     * @test
     */
    public function it_creates_and_verifies_jwt_token(): string
    {
        /** @var Token $token */
        $id        = random_int(1, 1000);
        $jwtString = Jwt::create($id);
        $token     = (new Parser())->parse($jwtString);

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals($token->getClaim('uid'), $id);
        $this->assertTrue(Jwt::verify($token));

        return $jwtString;
    }

    /**
     * @test
     * @depends it_creates_and_verifies_jwt_token
     * @param string $jwt
     */
    public function it_handles_jwt(string $jwt): void
    {
        $baseJwt      = new BaseJwt();
        $request      = new Request();
        $request->jwt = $jwt;

        $baseJwt->handle($request, function ($request) {
            $this->assertInstanceOf(Request::class, $request);
        });
    }
}
