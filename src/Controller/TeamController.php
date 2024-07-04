<?php

namespace App\Controller;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
            'teams' => $teams,
        ]);
    }


    #[Route("/teams/{id}", name: "team_show", methods: ["GET"])]
    public function show(EntityManagerInterface $entityManager, Team $team): Response
    {
        return $this->render('Teams/show.html.twig', [
        'team' => $team,
        ]);
    }

    #[Route("/teams/redirect", name: "team_redirect", methods: ["POST"])]
    public function redirectTo(Request $request): Response
    {
        $team_id = (int) $request->request->get('team_id');
        return $this->redirectToRoute('team_show', ['id' => $team_id]);
    }

    #[Route("/teams/create", name: "team_create", methods: ["POST"])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $nombre = $request->request->get('nombre');
        $presupuesto = $request->request->get('presupuestoActual');

        $team = new Team();
        $team->setNombre($nombre);
        $team->setPresupuestoActual($presupuesto);

        $entityManager->persist($team);
        $entityManager->flush();

        return $this->render('Teams/create.html.twig', [
            'team' => $team,
        ]);
    }


    #[Route("/teams/{id}/update", name: "team_update", methods: ["PUT", "POST"])]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $team = $entityManager->getRepository(Team::class)->find($id);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], 404);
        }

        $newNombre = $request->request->get('update-team-nombre');
        if (isset($newNombre)) {
            $newNombre = $team->getNombre();
        }
        $newPresupuesto = (float) $request->request->get('update-team-presupuesto');

        $team->setNombre($newNombre);
        $team->setPresupuestoActual($newPresupuesto);

        $entityManager->flush();

        return $this->render('Teams/update.html.twig', [
            'team' => $team,
        ]);
    }


    #[Route("/teams/{id}/delete", name: "team_delete", methods: ["DELETE", "POST"])]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $team = $entityManager->getRepository(Team::class)->find($id);
        if (!$team) {
            return $this->json(['message' => 'Team not found'], 404);
        }
        $entityManager->remove($team);
        $entityManager->flush();
        return $this->render('Teams/delete.html.twig', [
            'team' => $team,
        ]);
    }
}
