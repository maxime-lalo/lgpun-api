<?php

namespace App\Controller;

use App\Entity\Party;
use App\Entity\Player;
use App\Entity\Card;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/lgpun", name="lgpun_")
 */
class LgpunController extends AbstractController
{
    private function createResponse($content):Response{
        $response = new Response();
        $response->setContent(json_encode($content));
        return $response;
    }

    /**
     * @Route("/cards", methods={"GET","OPTIONS"}, name="getCards")
     */
    public function getCards():Response{
        $cards = $this->getDoctrine()->getRepository(Card::class)->findAll();
        return $this->createResponse($cards);
    }

    /**
     * @Route("/cards/playable", methods={"GET","OPTIONS"}, name="getPlayableCards")
     */
    public function getPlayableCards():Response{
        $cards = $this->getDoctrine()->getRepository(Card::class)->findAll();
        $finalCards = [];
        foreach ($cards as $card){
            if ($card->getName() == "Loup Garou" || $card->getName() == "Franc-Maçon"){
                $finalCards[] = $card;
                $finalCards[] = $card;
            }elseif($card->getName() == "Villageois"){
                $finalCards[] = $card;
                $finalCards[] = $card;
                $finalCards[] = $card;
            }else{
                $finalCards[] = $card;
            }
        }
        return $this->createResponse($finalCards);
    }

    /**
     * @Route("/cards/{id}", methods={"GET","OPTIONS"}, name="getSingleCard")
     * @param int $id
     * @return Response
     */
    public function getCard(int $id):Response{
        $card = $this->getDoctrine()->getRepository(Card::class)->find($id);
        return $this->createResponse($card);
    }

    /**
     * @Route("/parties/getByUser/{user}", methods={"GET","OPTIONS"}, name="getPartyByUser")
     * @param string $user
     * @param Request $request
     * @return Response
     */
    public function getPartyByUser(string $user,Request $request){
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $partyRepo = $this->getDoctrine()->getRepository(Party::class);
            $playerRepo = $this->getDoctrine()->getRepository(Player::class);

            $player = $playerRepo->findOneByFirebaseId($user);
            if ($player instanceof Player){
                $party = $partyRepo->find(19);

                return $this->createResponse($party);
            }else{
                return $this->createResponse([
                    "error" => "Pas de partie trouvée liée à cet utilisateur"
                ]);
            }
        }
    }

    /**
     * @Route("/parties", methods={"GET","POST","OPTIONS"}, name="partiesREST")
     * @param Request $request
     * @return Response
     */
    public function partiesREST(Request $request): Response
    {
        if ($request->getMethod() == "POST"){
            $cardRepo = $this->getDoctrine()->getRepository(Card::class);
            $partyRepo = $this->getDoctrine()->getRepository(Party::class);
            $playerRepo = $this->getDoctrine()->getRepository(Player::class);

            $params = json_decode($request->getContent(),true);

            $partyExists = $partyRepo->findOneByCode($params['code']);
            if ($partyExists instanceof Party){
                return $this->createResponse(['error','Ce code partie est déjà utilisé']);
            }else{
                $newParty = new Party();

                foreach($params['cards'] as $card){
                    $cardRepo->find($card);
                    $newParty->addCard($cardRepo->find($card));
                }

                $creator = $playerRepo->findOneByFirebaseId($params['creator']);

                $newParty->addPlayer($creator);
                $newParty->setCode($params['code']);
                $newParty->setCreator($creator);

                $newParty->setCardsHidden(false);
                $newParty->setStarted(false);

                $this->getDoctrine()->getManager()->persist($newParty);
                $this->getDoctrine()->getManager()->flush();
                return $this->createResponse($newParty);
            }
        }elseif($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $parties = $this->getDoctrine()->getRepository(Party::class)->findAll();
            foreach ($parties as $party){
                $party->setCards($party->getCards()->getValues());
                $party->setPlayers($party->getPlayers()->getValues());
            }
            return $this->createResponse($parties);
        }
    }

    /**
     * @Route("/party/join", methods={"POST","OPTIONS"}, name="partyJoin")
     * @param Request $request
     * @return Response
     */
    function partyJoin(Request $request){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);
        $userRepo = $this->getDoctrine()->getRepository(Player::class);

        $params = json_decode($request->getContent(),true);
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $party = $partyRepo->findOneByCode($params['party']);
            $user = $userRepo->findOneByFirebaseId($params['user']);

            $party->addPlayer($user);
            $this->getDoctrine()->getManager()->persist($party);
            $this->getDoctrine()->getManager()->flush();

            return $this->createResponse($party);
        }
    }
}
