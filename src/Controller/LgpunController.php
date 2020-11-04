<?php

namespace App\Controller;

use App\Entity\Party;
use App\Entity\Player;
use Doctrine\Persistence\ObjectManager;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Card;
/**
 * @Route("/lgpun", name="lgpun_")
 */
class LgpunController extends AbstractController
{
    private function createResponse($content):Response{
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Access-Control-Allow-Origin');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
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
     * @Route("/parties", methods={"GET","OPTIONS"}, name="getParties")
     */
    public function getParties(): Response
    {
        $parties = $this->getDoctrine()->getRepository(Party::class)->findAll();
        return $this->createResponse($parties);
    }

    /**
     * @Route("/parties", methods={"POST","OPTIONS"}, name="createParty")
     * @param Request $request
     * @return Response
     */
    public function createParty(Request $request){
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
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
                $newParty->setNumberOfPlayers($params['numberOfPlayers']);
                $newParty->setCardsHidden(false);
                $newParty->setLastTurn("");
                $newParty->setTurn("");
                $newParty->setStarted(false);

                $this->getDoctrine()->getManager()->persist($newParty);
                $this->getDoctrine()->getManager()->flush();
                return $this->createResponse($newParty);
            }
        }
    }
}
