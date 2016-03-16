<?php

namespace GTErrorTracker\H;

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
