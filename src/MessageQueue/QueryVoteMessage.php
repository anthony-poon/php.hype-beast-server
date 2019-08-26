<?php


namespace App\MessageQueue;


class QueryVoteMessage {
    private $labels;
    public function __construct(array $labels) {
        $this->labels = $labels;
    }

    /**
     * @return mixed
     */
    public function getLabels() {
        return $this->labels;
    }

    /**
     * @param mixed $labels
     */
    public function setLabels($labels): void {
        $this->labels = $labels;
    }



}