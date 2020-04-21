<?php

namespace Yosmy;

interface AnalyzePreFinishAuthenticationWithPassword
{
    /**
     * @param string $device
     * @param string $country
     * @param string $prefix
     * @param string $number
     *
     * @throws DeniedAuthenticationException
     */
    public function analyze(
        string $device,
        string $country,
        string $prefix,
        string $number
    );
}
