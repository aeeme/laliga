<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Team;
use App\Notification\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route("/api/coaches", name: "api_coaches")]
class CoachController extends AbstractController
{

    #[Route("/coaches", name: "coaches_index")]
    public function index(): Response
    {
        return $this->render('Coaches/index.html.twig', [
            'controller_name' => 'CoachController',
        ]);
    }
    private Notifier  $notifier;

    public function __construct(Notifier $notifier)
    {
        $this->notifier = $notifier;
    }

    #[Route("/coaches/list", name: "coaches_list", methods: ["GET"])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $coaches = $entityManager->getRepository(Coach::class)->findAll();

        return $this->render('Coaches/list.html.twig', [
            'coaches' => $coaches,
        ]);
    }


    #[Route("/coaches/{id}", name: "coach_show", methods: ["GET"])]
    public function show(Coach $coach): Response
    {
        return $this->render('Coaches/show.html.twig', [
            'coach' => $coach,
        ]);
    }

    #[Route("(/coaches/redirect", name: 'coach_redirect', methods: ["POST"])]
    public function redirectTo(Request $request): Response
    {
        $coach_id = (int) $request->request->get('coachID');
        return $this->redirectToRoute('coach_show', ['id' => $coach_id]);
    }

    #[Route("/coaches/create", name: "coach_create", methods: ["GET", "POST"])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $nombre = $request->request->get('nombre');
        $email = $request->request->get('email');
        $salario = $request->request->get('salario');
        $club_id = $request->request->get('club_id');

        $coach = new Coach();
        $coach->setNombre($nombre);
        $coach->setEmail($email);

        if ($club_id) {
            $club = $entityManager->getRepository(Team::class)->find($club_id);
            if (!$club) {
                return $this->json(['message' => 'Club not found'], 404);
            }
            $oldCoach = $club->getCoach();
            if (isset($oldCoach)) {
                return $this->json(['message' => 'The club already has a coach assigned'], 409);
            }
            if ($club->getPresupuestoActual() < $salario) {
                return $this->json(['message' => 'Insufficient club budget.'], 409);
            }
            $coach->setTeam($club);
            $club->setCoach($coach);
            $club->setPresupuestoActual($club->getPresupuestoActual() - $salario);

            $this->notifier->notify('You have been assigned to a new club.', $coach->getEmail());
        } else {
            $salario = 0;
        }
        $coach->setSalario($salario);

        $entityManager->persist($coach);
        $entityManager->flush();

        return $this->render('Coaches/create.html.twig', [
            'coach' => $coach,
        ]);
    }


    #[Route("/coaches/{id}/assign-club", name: "coach_assign", methods: ["PUT", "POST"])]
    public function assignToClub(Coach $coach, Request $request, EntityManagerInterface $entityManager): Response
    {
        $clubId = $request->request->get('club_id');
        $salario = $request->request->get('salario');

        $club = $entityManager->getRepository(Team::class)->find($clubId);
        if (!$club) {
            return $this->json(['message' => 'Club not found'], 404);
        }
        if ($club->getCoach() !== null) {
            return $this->json(['message' => 'The club specified already has a coach assigned.'], 409);
        }
        if ($coach->getTeam() !== null) {
            return $this->json(['message' => 'The coach is already assigned to another club.'], 409);
        }
        if ($club->getPresupuestoActual() < $salario) {
            return $this->json(['message' => 'Insufficient club budget.'], 400);
        }

        $coach->setTeam($club);
        $coach->setSalario($salario);
        $club->setCoach($coach);
        $club->setPresupuestoActual($club->getPresupuestoActual() - $salario);

        $entityManager->flush();

        $this->notifier->notify('You have been assigned to a new club.', $coach->getEmail());

        return $this->render('Coaches/assign.html.twig', [
            'coach' => $coach,
        ]);
    }


    #[Route("/coaches/{id}/remove-club", name: "coach_remove", methods: ["PUT", "POST"])]
    public function removeFromClub(Coach $coach, EntityManagerInterface $entityManager): Response
    {
        $club = $coach->getTeam();
        if (!$club) {
            return $this->json(['message' => 'This coach has not been assigned to any club.'], 400);
        }

        $club->setPresupuestoActual($club->getPresupuestoActual() + $coach->getSalario());
        $coach->setSalario(0);

        $coach->setTeam(null);
        $club->setCoach(null);

        $entityManager->flush();

        $this->notifier->notify('You have been removed from your club.', $coach->getEmail());

        return $this->render('Coaches/remove.html.twig', [
            'coach' => $coach,
        ]);
    }


    #[Route("/coaches/{id}/update", name: "coach_update", methods: ["PUT", "POST"])]
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);
        if (!$coach) {
            return $this->json(['message' => 'Coach not found'], 404);
        }

        $newNombre = $request->request->get('nombre');
        $newEmail = $request->request->get('email');
        $newSalario = $request->request->get('salario');

        if (!$newNombre) {
            $newNombre = $coach->getNombre();
        }
        if (!$newEmail) {
            $newEmail = $coach->getEmail();
        }
        if (!$newSalario) {
            $newSalario = $coach->getSalario();
        }

        $club = $coach->getTeam();

        if (!$club) {
            $newSalario = 0;
        } else {
            $club_id = $club->getId();
            $club = $entityManager->getRepository(Team::class)->find($club_id);
            $club->setPresupuestoActual($club->getPresupuestoActual() + $coach->getSalario());
            $club->setPresupuestoActual($club->getPresupuestoActual() - $newSalario);
        }
        $coach->setNombre($newNombre);
        $coach->setEmail($newEmail);
        $coach->setSalario($newSalario);

        $entityManager->flush();

        return $this->render('Coaches/update.html.twig', [
            'coach' => $coach,
        ]);
    }

    #[Route("/coaches/{id}/delete", name: "coach_delete", methods: ["DELETE", "POST"])]
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);
        if (!$coach) {
            return $this->json(['message' => 'Coach not found'], 404);
        }
        $club = $coach->getTeam();
        if ($club) {
            $club->setPresupuestoActual($club->getPresupuestoActual() + $coach->getSalario());
        }
        $entityManager->remove($coach);
        $entityManager->flush();

        return $this->render('Coaches/delete.html.twig', [
            'coach' => $coach,
        ]);
    }
    }