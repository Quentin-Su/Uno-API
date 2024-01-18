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
        $adminUser = new User();
        $adminUser->setUsername('admin');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setPassword($this->userPasswordHasher->hashPassword($adminUser, 'password'));
        $adminUser->setCreatedAt(new DateTimeImmutable());
        $adminUser->setStatus('on');
        $manager->persist($adminUser);

        for ($i = 0; $i < 10; $i++) { 
            $CardEntry = new Card();
            $CardEntry->setColor('red');
            $CardEntry->setNumber($this->faker->randomDigitNotNull());
            $CardEntry->setType('number');
            $CardEntry->setIsSpecial(false);
            $CardEntry->setIsWild(false);
            $CardEntry->setImage(null);
            $CardEntries[] = $CardEntry;
            $manager-> persist($CardEntry);
        }

        $manager->flush();
    }
}
