<?php

namespace App\Controller;

use App\Entity\Player;
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
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $players = $entityManager->getRepository(Player::class)->findAll();
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

        $entityManager->persist($player);
        $entityManager->flush();

        return $this->json($player, 201);
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
