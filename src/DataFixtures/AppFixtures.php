<?php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\NotUsedCard;
use App\Entity\Player;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $cards = file_get_contents(__DIR__ . "/cards.json");
        $cards = json_decode($cards,true);
        foreach ($cards as $card){
            $newCard = new Card();
            $newCard->setDescription($card['desc']);
            $newCard->setName($card['name']);
            $newCard->setPhoto($card['photo']);
            $newCard->setPosition($card['position']);
            $newCard->setHelp($card['help']);
            $newCard->setId($card['id']);
            $manager->persist($newCard);

            $newNotUsedCard = new NotUsedCard();
            $newNotUsedCard->setDescription($card['desc']);
            $newNotUsedCard->setName($card['name']);
            $newNotUsedCard->setPhoto($card['photo']);
            $newNotUsedCard->setPosition($card['position']);
            $newNotUsedCard->setHelp($card['help']);
            $newNotUsedCard->setId($card['id']);
            $manager->persist($newNotUsedCard);
        }

        $playerRepo = $manager->getRepository(Player::class);
        $allUsers = $playerRepo->findAll();
        foreach($allUsers as $user){
            $user->setParty(null);
            $user->setBeginningCard(null);
            $user->setEndingCard(null);
            $manager->persist($user);
        }
        $manager->flush();

        $users = file_get_contents(__DIR__ . "/users.json");
        $users = json_decode($users,true);
        foreach ($users as $user){
            $newPlayer = new Player();
            $newPlayer->setIdFirebase($user['id_firebase']);
            $newPlayer->setPseudo($user['pseudo']);
            $manager->persist($newPlayer);
        }
        $manager->flush();
    }
}
