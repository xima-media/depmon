<?php

namespace Xima\DepmonBundle\Util;

use vierbergenlars\SemVer\SemVerException;
use vierbergenlars\SemVer\version;
use vierbergenlars\SemVer\expression;

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

        // prepare stable
        $stableArray = explode('.', $stable);
        $stableArray[0] = $stableArray[0][0] == 'v' ? substr($stableArray[0], 1) : $stableArray[0];
        // prepare latest
        $latestArray = explode('.', $latest);
        $latestArray[0] = $latestArray[0][0] == 'v' ? substr($latestArray[0], 1) : $latestArray[0];

        if ($stableArray[0] != $latestArray[0] || (isset($stableArray[1]) && isset($latestArray[1]) && $stableArray[1] != $latestArray[1])) {
            $state =  self::STATE_OUT_OF_DATE;
        } else if (isset($stableArray[2]) && isset($latestArray[2]) && $stableArray[2] != $latestArray[2]) {
            $state =  self::STATE_PINNED_OUT_OF_DATE;
        }

        // ToDo: The satisfies function didn't worked as expected. For example the constraint ~4.1 is not satisfying the version 4.9.5.

        if ($state != self::STATE_UP_TO_DATE && $required != null) {
            try {
                $latestVersion = new version($latest);
                if (!$latestVersion->satisfies(new expression($required))) {
                    $state = self::STATE_UP_TO_DATE;
                }
            } catch (SemVerException $e) {

            }
        }

        return $state;
    }
}