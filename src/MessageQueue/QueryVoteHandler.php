<?php


namespace App\MessageQueue;


use App\Entity\PollEntry;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class QueryVoteHandler implements MessageHandlerInterface {
    private $logger;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger) {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function __invoke(QueryVoteMessage $message) {
        foreach ($message->getLabels() as $label) {
            /* @var PollEntry $entry */
            // Label is 1 to 5, offset by 1
            $entry = new PollEntry();
            $entry->setLabel($label);
            $this->em->persist($entry);
        }
        $this->em->flush();
    }
}