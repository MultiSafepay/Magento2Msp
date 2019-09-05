<?php

namespace MultiSafepay\Connect\Api;

interface IdealIssuersInterface
{
    /**
     * GET for iDEAL issuers
     * @return string
     */
    public function getIssuers();
}
