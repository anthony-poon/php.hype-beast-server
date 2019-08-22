<?php
namespace App\Controller;

use App\Entity\PollEntry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RESTController extends AbstractController {

    /**
     * @Route("/api/submit", methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function postResult(Request $request, ValidatorInterface $validator) {
        $arr = json_decode($request->getContent(), true);
        if (!$arr) {
            throw new BadRequestHttpException("Malformed JSON.");
        }
        $errors = $validator->validate($arr, new Assert\All([
            new Assert\Range([
                "min" => 1,
                "max" => 5
            ])
        ]));
        if (count($errors)) {
            throw new BadRequestHttpException("Invalid request.");
        }
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(PollEntry::class);
        // Find all, but order by label
        $entries = $repo->findBy([], ['label' => 'ASC']);
        foreach ($arr as $label) {
            /* @var PollEntry $entry */
            // Label is 1 to 5, offset by 1
            $entry = $entries[$label - 1];
            $entry->setCount($entry->getCount() + 1);
            $em->persist($entry);
        }
        $rtn = array_map(function(PollEntry $entry) {
            return $this->normalize($entry);
        }, $entries);
        $em -> flush();
        return new JsonResponse($rtn);
    }

    /**
     * @Route("/api/result", methods={"GET"})
     * @return JsonResponse
     */
    public function getResult() {
        $repo = $this->getDoctrine()->getRepository(PollEntry::class);
        $entries = $repo->findAll();
        $rtn = array_map(function(PollEntry $entry) {
            return $this->normalize($entry);
        }, $entries);
        return new JsonResponse($rtn);
    }

    public function normalize(PollEntry $entry) {
        return [
            "label" => $entry->getLabel(),
            "count" => $entry->getCount()
        ];
    }
}