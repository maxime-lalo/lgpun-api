<?php

namespace App\Controller;

use App\Entity\NotUsedCard;
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
                $party = $partyRepo->findOneByCode($player->getParty()->getCode());
                $party->setPlayers($party->getPlayers()->getValues());
                $party->setCards($party->getCards()->getValues());
                $party->setNotUsedCards($party->getNotUsedCards()->getValues());
                return $this->createResponse($party);
            }else{
                return $this->createResponse([
                    "error" => "Pas de partie trouvée liée à cet utilisateur"
                ]);
            }
        }
    }

    /**
     * @Route("/parties", methods={"GET","POST","OPTIONS","DELETE"}, name="partiesREST")
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
        }elseif($request->getMethod() == "DELETE"){
            $cardRepo = $this->getDoctrine()->getRepository(Card::class);
            $partyRepo = $this->getDoctrine()->getRepository(Party::class);
            $playerRepo = $this->getDoctrine()->getRepository(Player::class);

            $params = json_decode($request->getContent(),true);

            $party = $partyRepo->findOnyByCode($params['code']);
            if ($party){
                if ($party->getStarted()){
                    return $this->createResponse(["error","Vous ne pouvez pas supprimer une partie en cours !"]);
                }else{
                    $players = $party->getPlayers()->getValues();
                    foreach($players as $player){
                        $player->setParty(null);
                        $player->setBeginningCard(null);
                        $player->setEndingCard(null);
                        $this->getDoctrine()->getManager()->persist($player);
                    }
                    $this->getDoctrine()->getManager()->remove($party);
                    $this->getDoctrine()->getManager()->flush();
                    return $this->createResponse(["success" => "Partie supprimée"]);
                }
            }else{
                return $this->createResponse(["error" => "party not found"]);
            }
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

    /**
     * @Route("/party/quit", methods={"POST","OPTIONS"}, name="partyQuit")
     * @param Request $request
     * @return Response
     */
    function partyQuit(Request $request){
        $userRepo = $this->getDoctrine()->getRepository(Player::class);

        $params = json_decode($request->getContent(),true);
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $user = $userRepo->findOneByFirebaseId($params['user']);
            $user->setParty(null);

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            return $this->createResponse($user);
        }
    }

    /**
     * @Route("/party/start", methods={"POST","OPTIONS"}, name="partyStart")
     * @param Request $request
     * @return Response
     */
    function partyStart(Request $request){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);

        $params = json_decode($request->getContent(),true);
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $party = $partyRepo->findOneByCode($params['party']);
            $party->setStarted(true);

            $this->shuffleCards($party->getCode());
            $this->getDoctrine()->getManager()->persist($party);
            $this->getDoctrine()->getManager()->flush();

            return $this->createResponse($party);
        }
    }

    private function shuffleCards($partyCode){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);
        $nucRepo = $this->getDoctrine()->getRepository(NotUsedCard::class);

        $party = $partyRepo->findOneByCode($partyCode);
        foreach($party->getNotUsedCards()->getValues() as $notUsedCard){
            $party->removeNotUsedCard($notUsedCard);
        }
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($party);
        $manager->flush();

        $cards = $party->getCards()->getValues();
        $players = $party->getPlayers()->getValues();
        shuffle($cards);

        $i = 0;
        foreach ($cards as $card){
            if ($i < 3){
                $notUsedCard = $nucRepo->find($card->getId());
                $party->addNotUsedCard($notUsedCard);
            }else{
                $players[$i-3]->setBeginningCard($card);
                $players[$i-3]->setEndingCard($card);
                $manager->persist($players[$i-3]);
            }
            $i++;
            $manager->persist($party);
            $manager->flush();
        }

    }
}
