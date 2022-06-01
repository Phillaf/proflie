<?php

declare(strict_types=1);

namespace Test\Signup;

use PHPUnit\Framework\TestCase;
use Proflie\Signup\CreateAccountApi;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class CreateAccountApiTest extends TestCase
{
    public function testCreate(): void
    {
        $statement = $this->createMock(\mysqli_stmt::class);

        $mysqli = $this->createMock(\MySQLi::class);
        $mysqli->method('prepare')
            ->willReturn($statement);

        $request = $this->createMock(Request::class);
        $request->method('getContent')
                ->willReturn('{
                    "email": "fake@email.com",
                    "username": "username",
                    "password": "12345678"
                }');

        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('status')
            ->with(
                $this->equalTo(201),
                $this->equalTo('Created')
            );

        $signinApi = new CreateAccountApi($mysqli);
        $signinApi->execute($request, $response);
    }

    // duplicate email
    // invalid email
    // duplicate username
    // invalid username
    // invalid pw
}
