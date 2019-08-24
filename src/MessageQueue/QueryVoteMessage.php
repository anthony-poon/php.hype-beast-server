<?php


namespace App\MessageQueue;


class QueryVoteMessage {
    private $json;
    public function __construct(array $json) {
        $this->json = $json;
    }

    /**
     * @return array
     */
    public function getJson(): array {
        return $this->json;
    }

    /**
     * @param array $json
     */
    public function setJson(array $json): void {
        $this->json = $json;
    }


}