<?php

namespace Yosmy;

use JsonSerializable;

interface AnalyzePostFinishAuthenticationWithPasswordFail
{
    /**
     * @param string           $device
     * @param string           $country
     * @param string           $prefix
     * @param string           $number
     * @param string           $password
     * @param JsonSerializable $e
     */
    public function analyze(
        string $device,
        string $country,
        string $prefix,
        string $number,
        string $password,
        JsonSerializable $e
    );
}
