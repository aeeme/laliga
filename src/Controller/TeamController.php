<?php

namespace App\Controller;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route("/api/teams", name: "api_teams_")]

class TeamController extends AbstractController
{
    #[Route("/teams", name: "teams_index")]
    #[Template('Teams/index.html.twig')]
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

    #[Route("/teams/create", name: "team_create", methods: ["GET", "POST"])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createFormBuilder()
            ->add('nombre', TextType::class)
            ->add('presupuestoActual', IntegerType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $team = new Team();
            $team->setNombre($data['nombre']);
            $team->setPresupuestoActual($data['presupuestoActual']);
            $entityManager->persist($team);
            $entityManager->flush();
            return $this->redirectToRoute('teams_list');
        }
        return $this->render('Teams/index.html.twig', [
            'form' => $form->createView(),
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
