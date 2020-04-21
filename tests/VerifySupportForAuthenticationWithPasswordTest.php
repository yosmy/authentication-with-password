<?php

namespace Yosmy\Test;

use PHPUnit\Framework\TestCase;
use Yosmy;
use LogicException;

class VerifySupportForAuthenticationWithPasswordTest extends TestCase
{
    public function testVerify()
    {
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $pickPhone = $this->createMock(Yosmy\PickPhone::class);

        $phone = new Phone(
            'id',
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

        $pickSession = $this->createMock(Yosmy\PickSession::class);

        $pickSession->expects($this->once())
            ->method('pick')
            ->with(
                null,
                $phone->getUser(),
                $device
            );

        $pickPassword = $this->createMock(Yosmy\PickPassword::class);

        $pickPassword->expects($this->once())
            ->method('pick')
            ->with(
                $phone->getUser()
            );

        $verifySupportForAuthenticationWithPassword = new Yosmy\VerifySupportForAuthenticationWithPassword(
            $pickPhone,
            $pickSession,
            $pickPassword
        );

        try {
            $verifySupportForAuthenticationWithPassword->verify(
                $device,
                $country,
                $prefix,
                $number
            );
        } catch (Yosmy\UnsupportedAuthenticationException $e) {
            throw new LogicException();
        }
    }

    /**
     * @throws Yosmy\UnsupportedAuthenticationException
     */
    public function testVerifyHavingPhoneNonexistentUserException()
    {
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $pickPhone = $this->createMock(Yosmy\PickPhone::class);

        $pickPhone->expects($this->once())
            ->method('pick')
            ->willThrowException(new Yosmy\BaseNonexistentPhoneException());

        $pickSession = $this->createMock(Yosmy\PickSession::class);

        $pickPassword = $this->createMock(Yosmy\PickPassword::class);

        $verifySupportForAuthenticationWithPassword = new Yosmy\VerifySupportForAuthenticationWithPassword(
            $pickPhone,
            $pickSession,
            $pickPassword
        );

        $this->expectExceptionObject(new Yosmy\UnsupportedAuthenticationException('yosmy.nonexistent-phone'));

        try {
            $verifySupportForAuthenticationWithPassword->verify(
                $device,
                $country,
                $prefix,
                $number
            );
        } catch (Yosmy\UnsupportedAuthenticationException $e) {
            throw $e;
        }
    }

    /**
     * @throws Yosmy\UnsupportedAuthenticationException
     */
    public function testVerifyHavingSessionNonexistentUserException()
    {
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $pickPhone = $this->createMock(Yosmy\PickPhone::class);

        $user = new Phone(
            'id',
            $country,
            $prefix,
            $number
        );

        $pickPhone->expects($this->once())
            ->method('pick')
            ->willReturn($user);

        $pickSession = $this->createMock(Yosmy\PickSession::class);

        $pickSession->expects($this->once())
            ->method('pick')
            ->willThrowException(new Yosmy\BaseNonexistentSessionException());

        $pickPassword = $this->createMock(Yosmy\PickPassword::class);

        $verifySupportForAuthenticationWithPassword = new Yosmy\VerifySupportForAuthenticationWithPassword(
            $pickPhone,
            $pickSession,
            $pickPassword
        );

        $this->expectExceptionObject(new Yosmy\UnsupportedAuthenticationException('yosmy.nonexistent-session'));

        try {
            $verifySupportForAuthenticationWithPassword->verify(
                $device,
                $country,
                $prefix,
                $number
            );
        } catch (Yosmy\UnsupportedAuthenticationException $e) {
            throw $e;
        }
    }

    /**
     * @throws Yosmy\UnsupportedAuthenticationException
     */
    public function testVerifyHavingPasswordNonexistentUserException()
    {
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $pickPhone = $this->createMock(Yosmy\PickPhone::class);

        $user = new Phone(
            'id',
            $country,
            $prefix,
            $number
        );

        $pickPhone->expects($this->once())
            ->method('pick')
            ->willReturn($user);

        $pickSession = $this->createMock(Yosmy\PickSession::class);

        $pickPassword = $this->createMock(Yosmy\PickPassword::class);

        $pickPassword->expects($this->once())
            ->method('pick')
            ->willThrowException(new Yosmy\UnsupportedAuthenticationException('yosmy.nonexistent-password'));

        $verifySupportForAuthenticationWithPassword = new Yosmy\VerifySupportForAuthenticationWithPassword(
            $pickPhone,
            $pickSession,
            $pickPassword
        );

        $this->expectExceptionObject(new Yosmy\UnsupportedAuthenticationException('yosmy.nonexistent-session'));

        try {
            $verifySupportForAuthenticationWithPassword->verify(
                $device,
                $country,
                $prefix,
                $number
            );
        } catch (Yosmy\UnsupportedAuthenticationException $e) {
            throw $e;
        }
    }
}