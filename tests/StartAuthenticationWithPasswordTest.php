<?php

namespace Yosmy\Test;

use PHPUnit\Framework\TestCase;
use LogicException;
use Yosmy;
use Yosmy\UnsupportedAuthenticationException;

class StartAuthenticationWithPasswordTest extends TestCase
{
    public function testStart()
    {
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $normalization = new Yosmy\Phone\Normalization(
            $country,
            $prefix,
            $number
        );

        $normalizePhone = $this->createMock(Yosmy\NormalizePhone::class);

        $normalizePhone->expects($this->once())
            ->method('normalize')
            ->with(
                $country,
                $prefix,
                $number
            )
            ->willReturn($normalization);

        $verifySupportForAuthenticationWithPassword = $this->createMock(Yosmy\VerifySupportForAuthenticationWithPassword::class);

        $verifySupportForAuthenticationWithPassword->expects($this->once())
            ->method('verify')
            ->with(
                $device,
                $normalization->getCountry(),
                $normalization->getPrefix(),
                $normalization->getNumber()
            );

        $analyzePostStartAuthenticationWithPasswordSuccess = $this->createMock(Yosmy\AnalyzePostStartAuthenticationWithPasswordSuccess::class);

        $analyzePostStartAuthenticationWithPasswordSuccess->expects($this->once())
            ->method('analyze')
            ->with(
                $device,
                $normalization->getCountry(),
                $normalization->getPrefix(),
                $normalization->getNumber()
            );

        $startAuthenticationWithPassword = new Yosmy\StartAuthenticationWithPassword(
            $normalizePhone,
            $verifySupportForAuthenticationWithPassword,
            [],
            [],
            [$analyzePostStartAuthenticationWithPasswordSuccess]
        );

        try {
            $startAuthenticationWithPassword->start(
                $device,
                $normalization->getCountry(),
                $normalization->getPrefix(),
                $normalization->getNumber()
            );
        } catch (Yosmy\Phone\InvalidNumberException | Yosmy\UnsupportedAuthenticationException $e) {
            throw new LogicException();
        }
    }

    /**
     * @throws Yosmy\Phone\InvalidNumberException
     */
    public function testStartHavingInvalidNumberException()
    {
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $e = new Yosmy\Phone\InvalidNumberException();

        $normalizePhone = $this->createMock(Yosmy\NormalizePhone::class);

        $normalizePhone->expects($this->once())
            ->method('normalize')
            ->with(
                $country,
                $prefix,
                $number
            )
            ->willThrowException($e);

        $verifySupportForAuthenticationWithPassword = $this->createMock(Yosmy\VerifySupportForAuthenticationWithPassword::class);

        $analyzePostStartAuthenticationWithPasswordFail = $this->createMock(Yosmy\AnalyzePostStartAuthenticationWithPasswordFail::class);

        $analyzePostStartAuthenticationWithPasswordFail->expects($this->once())
            ->method('analyze')
            ->with(
                $device,
                $country,
                $prefix,
                $number,
                $e
            );

        $this->expectException(Yosmy\Phone\InvalidNumberException::class);

        $startAuthenticationWithPassword = new Yosmy\StartAuthenticationWithPassword(
            $normalizePhone,
            $verifySupportForAuthenticationWithPassword,
            [],
            [$analyzePostStartAuthenticationWithPasswordFail],
            []
        );

        try {
            $startAuthenticationWithPassword->start(
                $device,
                $country,
                $prefix,
                $number
            );
        } catch (Yosmy\Phone\InvalidNumberException $e) {
            throw $e;
        } catch (Yosmy\UnsupportedAuthenticationException $e) {
            throw new LogicException();
        }
    }

    /**
     * @throws Yosmy\UnsupportedAuthenticationException
     */
    public function testStartHavingUnsupportedAuthenticationExceptionOnVerifySupport()
    {
        $device = 'device';
        $country = 'country';
        $prefix = 'prefix';
        $number = 'number';

        $normalization = new Yosmy\Phone\Normalization(
            $country,
            $prefix,
            $number
        );

        $e = new Yosmy\UnsupportedAuthenticationException('reason');

        $normalizePhone = $this->createMock(Yosmy\NormalizePhone::class);

        $normalizePhone->expects($this->once())
            ->method('normalize')
            ->willReturn($normalization);

        $verifySupportForAuthenticationWithPassword = $this->createMock(Yosmy\VerifySupportForAuthenticationWithPassword::class);

        $verifySupportForAuthenticationWithPassword->expects($this->once())
            ->method('verify')
            ->willThrowException($e);

        $analyzePostStartAuthenticationWithPasswordFail = $this->createMock(Yosmy\AnalyzePostStartAuthenticationWithPasswordFail::class);

        $analyzePostStartAuthenticationWithPasswordFail->expects($this->once())
            ->method('analyze')
            ->with(
                $device,
                $normalization->getCountry(),
                $normalization->getPrefix(),
                $normalization->getNumber(),
                $e
            );

        $this->expectExceptionObject($e);

        $startAuthenticationWithPassword = new Yosmy\StartAuthenticationWithPassword(
            $normalizePhone,
            $verifySupportForAuthenticationWithPassword,
            [],
            [$analyzePostStartAuthenticationWithPasswordFail],
            []
        );

        try {
            $startAuthenticationWithPassword->start(
                $device,
                $country,
                $prefix,
                $number
            );
        } catch (Yosmy\Phone\InvalidNumberException $e) {
            throw new LogicException();
        } catch (UnsupportedAuthenticationException $e) {
            throw $e;
        }
    }
}