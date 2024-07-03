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
    public function list(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clubId = $request->query->get('clubId');
        $filter = $request->query->get('filter');
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 10);

        $qb = $entityManager->createQueryBuilder();
        $qb->select('p')
            ->from(Player::class, 'p');

        if ($clubId){
            $qb->andWhere('p.team IS NOT NULL')
               ->leftJoin('p.team', 't')
               ->andWhere('t.id = :clubId')
               ->setParameter('clubId', $clubId);
        }

        if ($filter){
            $qb->andWhere('p.name LIKE :name')
                ->setParameter('filter', '%'.$filter.'%');
        }

        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $players = $qb->getQuery()->getResult();

        return $this->render('Players/list.html.twig', [
            'players' => $players,
        ]);
    }


    #[Route("/players/{id}", name: 'players_show', methods: ['GET'])]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $players = $entityManager->getRepository(Player::class)->find($id);
        if (!$players) {
            return $this->json(['message' => 'Player not found'], 404);
        }
        return $this->render('Players/show.html.twig', [
            'players' => $players,
        ]);
    }

    #[Route("(/players/redirect", name: 'players_redirect')]
    public function redirectTo(Request $request): Response
    {
        $playerID = (int) $request->request->get('playerID');
        return $this->redirectToRoute('players_show', ['id' => $playerID]);
    }


    #[Route("/players", name: 'players_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        $nombre = $data['nombre'];
        $salario = $data['salario'];
        $email = $data['email'];
        $clubId = $data['club_id'];

        $player = new Player();
        $player->setName($nombre);
        $player->setEmail($email);

        if (isset($clubId)){
            $club = $entityManager->getRepository(Team::class)->find($clubId);
            if(!$club) {
                return $this->json(['message' => 'Club not found'], 404);
            }

            if ($club->getPresupuestoActual() < $player->getSalario()) {
                return $this->json(['message' => 'Insufficient club budget'], 400);
            }
            $player->setTeam($club);
            $club->setPresupuestoActual($club->getPresupuestoActual() - $data['salario']);
            $club->addPlayer($player);

            $this->notifier->notify('You have been assigned to a new club.', $player->getEmail());
        }
        $player->setSalario($salario);

        $entityManager->persist($player);
        $entityManager->flush();

        return $this->redirectToRoute('players_list');
    }


    #[Route("/players/{id}/assign-club", name: 'players_assign', methods: ['PUT'])]
    public function assignToClub(Player $player, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $clubId = $data['club_id'];
        $salario = $data['salario'];

        $club = $entityManager->getRepository(Team::class)->find($clubId);
        if (!$club) {
            return $this->json(['message' => 'Club not found'], 404);
        }
        if ($player->getTeam() !==null) {
            return $this->json(['message' => 'Players already assigned to a club'], 400);
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

        return $this->json($player, 200);
    }


    #[Route("/players/{id}/remove-club", name: 'players_remove', methods: ['PUT'])]
    public function removeFromClub(Player $player, EntityManagerInterface $entityManager): JsonResponse
    {
        $club = $player->getTeam();
        if (!$club) {
            return $this->json(['message' => 'This player has not been assigned to a club'], 400);
        }

        $club->setPresupuestoActual($club->getPresupuestoActual() + $player->getSalario());

        $player->setTeam(null);
        $club->removePlayer($player);

        $entityManager->flush();

        $this->notifier->notify('You have been removed from your club.', $player->getEmail());

        return $this->json(['message' => 'Players removed from club successfully'], 200);
    }


    #[Route("/players/{id}/update", name: 'players_update', methods: ['PUT'])]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        if (!$player) {
            return $this->json(['message' => 'Players not found'], 404);
        }
        $data = json_decode($request->getContent(), true);

        $player->setName($data['nombre']);
        $player->setSalario($data['salario']);

        $entityManager->flush();

        return $this->json($player);
    }

    #[Route("/players/{id}/delete", name: 'players_delete', methods: ['DELETE'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        if (!$player) {
            return $this->json(['message' => 'Players not found'], 404);
        }

        $entityManager->remove($player);
        $entityManager->flush();

        return $this->json(['message' => 'Players successfully deleted']);
    }
}
