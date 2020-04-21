<?php

namespace Yosmy\Test;

use PHPUnit\Framework\TestCase;
use LogicException;
use Yosmy;

class FinishAuthenticationWithPasswordTest extends TestCase
{
    public function testFinish()
    {
        $user = 'id';
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';
        $password = 'password';

        $normalizePhone = $this->createMock(Yosmy\NormalizePhone::class);

        $normalizePhone->expects($this->once())
            ->method('normalize')
            ->with(
                $country,
                $prefix,
                $number
            );

        $analyzeFinishAuthenticationWithPasswordIn = $this->createMock(Yosmy\AnalyzePreFinishAuthenticationWithPassword::class);

        $analyzeFinishAuthenticationWithPasswordIn->expects($this->once())
            ->method('analyze')
            ->with(
                $device,
                $country,
                $prefix,
                $number
            );

        $pickPhone = $this->createMock(Yosmy\PickPhone::class);

        $phone = new Phone(
            $user,
            $country,
            $prefix,
            $number
        );

        $pickPhone->expects($this->once())
            ->method('pick')
            ->with(
                null,
                $country,
                $prefix,
                $number
            )
            ->willReturn($phone);

        $assertPassword = $this->createMock(Yosmy\Password\AssertValue::class);

        $assertPassword->expects($this->once())
            ->method('assert')
            ->with(
                $phone->getUser(),
                $password
            )
            ->willReturn(true);

        $analyzePostFinishAuthenticationWithPasswordSuccess = $this->createMock(Yosmy\AnalyzePostFinishAuthenticationWithPasswordSuccess::class);

        $analyzePostFinishAuthenticationWithPasswordSuccess
            ->method('analyze')
            ->with(
                $device,
                $country,
                $prefix,
                $number
            );

        $buildCredential = $this->createMock(Yosmy\BuildCredential::class);

        $credential = new Yosmy\Credential(
            $user,
            'token',
            $country,
            $prefix,
            $number,
            ['role']
        );

        $buildCredential
            ->method('build')
            ->with(
                $phone->getUser()
            )
            ->willReturn($credential);

        $finishAuthenticationWithPassword = new Yosmy\FinishAuthenticationWithPassword(
            $normalizePhone,
            $pickPhone,
            $assertPassword,
            $buildCredential,
            [$analyzeFinishAuthenticationWithPasswordIn],
            [$analyzePostFinishAuthenticationWithPasswordSuccess],
            []
        );

        try {
            $expectedCredential = $finishAuthenticationWithPassword->finish(
                $device,
                $country,
                $prefix,
                $number,
                $password
            );
        } catch (Yosmy\DeniedAuthenticationException $e) {
            throw new LogicException();
        }

        $this->assertEquals(
            $expectedCredential,
            $credential
        );
    }

    public function testFinishHavingInvalidNumberException()
    {
        $normalizePhone = $this->createMock(Yosmy\NormalizePhone::class);

        $normalizePhone->expects($this->once())
            ->method('normalize')
            ->willThrowException(new Yosmy\Phone\InvalidNumberException());

        $pickPhone = $this->createMock(Yosmy\PickPhone::class);

        $assertPassword = $this->createMock(Yosmy\Password\AssertValue::class);

        $buildCredential = $this->createMock(Yosmy\BuildCredential::class);

        $finishAuthenticationWithPassword = new Yosmy\FinishAuthenticationWithPassword(
            $normalizePhone,
            $pickPhone,
            $assertPassword,
            $buildCredential,
            [],
            [],
            []
        );

        $this->expectException(LogicException::class);

        try {
            $finishAuthenticationWithPassword->finish(
                'device',
                'country',
                'prefix',
                'number',
                'password'
            );
        } catch (Yosmy\DeniedAuthenticationException $e) {
            throw new LogicException();
        }
    }

    /**
     * @throws Yosmy\DeniedAuthenticationException
     */
    public function testFinishHavingDeniedAuthenticationException()
    {
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';
        $password = 'password';

        $normalizePhone = $this->createMock(Yosmy\NormalizePhone::class);

        $analyzeFinishAuthenticationWithPasswordIn = $this->createMock(Yosmy\AnalyzePreFinishAuthenticationWithPassword::class);

        $e = new Yosmy\DeniedAuthenticationException('message');

        $analyzeFinishAuthenticationWithPasswordIn->expects($this->once())
            ->method('analyze')
            ->willThrowException($e);

        $analyzePostFinishAuthenticationWithPasswordFail = $this->createMock(Yosmy\AnalyzePostFinishAuthenticationWithPasswordFail::class);

        $analyzePostFinishAuthenticationWithPasswordFail->expects($this->once())
            ->method('analyze')
            ->with(
                $device,
                $country,
                $prefix,
                $number,
                $password,
                $e
            );

        $pickPhone = $this->createMock(Yosmy\PickPhone::class);

        $assertPassword = $this->createMock(Yosmy\Password\AssertValue::class);

        $buildCredential = $this->createMock(Yosmy\BuildCredential::class);

        $finishAuthenticationWithPassword = new Yosmy\FinishAuthenticationWithPassword(
            $normalizePhone,
            $pickPhone,
            $assertPassword,
            $buildCredential,
            [$analyzeFinishAuthenticationWithPasswordIn],
            [],
            [$analyzePostFinishAuthenticationWithPasswordFail]
        );

        $this->expectExceptionObject($e);

        try {
            $finishAuthenticationWithPassword->finish(
                $device,
                $country,
                $prefix,
                $number,
                $password
            );
        } catch (Yosmy\DeniedAuthenticationException $e) {
            throw $e;
        }
    }

    public function testFinishHavingNonexistentUserException()
    {
        $normalizePhone = $this->createMock(Yosmy\NormalizePhone::class);

        $pickPhone = $this->createMock(Yosmy\PickPhone::class);

        $pickPhone->expects($this->once())
            ->method('pick')
            ->willThrowException(new Yosmy\BaseNonexistentPhoneException());

        $assertPassword = $this->createMock(Yosmy\Password\AssertValue::class);

        $buildCredential = $this->createMock(Yosmy\BuildCredential::class);

        $finishAuthenticationWithPassword = new Yosmy\FinishAuthenticationWithPassword(
            $normalizePhone,
            $pickPhone,
            $assertPassword,
            $buildCredential,
            [],
            [],
            []
        );

        $this->expectException(LogicException::class);

        try {
            $finishAuthenticationWithPassword->finish(
                'device',
                'country',
                'prefix',
                'number',
                'password'
            );
        } catch (Yosmy\DeniedAuthenticationException $e) {
            throw new LogicException();
        }
    }

    /**
     * @throws Yosmy\DeniedAuthenticationException
     */
    public function testFinishHavingNoAssertPassword()
    {
        $user = 'id';
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';
        $password = 'password';

        $normalizePhone = $this->createMock(Yosmy\NormalizePhone::class);

        $pickPhone = $this->createMock(Yosmy\PickPhone::class);

        $phone = new Phone(
            $user,
            $country,
            $prefix,
            $number
        );

        $pickPhone->expects($this->once())
            ->method('pick')
            ->willReturn($phone);

        $assertPassword = $this->createMock(Yosmy\Password\AssertValue::class);

        $assertPassword->expects($this->once())
            ->method('assert')
            ->willReturn(false);

        $analyzePostFinishAuthenticationWithPasswordFail = $this->createMock(Yosmy\AnalyzePostFinishAuthenticationWithPasswordFail::class);

        $e = new Yosmy\DeniedAuthenticationException('El pin es incorrecto');

        $analyzePostFinishAuthenticationWithPasswordFail->expects($this->once())
            ->method('analyze')
            ->with(
                $device,
                $country,
                $prefix,
                $number,
                $password,
                $e
            );

        $buildCredential = $this->createMock(Yosmy\BuildCredential::class);

        $finishAuthenticationWithPassword = new Yosmy\FinishAuthenticationWithPassword(
            $normalizePhone,
            $pickPhone,
            $assertPassword,
            $buildCredential,
            [],
            [],
            [$analyzePostFinishAuthenticationWithPasswordFail]
        );

        $this->expectExceptionObject($e);

        try {
            $finishAuthenticationWithPassword->finish(
                $device,
                $country,
                $prefix,
                $number,
                $password
            );
        } catch (Yosmy\DeniedAuthenticationException $e) {
            throw $e;
        }
    }

}