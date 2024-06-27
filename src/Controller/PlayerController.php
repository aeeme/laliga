<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/players", name="api_players_")
 */
class PlayerController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
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

        return $this->json($players);
    }

    /**
     * @Route("/id", name="show", methods={"GET"})
     */
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $players = $entityManager->getRepository(Player::class)->find($id);
        return $this->json($players);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $player = new Player();
        $player->setName($data['nombre']);
        $player->setSalario($data['salario']);

        if (isset($data['club_id'])){
            $club = $entityManager->getRepository(Team::class)->find($data['club_id']);
            if(!$club) {
                return $this->json(['message' => 'Club not found'], 404);
            }

            if ($club->getPresupuestoActual() < $player->getSalario()) {
                return $this->json(['message' => 'Insufficient club budget'], 400);
            }
            $player->setTeam($club);
            $club->setPresupuestoActual($club->getPresupuestoActual() - $data['salario']);
            $club->addPlayer($player);
        }
        $player->setSalario($data['salario']);

        $entityManager->persist($player);
        $entityManager->flush();

        return $this->json($player, 201);
    }

    /**
     * @Route("/{id}/assign-to-club", name="assign_to_club", methods={"POST"}
     */
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

        return $this->json($player, 200);
    }

    /**
     * @Route("/{id}/remove-from-club", name="remove_from_club", methods={"POST"})
     */
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

        return $this->json(['message' => 'Player removed from club successfully'], 200);
    }

    /**
     * @Route("/{id}", name="update", methods="{PUT}")
     */
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        if (!$player) {
            return $this->json(['message' => 'Player not found'], 404);
        }
        $data = json_decode($request->getContent(), true);

        $player->setName($data['nombre']);
        $player->setSalario($data['salario']);

        $entityManager->flush();

        return $this->json($player);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $player = $entityManager->getRepository(Player::class)->find($id);
        if (!$player) {
            return $this->json(['message' => 'Player not found'], 404);
        }

        $entityManager->remove($player);
        $entityManager->flush();

        return $this->json(['message' => 'Player successfully deleted']);
    }
}
