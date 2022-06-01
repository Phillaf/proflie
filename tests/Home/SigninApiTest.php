<?php

declare(strict_types=1);

namespace Test\Home;

use PHPUnit\Framework\TestCase;
use Proflie\Home\SigninApi;
use Swoole\Http\Request;
use Swoole\Http\Response;

final class SigninApiTest extends TestCase
{
    public function testEmailNotFound(): void
    {
        $statement = $this->createMock(\mysqli_stmt::class);
        $statement->method('execute')
            ->willReturn(false);

        $mysqli = $this->createMock(\MySQLi::class);
        $mysqli->method('prepare')
            ->willReturn($statement);

        $request = $this->createMock(Request::class);
        $request->method('getContent')
            ->willReturn('{"email": "fake@email.com", "password": "12345678"}');

        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('status')
            ->with(
                $this->equalTo(401),
                $this->equalTo('Unauthorized')
            );

        $signinApi = new SigninApi($mysqli);
        $signinApi->execute($request, $response);
    }

    public function testPasswordInvalid(): void
    {
        $sqlResult = $this->createMock(\mysqli_result::class);
        $sqlResult->method('fetch_assoc')
                  ->willReturn([
                      'id' => 1,
                      'email' => 'test@email.com',
                      'password' => '123456',
                  ]);

        $statement = $this->createMock(\mysqli_stmt::class);
        $statement->method('execute')
            ->willReturn(true);
        $statement->method('get_result')
            ->willReturn($sqlResult);

        $mysqli = $this->createMock(\MySQLi::class);
        $mysqli->method('prepare')
            ->willReturn($statement);

        $request = $this->createMock(Request::class);
        $request->method('getContent')
            ->willReturn('{"email": "fake@email.com", "password": "12345678"}');

        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('status')
            ->with(
                $this->equalTo(401),
                $this->equalTo('Unauthorized')
            );


        $signinApi = new SigninApi($mysqli);
        $signinApi->execute($request, $response);
    }

    public function testPasswordValid(): void
    {
        $password = password_hash('12345678', PASSWORD_DEFAULT);

        $sqlResult = $this->createMock(\mysqli_result::class);
        $sqlResult->method('fetch_assoc')
                  ->willReturn([
                      'id' => 1,
                      'email' => 'test@email.com',
                      'password' => $password,
                  ]);

        $statement = $this->createMock(\mysqli_stmt::class);
        $statement->method('execute')
            ->willReturn(true);
        $statement->method('get_result')
            ->willReturn($sqlResult);

        $mysqli = $this->createMock(\MySQLi::class);
        $mysqli->method('prepare')
            ->willReturn($statement);

        $request = $this->createMock(Request::class);
        $request->method('getContent')
            ->willReturn('{"email": "fake@email.com", "password": "12345678"}');

        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('status')
            ->with(
                $this->equalTo(200),
                $this->equalTo('Success')
            );


        $signinApi = new SigninApi($mysqli);
        $signinApi->execute($request, $response);
    }

    //Testtoken
}
