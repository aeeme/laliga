<?php

namespace App\Controller;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/teams", name="api_teams_")
 */

class TeamController extends AbstractController
{
    /**
     * [Route('/', name: 'index', methods: ['GET'])]
     */
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $teams = $entityManager->getRepository(Team::class)->findAll();
        return $this->json($teams);
    }

    /**
     * @Route("/{id}, name="show", methods={"GET"})
     */
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $team = $entityManager->getRepository(Team::class)->find($id);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], 404);
        }
        return $this->json($team);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $team = new Team();
        $team->setNombre($data['nombre']);
        $team->setPresupuestoActual($data['presupuestoActual']);

        $entityManager->persist($team);
        $entityManager->flush();

        return $this->json($team, 201);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $team = $entityManager->getRepository(Team::class)->find($id);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $team->setNombre($data['nombre']);
        $team->setPresupuestoActual($data['presupuestoActual']);

        $entityManager->flush();

        return $this->json($team);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $team = $entityManager->getRepository(Team::class)->find($id);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], 404);
        }
        $entityManager->remove($team);
        $entityManager->flush();
        return $this->json(['message' => 'Team successfully deleted']);
    }
}
