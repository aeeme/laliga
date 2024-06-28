<?php

namespace App\DataFixtures;

use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $celta = new Team();
        $celta->setNombre('Real Club Celta');
        $celta->setPresupuestoActual(100);
        $manager->persist($celta);

        $oviedo = new Team();
        $oviedo->setNombre('Real Oviedo');
        $oviedo->setPresupuestoActual(85);
        $manager->persist($oviedo);

        //Jugadores
        $aspas = new Player();
        $aspas->setName('Iago Aspas');
        $aspas->setSalario(10);
        $aspas->setEmail('aspas@gmail.com');
        $aspas->setTeam($celta);
        $manager->persist($aspas);

        $cazorla = new Player();
        $cazorla->setName('Santiago Cazorla');
        $cazorla->setSalario(4);
        $cazorla->setEmail('cazorla@hotmail.es');
        $cazorla->setTeam($oviedo);
        $manager->persist($cazorla);

        $ontiveros = new Player();
        $ontiveros->setName('Javier Ontiveros');
        $ontiveros->setSalario(0);
        $ontiveros->setEmail('ontiveros@yahoo.com');
        $manager->persist($ontiveros);

        //Entrenadores
        $giraldez = new Coach();
        $giraldez->setNombre('Claudio Giraldez');
        $giraldez->setSalario(2);
        $giraldez->setEmail('giraldez@hotmail.com');
        $giraldez->setTeam($celta);
        $manager->persist($giraldez);

        $pacheta = new Coach();
        $pacheta->setNombre('Pacheta');
        $pacheta->setSalario(1);
        $pacheta->setEmail('pacheta@hotmail.com');
        $pacheta->setTeam($oviedo);
        $manager->persist($pacheta);

        $xavi = new Coach();
        $xavi->setNombre('Xavi Hernandez');
        $xavi->setSalario(0);
        $xavi->setEmail('xavi@hotmail.com');
        $manager->persist($xavi);

        $manager->flush();
    }
}
