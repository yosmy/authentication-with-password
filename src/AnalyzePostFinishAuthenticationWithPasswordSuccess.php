<?php

namespace Yosmy;

interface AnalyzePostFinishAuthenticationWithPasswordSuccess
{
    /**
     * @param string $device
     * @param string $country
     * @param string $prefix
     * @param string $number
     */
    public function analyze(
        string $device,
        string $country,
        string $prefix,
        string $number
    );
}
