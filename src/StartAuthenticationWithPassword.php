<?php

namespace Yosmy;

/**
 * @di\service()
 */
class StartAuthenticationWithPassword
{
    /**
     * @var NormalizePhone
     */
    private $normalizePhone;

    /**
     * @var VerifySupportForAuthenticationWithPassword
     */
    private $verifySupportForAuthenticationWithPassword;

    /**
     * @var AnalyzePreStartAuthenticationWithPassword[]
     */
    private $analyzePreStartAuthenticationWithPasswordServices;

    /**
     * @var AnalyzePostStartAuthenticationWithPasswordFail[]
     */
    private $analyzePostStartAuthenticationWithPasswordFailServices;

    /**
     * @var AnalyzePostStartAuthenticationWithPasswordSuccess[]
     */
    private $analyzePostStartAuthenticationWithPasswordSuccessServices;

    /**
     * @di\arguments({
     *     analyzePreStartAuthenticationWithPasswordServices:         '#yosmy.pre_start_authentication_with_password',
     *     analyzePostStartAuthenticationWithPasswordFailServices:    '#yosmy.post_start_authentication_with_password_fail',
     *     analyzePostStartAuthenticationWithPasswordSuccessServices: '#yosmy.post_start_authentication_with_password_success'
     * })
     *
     * @param NormalizePhone                                      $normalizePhone
     * @param VerifySupportForAuthenticationWithPassword          $verifySupportForAuthenticationWithPassword
     * @param AnalyzePreStartAuthenticationWithPassword[]         $analyzePreStartAuthenticationWithPasswordServices
     * @param AnalyzePostStartAuthenticationWithPasswordFail[]    $analyzePostStartAuthenticationWithPasswordFailServices
     * @param AnalyzePostStartAuthenticationWithPasswordSuccess[] $analyzePostStartAuthenticationWithPasswordSuccessServices
     */
    public function __construct(
        NormalizePhone $normalizePhone,
        VerifySupportForAuthenticationWithPassword $verifySupportForAuthenticationWithPassword,
        array $analyzePreStartAuthenticationWithPasswordServices,
        array $analyzePostStartAuthenticationWithPasswordFailServices,
        array $analyzePostStartAuthenticationWithPasswordSuccessServices
    ) {
        $this->normalizePhone = $normalizePhone;
        $this->verifySupportForAuthenticationWithPassword = $verifySupportForAuthenticationWithPassword;
        $this->analyzePreStartAuthenticationWithPasswordServices = $analyzePreStartAuthenticationWithPasswordServices;
        $this->analyzePostStartAuthenticationWithPasswordFailServices = $analyzePostStartAuthenticationWithPasswordFailServices;
        $this->analyzePostStartAuthenticationWithPasswordSuccessServices = $analyzePostStartAuthenticationWithPasswordSuccessServices;
    }

    /**
     * @param string $device
     * @param string $country
     * @param string $prefix
     * @param string $number
     *
     * @throws Phone\InvalidNumberException
     * @throws UnsupportedAuthenticationException
     */
    public function start(
        string $device,
        string $country,
        string $prefix,
        string $number
    ) {
        try {
            $normalization = $this->normalizePhone->normalize(
                $country,
                $prefix,
                $number
            );
        } catch (Phone\InvalidNumberException $e) {
            foreach ($this->analyzePostStartAuthenticationWithPasswordFailServices as $analyzePostStartAuthenticationWithPasswordFail) {
                $analyzePostStartAuthenticationWithPasswordFail->analyze(
                    $device,
                    $country,
                    $prefix,
                    $number,
                    $e
                );
            }

            throw $e;
        }

        try {
            $this->verifySupportForAuthenticationWithPassword->verify(
                $device,
                $normalization->getCountry(),
                $normalization->getPrefix(),
                $normalization->getNumber()
            );
        } catch (UnsupportedAuthenticationException $e) {
            foreach ($this->analyzePostStartAuthenticationWithPasswordFailServices as $analyzePostStartAuthenticationWithPasswordFail) {
                $analyzePostStartAuthenticationWithPasswordFail->analyze(
                    $device,
                    $normalization->getCountry(),
                    $normalization->getPrefix(),
                    $normalization->getNumber(),
                    $e
                );
            }

            throw $e;
        }

        foreach ($this->analyzePostStartAuthenticationWithPasswordSuccessServices as $analyzePostStartAuthenticationWithPasswordSuccess) {
            $analyzePostStartAuthenticationWithPasswordSuccess->analyze(
                $device,
                $normalization->getCountry(),
                $normalization->getPrefix(),
                $normalization->getNumber()
            );
        }
    }
}
