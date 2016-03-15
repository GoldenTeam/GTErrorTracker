<?php

namespace GTErrorTracker\H;

class ResultException extends \RuntimeException {
    public function __construct($previous = null) {
        parent::__construct(GTResult::getMessage(), null, $previous);
    }
}