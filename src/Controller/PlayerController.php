<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Team;
use App\Notification\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route("/api/players", name: 'api_players_')]
class PlayerController extends AbstractController
{
    #[Route("/players", name: 'players_index')]
    public function index(): Response
    {
        return $this->render('Players/index.html.twig', [
            'controller_name' => 'PlayerController',
        ]);
    }
    private Notifier $notifier;

    public function __construct(Notifier $notifier)
    {
        $this->notifier = $notifier;
    }

    #[Route("/players/list", name: 'players_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $players = $entityManager->getRepository(Player::class)->findAll();

        return $this->render('Players/list.html.twig', [
            'players' => $players,
        ]);
    }


    #[Route("/players/{id}", name: 'player_show', methods: ['GET'])]
    public function show(Player $player): Response
    {
        return $this->render('Players/show.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route("(/players/redirect", name: 'players_redirect', methods: ["POST"])]
    public function redirectTo(Request $request): Response
    {
        $playerID = (int) $request->request->get('playerID');
        return $this->redirectToRoute('player_show', ['id' => $playerID]);
    }


    #[Route("/players/create", name: 'player_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $nombre = $request->request->get('nombre');
        $email = $request->request->get('email');
        $salario = $request->request->get('salario');
        $clubId = $request->request->get('club_id');

        $player = new Player();
        $player->setName($nombre);
        $player->setEmail($email);

        if ($clubId){
            $club = $entityManager->getRepository(Team::class)->find($clubId);
            if(!$club) {
                return $this->json(['message' => 'Club not found'], 404);
            }
            if ($club->getPresupuestoActual() < $salario) {
                return $this->json(['message' => 'Insufficient club budget'], 409);
            }
            $player->setTeam($club);
            $club->setPresupuestoActual($club->getPresupuestoActual() - $salario);
            $club->addPlayer($player);

            $this->notifier->notify('You have been assigned to a new club.', $player->getEmail());
        } else {
            $salario = 0;
        }
        $player->setSalario($salario);

        $entityManager->persist($player);
        $entityManager->flush();

        return $this->render('Players/create.html.twig', [
            'player' => $player,
        ]);
    }


    #[Route("/players/{id}/assign-club", name: 'player_assign', methods: ['PUT', 'POST'])]
    public function assignToClub(Player $player, Request $request, EntityManagerInterface $entityManager): Response
    {
        $clubId = $request->request->get('club_id');
        $salario = $request->request->get('salario');

        $club = $entityManager->getRepository(Team::class)->find($clubId);
        if (!$club) {
            return $this->json(['message' => 'Club not found'], 404);
        }
        if ($player->getTeam() !==null) {
            return $this->json(['message' => 'Player already assigned to a club'], 400);
        }
        if ($club->getPresupuestoActual() < $salario) {
            return $this->json(['message' => 'Insufficient club budget'], 400);
        }
        $player->setTeam($club);
        $player->setSalario($salario);
        $club->setPresupuestoActual($club->getPresupuestoActual() - $salario);
        $club->addPlayer($player);

        $entityManager->flush();

        $this->notifier->notify('You have been asigned to a new club.', $player->getEmail());

        return $this->render('Players/assign.html.twig', [
            'player' => $player,
        ]);
    }


    #[Route("/players/{id}/remove-club", name: 'player_remove', methods: ['POST', 'PUT'])]
    public function removeFromClub(Player $player, EntityManagerInterface $entityManager): Response
    {
        $club = $player->getTeam();
        if (!$club) {
            return $this->json(['message' => 'This player has not been assigned to a club'], 400);
        }

        $club->setPresupuestoActual($club->getPresupuestoActual() + $player->getSalario());
        $player->setSalario(0);

        $player->setTeam(null);
        $club->removePlayer($player);

        $entityManager->flush();

        $this->notifier->notify('You have been removed from your club.', $player->getEmail());

        return $this->render('Players/remove.html.twig', [
            'player' => $player,
        ]);
    }


    #[Route("/players/{id}/update", name: 'player_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        if (!$player) {
            return $this->json(['message' => 'Players not found'], 404);
        }
        $newNombre = $request->request->get('nombre');
        $newEmail = $request->request->get('email');
        $newSalario = $request->request->get('salario');

        if (!$newNombre) {
            $newNombre = $player->getName();
        }
        if (!$newEmail) {
            $newEmail = $player->getEmail();
        }
        if (!$newSalario) {
            $newSalario = $player->getSalario();
        }

        $club = $player->getTeam();
        if (!$club) {
            $newSalario = 0;
        } else {
            $club->setPresupuestoActual($club->getPresupuestoActual() + $player->getSalario());
            $club->setPresupuestoActual($club->getPresupuestoActual() - $newSalario);
        }

        $player->setName($newNombre);
        $player->setEmail($newEmail);
        $player->setSalario($newSalario);

        $entityManager->flush();

        return $this->render('Players/update.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route("/players/{id}/delete", name: 'player_delete', methods: ['POST', 'DELETE'])]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        if (!$player) {
            return $this->json(['message' => 'Player not found'], 404);
        }
        $club = $player->getTeam();
        if ($club) {
            $club->setPresupuestoActual($club->getPresupuestoActual() + $player->getSalario());
        }
        $entityManager->remove($player);
        $entityManager->flush();

        return $this->render('Players/delete.html.twig', [
            'player' => $player,
        ]);
    }
}
