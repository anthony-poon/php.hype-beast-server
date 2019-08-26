<?php
namespace App\Controller;

use App\DTO\PollResult;
use App\Entity\PollEntry;
use App\MessageQueue\QueryVoteMessage;
use App\Repository\PollEntryRepository;
use Doctrine\DBAL\DBALException;
use ErrorException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class RESTController extends AbstractController {
    const CACHE_NAMESPACE = "hype-beast.poll-app";

    /**
     * @Route("/api/submit", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @throws ErrorException
     * @throws InvalidArgumentException
     *
     * Read request content and send a message to message queue. Read the cache to see the current / cached poll result then increment the result
     */
    public function postResult(Request $request, ValidatorInterface $validator) {
        // Validation
        $labels = array_unique(json_decode($request->getContent(), true));
        if (!$labels) {
            throw new BadRequestHttpException("Malformed JSON.");
        }
        $errors = $validator->validate($labels, new Assert\All([
            new Assert\Range([
                "min" => 1,
                "max" => 5
            ])
        ]));
        if (count($errors)) {
            throw new BadRequestHttpException("Invalid request.");
        }
        $memeCache = MemcachedAdapter::createConnection($_ENV['MEMCACHE_ADDRESS']);
        $adaptor = new MemcachedAdapter($memeCache, self::CACHE_NAMESPACE, $_ENV['CACHE_LIFE_TIME']);
        /* @var PollResult $pollResult */
        $pollResult = $adaptor->get("result", function (ItemInterface $item){
            // This will only run on cache miss
            return $this->initializeCache();
        });
        foreach ($labels as $label) {
            // Increment the cache result
            $count = $pollResult->getResultByLabel($label) + 1;
            $pollResult->setResultByLabel($label, $count);
        }
        $this->dispatchMessage(new QueryVoteMessage($labels));
        return new JsonResponse($pollResult);
    }

    /**
     * @Route("/api/result", methods={"GET"})
     * @return JsonResponse
     * @throws ErrorException
     * @throws InvalidArgumentException
     *
     * Read the cached poll result
     */
    public function getResult() {
        $memeCache = MemcachedAdapter::createConnection($_ENV['MEMCACHE_ADDRESS']);
        $adaptor = new MemcachedAdapter($memeCache, self::CACHE_NAMESPACE, $_ENV['CACHE_LIFE_TIME']);
        /* @var PollResult $cache */
        $cache = $adaptor->get("result", function (ItemInterface $item){
            return $this->initializeCache();
        });
        return new JsonResponse($cache);
    }

    /**
     * @throws DBALException
     */
    private function initializeCache() {
        /* @var PollEntryRepository $repo */
        $repo = $this->getDoctrine()->getRepository(PollEntry::class);
        return $repo->getPollResult();
    }
}