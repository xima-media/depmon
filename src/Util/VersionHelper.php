<?php

namespace Xima\DepmonBundle\Util;


use Composer\Semver\Semver;

class VersionHelper
{


    /**
     * State constants
     * @var int
     */
    const STATE_UP_TO_DATE = 1;
    const STATE_PINNED_OUT_OF_DATE = 2;
    const STATE_OUT_OF_DATE = 3;
    const STATE_INSECURE = 4;

    /**
     * Compare versions to check if they are:
     * 1 - Up to date
     * 2 - Pinned, out of date
     * 3 - Out of date
     *
     * @param $stable
     * @param $latest
     * @param $required
     * @return int
     */
    public static function compareVersions($stable, $latest, $required = null)
    {
        $state = self::STATE_UP_TO_DATE;

        if (explode('.', $stable)[0] != explode('.', $latest)[0] || (isset(explode('.', $stable)[1]) && isset(explode('.', $latest)[1]) && explode('.', $stable)[1] != explode('.', $latest)[1])) {
            $state =  self::STATE_OUT_OF_DATE;
        } else if (isset(explode('.', $stable)[2]) && isset(explode('.', $latest)[2]) && explode('.', $stable)[2] != explode('.', $latest)[2]) {
            $state =  self::STATE_PINNED_OUT_OF_DATE;
        }

        if ($state != self::STATE_UP_TO_DATE && $required != null) {

            if (Semver::satisfies($stable,$required)) {
                $state = self::STATE_UP_TO_DATE;
            }
        }

        return $state;
    }
}