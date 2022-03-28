<?php
namespace App\DataFixtures;

use App\Entity\Build;
use App\Repository\UserRepository;
use App\Repository\WeaponRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public $weaponRerpository;

    function __construct(UserRepository $userRepository, WeaponRepository $weaponRerpository)
    {
      $this->userRepository = $userRepository;
      $this->weaponRerpository = $weaponRerpository;
    }

    public function load(ObjectManager $manager)
    {
      $weaponArr = [62,63,64,65,66,67,68,69,70,72,75];

        for ($i = 0; $i < 50; $i++) {
            $build = new Build();
            $build->setName('Fixture Build ')
              ->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.')
              ->setType(mt_rand(1, 5))
              ->setViews(mt_rand(0, 10000))
              ->setAuthor($this->userRepository->findOneBy(['id' => mt_rand(1, 5)]))
              ->setWeapons([
                '/api/weapons/' . $weaponArr[rand(0, count($weaponArr)-1)],
                '/api/weapons/' . $weaponArr[rand(0, count($weaponArr)-1)]
              ])
              ->setSelectedSkills([[],[]])
              ->setActivedSkills([[null, null, null], [null, null, null]]);

            
              for ($o = 1; $o <= mt_rand(1, 5); $o++) { 
                $build->addFavorites($this->userRepository->findOneBy(['id' => $o]));
              }


            $manager->persist($build);
        }

        $manager->flush();
    }
}