<?php
namespace App\Controller;

use App\Entity\PollEntry;
use App\MessageQueue\QueryVoteMessage;
use Memcached;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
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
     * @throws \ErrorException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function postResult(Request $request, ValidatorInterface $validator) {
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
        $adaptor = new MemcachedAdapter($memeCache, self::CACHE_NAMESPACE, 0);
        $this->dispatchMessage(new QueryVoteMessage($labels));
        $adaptor->delete("result");
        return new JsonResponse([
            "status" => "ok"
        ]);
    }

    /**
     * @Route("/api/cache", methods={"GET"})
     * @return JsonResponse
     * @throws \ErrorException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getCache() {
        $memeCache = MemcachedAdapter::createConnection($_ENV['MEMCACHE_ADDRESS']);
        $adaptor = new MemcachedAdapter($memeCache, self::CACHE_NAMESPACE, 0);
        /* @var ItemInterface $cache */
        $cache = $adaptor->get("result", function (ItemInterface $item){
            return $this->initializeCache();
        });
        $cache = json_decode($cache, true);
        return new JsonResponse($cache);
    }

    /**
     * @Route("/api/cache", methods={"POST"})
     * @return JsonResponse
     * @throws \ErrorException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function flushCache() {
        $memeCache = MemcachedAdapter::createConnection($_ENV['MEMCACHE_ADDRESS']);
        $adaptor = new MemcachedAdapter($memeCache, self::CACHE_NAMESPACE, 0);
        $cache = $adaptor->get("result", function (ItemInterface $item){
            return $this->initializeCache();
        });
        $cache = json_decode($cache, true);
        $this->dispatchMessage(new QueryVoteMessage($cache["labels"]));
        $adaptor->delete("result");
        return new JsonResponse([
            "status" => "ok"
        ]);
    }

    /**
     * @Route("/api/cache", methods={"DELETE"})
     * @throws \ErrorException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function deleteCache() {
        $memeCache = MemcachedAdapter::createConnection($_ENV['MEMCACHE_ADDRESS']);
        $adaptor = new MemcachedAdapter($memeCache, self::CACHE_NAMESPACE, 0);
        $adaptor->delete("result");
        return new JsonResponse([
            "status" => "ok"
        ]);
    }
    /**
     * @Route("/api/result", methods={"GET"})
     * @return JsonResponse
     */
    public function getResult() {
        // https://symfony.com/doc/current/messenger/handler_results.html
//        $envelope = $this->dispatchMessage(new QueryVoteMessage([]));
        /* @var HandledStamp $handledStamp */
//        $handledStamp = $envelope->last(HandledStamp::class);
//        $result = $handledStamp->getResult();
        return new JsonResponse([
            "status" => "ok"
        ]);
    }

    private function initializeCache() {
        $repo = $this->getDoctrine()->getRepository(PollEntry::class);
        $entries = $repo->findBy([], ['label' => 'ASC']);
        $entries = array_map(function (PollEntry $entry) {
            return $this->normalize($entry);
        }, $entries);
        return json_encode([
            "hits" => 1,
            "labels" => [
                "1" => 0,
                "2" => 0,
                "3" => 0,
                "4" => 0,
                "5" => 0
            ],
            "result" => $entries
        ]);
    }

    private function normalize(PollEntry $entry) {
        return [
            "label" => $entry->getLabel(),
            "count" => $entry->getCount()
        ];
    }
}