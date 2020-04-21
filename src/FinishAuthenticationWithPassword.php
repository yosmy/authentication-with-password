<?php

namespace Yosmy;

use Yosmy;
use LogicException;

/**
 * @di\service()
 */
class FinishAuthenticationWithPassword
{
    /**
     * @var NormalizePhone
     */
    private $normalizePhone;

    /**
     * @var Yosmy\PickPhone
     */
    private $pickPhone;

    /**
     * @var Yosmy\Password\AssertValue
     */
    private $assertPassword;

    /**
     * @var BuildCredential
     */
    private $buildCredential;

    /**
     * @var AnalyzePreFinishAuthenticationWithPassword[]
     */
    private $analyzePreFinishAuthenticationWithPasswordServices;

    /**
     * @var AnalyzePostFinishAuthenticationWithPasswordSuccess[]
     */
    private $analyzePostFinishAuthenticationWithPasswordSuccessServices;

    /**
     * @var AnalyzePostFinishAuthenticationWithPasswordFail[]
     */
    private $analyzePostFinishAuthenticationWithPasswordFailServices;

    /**
     * @di\arguments({
     *     analyzePreFinishAuthenticationWithPasswordServices:         '#yosmy.pre_finish_authentication_with_password',
     *     analyzePostFinishAuthenticationWithPasswordSuccessServices: '#yosmy.post_finish_authentication_with_password_success',
     *     analyzePostFinishAuthenticationWithPasswordFailServices:    '#yosmy.post_finish_authentication_with_password_fail'
     * })
     *
     * @param NormalizePhone                                       $normalizePhone
     * @param PickPhone                                            $pickPhone
     * @param Password\AssertValue                                 $assertPassword
     * @param BuildCredential                                      $buildCredential
     * @param AnalyzePreFinishAuthenticationWithPassword[]         $analyzePreFinishAuthenticationWithPasswordServices
     * @param AnalyzePostFinishAuthenticationWithPasswordSuccess[] $analyzePostFinishAuthenticationWithPasswordSuccessServices
     * @param AnalyzePostFinishAuthenticationWithPasswordFail[]    $analyzePostFinishAuthenticationWithPasswordFailServices
     */
    public function __construct(
        NormalizePhone $normalizePhone,
        PickPhone $pickPhone,
        Password\AssertValue $assertPassword,
        BuildCredential $buildCredential,
        array $analyzePreFinishAuthenticationWithPasswordServices,
        array $analyzePostFinishAuthenticationWithPasswordSuccessServices,
        array $analyzePostFinishAuthenticationWithPasswordFailServices
    ) {
        $this->normalizePhone = $normalizePhone;
        $this->pickPhone = $pickPhone;
        $this->assertPassword = $assertPassword;
        $this->buildCredential = $buildCredential;
        $this->analyzePreFinishAuthenticationWithPasswordServices = $analyzePreFinishAuthenticationWithPasswordServices;
        $this->analyzePostFinishAuthenticationWithPasswordSuccessServices = $analyzePostFinishAuthenticationWithPasswordSuccessServices;
        $this->analyzePostFinishAuthenticationWithPasswordFailServices = $analyzePostFinishAuthenticationWithPasswordFailServices;
    }

    /**
     * @param string $device
     * @param string $country
     * @param string $prefix
     * @param string $number
     * @param string $password
     *
     * @return Credential
     *
     * @throws DeniedAuthenticationException
     */
    public function finish(
        string $device,
        string $country,
        string $prefix,
        string $number,
        string $password
    ): Credential {
        try {
            $this->normalizePhone->normalize(
                $country,
                $prefix,
                $number
            );
        } catch (Phone\InvalidNumberException $e) {
            throw new LogicException(null, null, $e);
        }
        
        foreach ($this->analyzePreFinishAuthenticationWithPasswordServices as $analyzePreFinishAuthenticationWithPassword) {
            try {
                $analyzePreFinishAuthenticationWithPassword->analyze(
                    $device,
                    $country,
                    $prefix,
                    $number
                );
            } catch (DeniedAuthenticationException $e) {
                foreach ($this->analyzePostFinishAuthenticationWithPasswordFailServices as $analyzePostFinishAuthenticationWithPasswordFail) {
                    $analyzePostFinishAuthenticationWithPasswordFail->analyze(
                        $device,
                        $country,
                        $prefix,
                        $number,
                        $password,
                        $e
                    );
                }

                throw $e;
            }
        }

        try {
            $phone = $this->pickPhone->pick(
                null,
                $country,
                $prefix,
                $number
            );
        } catch (Yosmy\NonexistentPhoneException $e) {
            throw new LogicException(null, null, $e);
        }

        if (!$this->assertPassword->assert(
            $phone->getUser(),
            $password
        )) {
            $e = new DeniedAuthenticationException('El pin es incorrecto');

            foreach ($this->analyzePostFinishAuthenticationWithPasswordFailServices as $analyzePostFinishAuthenticationWithPasswordFail) {
                $analyzePostFinishAuthenticationWithPasswordFail->analyze(
                    $device,
                    $country,
                    $prefix,
                    $number,
                    $password,
                    $e
                );
            }

            throw $e;
        }

        foreach ($this->analyzePostFinishAuthenticationWithPasswordSuccessServices as $analyzePostFinishAuthenticationWithPasswordSuccess) {
            $analyzePostFinishAuthenticationWithPasswordSuccess->analyze(
                $device,
                $country,
                $prefix,
                $number
            );
        }

        return $this->buildCredential->build(
            $phone->getUser()
        );
    }
}
