<?php

namespace Yosmy;

/**
 * @di\service()
 */
class VerifySupportForAuthenticationWithPassword
{
    /**
     * @var PickPhone
     */
    private $pickPhone;

    /**
     * @var PickSession
     */
    private $pickSession;

    /**
     * @var PickPassword
     */
    private $pickPassword;

    /**
     * @param PickPhone    $pickPhone
     * @param PickSession  $pickSession
     * @param PickPassword $pickPassword
     */
    public function __construct(
        PickPhone $pickPhone,
        PickSession $pickSession,
        PickPassword $pickPassword
    ) {
        $this->pickPhone = $pickPhone;
        $this->pickSession = $pickSession;
        $this->pickPassword = $pickPassword;
    }

    /**
     * @param string $device
     * @param string $country
     * @param string $prefix
     * @param string $number
     *
     * @throws UnsupportedAuthenticationException
     */
    public function verify(
        string $device,
        string $country,
        string $prefix,
        string $number
    ) {
        try {
            $phone = $this->pickPhone->pick(
                null,
                $country,
                $prefix,
                $number
            );
        } catch (NonexistentPhoneException $e) {
            throw new UnsupportedAuthenticationException('yosmy.nonexistent-phone');
        }

        try {
            $this->pickSession->pick(
                null,
                $phone->getUser(),
                $device
            );
        } catch (NonexistentSessionException $e) {
            throw new UnsupportedAuthenticationException('yosmy.nonexistent-session');
        }

        try {
            $this->pickPassword->pick(
                $phone->getUser()
            );
        } catch (NonexistentPasswordException $e) {
            throw new UnsupportedAuthenticationException('yosmy.nonexistent-password');
        }
    }
}
