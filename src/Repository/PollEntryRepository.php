<?php


namespace App\Repository;


use App\DTO\PollResult;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;
use PDO;

class PollEntryRepository extends EntityRepository {
    /**
     * @throws DBALException
     */
    public function getPollResult() {
        $connection = $this->getEntityManager()->getConnection();
        $stm = "SELECT label, count(*) as count FROM poll_entry GROUP BY label";
        $query = $connection->prepare($stm);
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $dto = new PollResult();
        foreach ($results as $result) {
            $dto->setResultByLabel($result["label"], $result["count"]);
        }
        return $dto;
    }

}