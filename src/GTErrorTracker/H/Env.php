<?php

namespace GTErrorTracker\H;

/**
 * Description of Env
 *
 * @author kalin-mv
 */
class Env {

    const EVENT_PAGER = 10;

    public static function getDateTime($timestamp = 0) {
        $date = new \DateTime();
        if (intval($timestamp) > 0) {
            $date->setTimestamp($timestamp);
        }
        return $date;
    }
}
?>
