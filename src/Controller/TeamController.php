<?php

namespace App\Controller;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/teams", name: "api_teams_")]

class TeamController extends AbstractController
{
    #[Route("/teams", name: "teams_index")]
    public function index(): Response
    {
        return $this->render('Teams/index.html.twig', [
            'controller_name' => 'TeamController',
        ]);
    }

    #[Route("/teams/list", name: "teams_list", methods: ["GET"])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $teams = $entityManager->getRepository(Team::class)->findAll();
        return $this->render('Teams/list.html.twig', [
            'equipos' => $teams,
        ]);
    }


    #[Route("/teams/{id}", name: "teams_show", methods: ["GET"])]
    public function show(EntityManagerInterface $entityManager, string $id): Response
    {
        $team = $entityManager->getRepository(Team::class)->find($id);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], 404);
        }
        return $this->render('Teams/show.html.twig', [
            'team' => $team,
        ]);
    }

    #[Route("/teams/redirect", name: "teams_redirect", methods: ["GET"])]
    public function redirectTo(Request $request): Response
    {
        $team_id = (int) $request->query->get('team_id');
        return $this->redirectToRoute('teams_show', ['id' => $team_id]);
    }

    #[Route("/teams/create", name: "teams_create", methods: ["POST"])]
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


    #[Route("/teams/{id}/update", name: "teams_update", methods: ["PUT"])]
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


    #[Route("/teams/{id}/delete", name: "teams_delete", methods: ["DELETE"])]
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
