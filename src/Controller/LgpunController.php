<?php

namespace App\Controller;

use App\Entity\NotUsedCard;
use App\Entity\Party;
use App\Entity\Player;
use App\Entity\Card;

use App\Entity\Vote;
use DateTime;
use Doctrine\DBAL\Driver\Connection;
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
     * @Route("/user", methods={"POST","OPTIONS"}, name="userREST")
     * @param Request $request
     * @return Response
     */
    public function playerRest(Request $request){
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $playerRepo = $this->getDoctrine()->getRepository(Player::class);

            $params = json_decode($request->getContent(),true);

            $player = new Player();
            $player->setPseudo($params['pseudo']);
            $player->setIdFirebase($params['id_firebase']);

            $this->getDoctrine()->getManager()->persist($player);
            $this->getDoctrine()->getManager()->flush();

            return $this->createResponse($player);
        }

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
                $party->setVotes($party->getVotes()->getValues());
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
                $newParty->setEnded(false);
                $this->getDoctrine()->getManager()->persist($newParty);
                $this->getDoctrine()->getManager()->flush();
                return $this->createResponse($newParty);
            }
        }elseif($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }elseif($request->getMethod() == "DELETE"){
            $partyRepo = $this->getDoctrine()->getRepository(Party::class);

            $params = json_decode($request->getContent(),true);

            $party = $partyRepo->findOneByCode($params['code']);
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
                $party->setVotes($party->getVotes()->getValues());
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
     * @param Connection $connection
     * @return Response
     */
    function partyStart(Request $request,Connection $connection){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);

        $params = json_decode($request->getContent(),true);
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $party = $partyRepo->findOneByCode($params['party']);
            $party->setStarted(true);
            $party->setEnded(false);
            $party->setRelaunch(false);

            $stmt = $connection->prepare("DELETE a FROM vote a INNER JOIN vote_party b ON a.id = b.vote_id WHERE party_id = ?;");
            $stmt->execute([$party->getId()]);

            $this->shuffleCards($party->getCode());
            $this->getDoctrine()->getManager()->persist($party);
            $this->getDoctrine()->getManager()->flush();

            return $this->createResponse($party);
        }
    }

    /**
     * @Route("/party/hideCards", methods={"POST","OPTIONS"}, name="partyHideCards")
     * @param Request $request
     * @return Response
     */
    function hideCards(Request $request){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);

        $params = json_decode($request->getContent(),true);
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $party = $partyRepo->findOneByCode($params['party']);
            $party->setCardsHidden(true);
            $this->getDoctrine()->getManager()->persist($party);
            $this->getDoctrine()->getManager()->flush();

            return $this->createResponse($party);
        }
    }
    /**
     * @Route("/party/isAlone", methods={"POST","OPTIONS"}, name="partyIsAlone")
     * @param Request $request
     * @return Response
     */
    public function isAlone(Request $request){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);
        $playerRepo = $this->getDoctrine()->getRepository(Player::class);

        $params = json_decode($request->getContent(),true);
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $party = $partyRepo->findOneByCode($params['party']);
            $type = $params['type'];

            $doppelCard = $party->getDoppelCard();
            if ($doppelCard != null){
                $doppelCard = $doppelCard->getId();
            }

            if ($type == 'wolf'){
                $allWolves = [];

                $doppelIsWolf = $doppelCard == 2 || $doppelCard == 3;

                foreach($party->getPlayers() as $player){
                    if ($doppelIsWolf && ($player->getBeginningCard()->getId() == 1) ){
                        $allWolves[] = $player;
                    }
                    if (($player->getBeginningCard()->getId() == 2) || ($player->getBeginningCard()->getId() == 3) ){
                        $allWolves[] = $player;
                    }
                }

                return $this->createResponse($allWolves);
            }else{
                $allFrancs = [];
                $doppelIsFranc = $doppelCard == 5 || $doppelCard == 6;
                foreach($party->getPlayers() as $player){
                    // Si la personne qui est dopel est présente on l'ajoute
                    if ($doppelIsFranc && ($player->getBeginningCard()->getId() == 1) ){
                        $allFrancs[] = $player;
                    }

                    // on ajoute tous les autres francs
                    if (($player->getBeginningCard()->getId() == 5) || ($player->getBeginningCard()->getId() == 6) ){
                        $allFrancs[] = $player;
                    }
                }

                return $this->createResponse($allFrancs);
            }
        }
    }

    private function shuffleCards($partyCode){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);
        $nucRepo = $this->getDoctrine()->getRepository(NotUsedCard::class);

        $now = new DateTime('now');
        $in20s = $now->add(new \DateInterval('PT20S'));

        $party = $partyRepo->findOneByCode($partyCode);
        foreach($party->getNotUsedCards()->getValues() as $notUsedCard){
            $party->removeNotUsedCard($notUsedCard);
        }
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($party);
        $manager->flush();

        $cards = $party->getCards()->getValues();

        $firstCardToPlay = $cards[0];

        $players = $party->getPlayers()->getValues();
        shuffle($cards);

        $i = 0;

        $minCard = 9999;
        $minUser = null;
        foreach ($cards as $card){
            // On récupère la première carte qui va jouer
            if ($firstCardToPlay->getId() == $card->getId()){
                // Si i est < 3 alors la première carte sera une fake card
                if ($i < 3){
                    $party->setFakeTurn($card);
                    $party->setTurn(null);
                    $party->setTurnEnd($in20s);
                }else{
                    $party->setFakeTurn(null);
                    $party->setTurn($players[$i-3]);
                    $party->setTurnEnd($in20s);
                    // Sinon ça sera au tour d'un user
                }
            }
            if ($i < 3){
                $notUsedCard = $nucRepo->find($card->getId());
                $party->addNotUsedCard($notUsedCard);
            }else{
                $players[$i-3]->setBeginningCard($card);
                $players[$i-3]->setEndingCard($card);

                if ($card->getId() <= $minCard){
                    $minCard = $card->getId();
                    $minUser = $players[$i-3];
                }

                $manager->persist($players[$i-3]);
            }
            $i++;
            $manager->persist($party);
        }

        $manager->flush();
    }

    /**
     * @Route("/parties/nextTurn", methods={"GET","POST","OPTIONS"}, name="allPartiesNextTurn")
     * @return Response
     */
    public function nextTurnAllParties(){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);
        $parties = $partyRepo->findAll();

        $now = new DateTime('now');

        foreach($parties as $party){
            if ($party->getTurnEnd() < $now){
                $this->triggerNextTurn($party->getCode());
            }
        }

        return $this->createResponse($now);
    }

    private function triggerNextTurn($partyCode){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);

        // On récupère maintenant et dans 20 secondes pour set la fin du tour
        $now = new DateTime('now');
        $in20s = $now->add(new \DateInterval('PT20S'));

        // On récupère la partie et on set la fin du tour à dans 20s
        $party = $partyRepo->findOneByCode($partyCode);
        $party->setTurnEnd($in20s);

        // On renregistre la fin du tour dans 20sec
        $this->getDoctrine()->getManager()->persist($party);
        $this->getDoctrine()->getManager()->flush();


        // On récupère le fake turn et le turn et on vérifie le quel des deux est activé
        $fakeTurn = $party->getFakeTurn();
        $turn = $party->getTurn();
        if ($fakeTurn == null){
            $actualTurn = [
                'turn' => $turn->getBeginningCard()->getId(),
                'type' => 'card'
            ];
        }else{
            $actualTurn = [
                'turn' => $fakeTurn->getId(),
                'type' => 'fake'
            ];
        }

        // On va venir récupérer toutes les cartes jouables, not used et used
        $allPlayableCards = [];

        foreach($party->getCards() as $card){
            if ($card->getPosition() != 0){
                $allPlayableCards[] = [
                    'card' => $card,
                    'type' => 'card'
                ];
            }
        }

        foreach ($party->getNotUsedCards() as $card){
            foreach ($allPlayableCards as $key => $playableCard){
                if ($card->getId() == $playableCard['card']->getId()){
                    $allPlayableCards[$key]['card'] = $card;
                    $allPlayableCards[$key]['type'] = 'fake';
                }
            }
        }

        // On les trie dans l'ordre
        $swapped = 1;
        while($swapped == 1){
            $swapped = 0;
            $i = 0;
            for($i = 0; $i < (count($allPlayableCards)-1); $i++){
                if ($allPlayableCards[$i]['card']->getId() > $allPlayableCards[$i+1]['card']->getId()){
                    $temp = $allPlayableCards[$i];
                    $allPlayableCards[$i] = $allPlayableCards[$i+1];
                    $allPlayableCards[$i+1] = $temp;
                    $swapped = 1;
                }
            }
        }

        $nextTurn = null;

        // Une fois dans l'ordre on regarde laquelle est jouée actuellement, et on prend celle d'après
        foreach($allPlayableCards as $key => $card){
            if ($allPlayableCards[$key]['card']->getId() == $actualTurn['turn']){
                // Si il y a bien une carte suivante on la set et on la renvoie
                if (isset($allPlayableCards[$key+1])){
                    $this->setTurn($allPlayableCards[$key+1]['card'],$party->getCode());
                    return $allPlayableCards[$key+1];
                }else{
                    // Si il n'y en a pas on termine la partie
                    $party->setStarted(false);
                    $party->setEnded(true);

                    $party->setTurn(null);
                    $party->setFakeTurn(null);
                    $party->setTurnEnd(null);

                    $this->getDoctrine()->getManager()->persist($party);
                    $this->getDoctrine()->getManager()->flush();
                }
            }
        }
    }
    /**
     * @Route("/party/nextTurn", methods={"POST","OPTIONS"}, name="partyNextTurn")
     * @param Request $request
     * @return Response
     */
    public function nextTurnParty(Request $request){
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $params = json_decode($request->getContent(), true);
            $nextTurn = $this->triggerNextTurn($params['party']);
            return $this->createResponse($nextTurn);
        }
    }


    private function setTurn($card,$partyCode){
        $partyRepo = $this->getDoctrine()->getRepository(Party::class);
        $cardRepo = $this->getDoctrine()->getRepository(Card::class);
        $party = $partyRepo->findOneByCode($partyCode);

        // si c'est une vraie carte alors c'est le tour d'un joueur, sinon c'est un fake tour
        if ($card instanceof Card){
            foreach($party->getPlayers() as $player){
                if ($player->getBeginningCard()->getId() == $card->getId()){
                    $party->setTurn($player);
                    $party->setFakeTurn(null);
                }
            }
        }else{
            $party->setFakeTurn($cardRepo->find($card->getId()));
            $party->setTurn(null);
        }

        $this->getDoctrine()->getManager()->persist($party);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * @Route("/party/invertCards", methods={"POST","OPTIONS"}, name="partyInvertCards")
     * @param Request $request
     * @return Response
     */
    public function invertCards(Request $request){
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $partyManager = $this->getDoctrine()->getRepository(Party::class);
            $params = json_decode($request->getContent(), true);

            $firstCard = $params['first_card'];
            $secondCard = $params['second_card'];

            $party = $partyManager->findOneByCode($params['party']);

            $firstPlayer = null;
            $secondPlayer = null;
            foreach($party->getPlayers()->getValues() as $player){
                if ($player->getEndingCard()->getId() == $firstCard){
                    $firstPlayer = $player;
                }elseif($player->getEndingCard()->getId() == $secondCard){
                    $secondPlayer = $player;
                }
            }

            $temp = $firstPlayer->getEndingCard();
            $firstPlayer->setEndingCard($secondPlayer->getEndingCard());
            $secondPlayer->setEndingCard($temp);

            $this->getDoctrine()->getManager()->persist($firstPlayer);
            $this->getDoctrine()->getManager()->persist($secondPlayer);
            $this->getDoctrine()->getManager()->flush();

            return $this->createResponse(['success','swapped']);
        }
    }

    /**
     * @Route("/party/invertNotUsed", methods={"POST","OPTIONS"}, name="partyInvertNotUsed")
     * @param Request $request
     * @return Response
     */
    public function invertNotUsed(Request $request){
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $partyManager = $this->getDoctrine()->getRepository(Party::class);
            $notUsedCardsManager = $this->getDoctrine()->getRepository(NotUsedCard::class);
            $cardsManager = $this->getDoctrine()->getRepository(Card::class);

            $params = json_decode($request->getContent(), true);

            // L'id de la carte du joueur
            $usedCard = $params['used_card'];

            // La carte du joueur mais en objet NotUsedCard
            $usedCardNotUsedCard = $notUsedCardsManager->find($usedCard);

            // La carte not used
            $notUsedCard = $notUsedCardsManager->find($params['not_used_card']);

            // La carte not used mais en objet Card
            $notUsedCardCard = $cardsManager->find($params['not_used_card']);

            $party = $partyManager->findOneByCode($params['party']);
            $party->removeNotUsedCard($notUsedCard);

            $playerToSwap = null;
            foreach($party->getPlayers()->getValues() as $player){
                if ($player->getEndingCard()->getId() == $usedCard){
                    $playerToSwap = $player;
                }
            }


            $playerToSwap->setEndingCard($notUsedCardCard);
            $party->addNotUsedCard($usedCardNotUsedCard);

            $this->getDoctrine()->getManager()->persist($player);
            $this->getDoctrine()->getManager()->persist($party);
            $this->getDoctrine()->getManager()->flush();

            return $this->createResponse(['success','swapped']);
        }
    }

    /**
     * @Route("/party/addVote", methods={"POST","OPTIONS"}, name="partyAddVote")
     * @param Request $request
     * @return Response
     */
    public function addVote(Request $request){
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $partyRepo = $this->getDoctrine()->getRepository(Party::class);
            $uRepo = $this->getDoctrine()->getRepository(Player::class);
            $voteRepo = $this->getDoctrine()->getRepository(Vote::class);

            $manager = $this->getDoctrine()->getManager();
            $params = json_decode($request->getContent(), true);

            $party = $partyRepo->findOneByCode($params['party']);

            $newVote = new Vote();
            $newVote->setTarget($uRepo->find($params['target']));
            $newVote->setUser($uRepo->find($params['user']));

            $manager->persist($newVote);

            $party->addVote($newVote);

            $manager->persist($party);
            $manager->flush();

            return $this->createResponse(['succes','vote added']);
        }
    }

    /**
     * @Route("/party/relaunch", methods={"POST","OPTIONS"}, name="partyRelaunch")
     * @param Request $request
     * @return Response
     */
    public function relaunchParty(Request $request){
        if ($request->getMethod() == "OPTIONS"){
            return $this->createResponse([]);
        }else{
            $partyRepo = $this->getDoctrine()->getRepository(Party::class);

            $manager = $this->getDoctrine()->getManager();
            $params = json_decode($request->getContent(), true);

            $party = $partyRepo->findOneByCode($params['party']);

            $party->setRelaunch(true);
            $party->setStarted(false);
            $party->setEnded(false);
            $manager->persist($party);
            $manager->flush();
            return $this->createResponse(['success','party relaunched']);
        }
    }
}
