<?php


namespace App\DTO;


use JsonSerializable;

class PollResult implements JsonSerializable {
    const LABELS = [
        "1",
        "2",
        "3",
        "4",
        "5"
    ];
    private $results = [];
    public function __construct() {
        foreach (self::LABELS as $label) {
            $this->results[$label] = 0;
        }
    }

    public function setResultByLabel(String $label, $count) {
        $this->results[$label] = $count;
    }

    public function getResultByLabel(String $label) {
        return $this->results[$label];
    }

    public function jsonSerialize() {
        $rtn = [];
        foreach ($this->results as $label => $count) {
            $rtn[(int) $label] = (int) $count;
        }
        return $rtn;
    }


}