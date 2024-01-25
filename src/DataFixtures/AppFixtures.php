<?php

namespace App\DataFixtures;

use App\Entity\Card;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use DateTimeImmutable;

class AppFixtures extends Fixture
{
    /**
     * Faker
     * @var Generator $faker
     */
    private Generator $faker;

    /**
     * User Password Hash
     * @var UserPasswordHasherInterface $userPasswordHasher
     */
    private UserPasswordHasherInterface $userPasswordHasher;

    /**
     * Constructor Fixtures
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->faker = Factory::create("fr_FR");
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Generating Admin user

        $adminUser = new User();
        $adminUser->setUsername('admin');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, 'password'));
        $adminUser->setCreatedAt(new DateTimeImmutable());
        $adminUser->setStatus('on');
        $manager->persist($adminUser);


        // Generating Cards

        $colors = ['red', 'blue', 'green', 'yellow'];
        $specialTypes = ['skip', 'reverse', 'draw_two'];
        $specialTypesWild = ['draw_four', 'change_color'];

        foreach ($colors as $color) {
            $this->createCard($manager, $color, 0, 'number', false, false, null); // Adding the 0 card once

            $iterations = array_fill(1, 9, 2); // 2 iterations for numbers 1 to 9

            foreach ($iterations as $number => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $this->createCard($manager, $color, $number, 'number', false, false, null);
                };
            };

            foreach ($specialTypes as $type) {
                for ($i = 0; $i < 2; $i++) {
                    $this->createCard($manager, $color, null, $type, true, false, null);
                };
            };
        };

        foreach ($specialTypesWild as $type) {
            for ($i = 0; $i < 4; $i++) { 
                $this->createCard($manager, null, null, $type, true, true, null);
            };
        };

        $manager->flush();
    }

    private function createCard(ObjectManager $manager, $color, $number, $type, $isSpecial, $isWild, $image)
    {
        $card = new Card();
        $card->setColor($color);
        $card->setNumber($number);
        $card->setType($type);
        $card->setIsSpecial($isSpecial);
        $card->setIsWild($isWild);
        $card->setImage($image);

        $manager->persist($card);
    }
}
