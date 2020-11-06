<?php

namespace App\DataFixtures;

use App\Entity\Card;
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
        }

        $users = file_get_contents(__DIR__ . "/users.json");
        $users = json_decode($users,true);
        foreach ($users as $user){
            $user = new Player();
            $user->setIdFirebase($user['id_firebase']);
            $user->setPseudo($user['pseudo']);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
