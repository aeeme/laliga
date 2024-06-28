<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Entity\Team;
use App\Notification\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/coaches", name="api_coaches_")
 */
class CoachController extends AbstractController
{
    private Notifier  $notifier;

    public function __construct(Notifier $notifier)
    {
        $this->notifier = $notifier;
    }
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $clubId = $request->query->get('club_id');
        $filter = $request->query->get('filter');
        $page = $request->query->getInt('page', 1);
        $perpage = $request->query->getInt('perpage', 10);

        $qb = $entityManager->createQueryBuilder();
        $qb->select('c')
            ->from(Coach::class, 'c');

        if ($clubId) {
            $qb->andWhere('c.team IS NOT NULL')
                ->leftjoin('c.team', 't')
                ->andWhere('t.id = :clubId')
                ->setParameter('clubId', $clubId);
        }

        if ($filter) {
            $qb->andWhere('c.name LIKE :filter')
                ->setParameter('filter', '%' . $filter . '%');
        }

        $qb->setFirstResult(($page - 1) * $perpage)
            ->setMaxResults($perpage);

        $coaches = $qb->getQuery()->getResult();

        return $this->json($coaches);
    }

    /**
     * @Route("/", name="show", methods={"GET"})
     */
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);
        if (!$coach) {
            return $this->json(['message' => 'Coach not found'], 404);
        }
        return $this->json($coach);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $coach = new Coach();
        $coach->setNombre($data['nombre']);
        $coach->setEmail($data['email']);

        if (isset($data['club_id'])) {
            $club = $entityManager->getRepository(Team::class)->find($data['club_id']);
            if (!$club) {
                return $this->json(['message' => 'Club not found'], 404);
            }
            if ($club->getCoach() !== null) {
                return $this->json(['message' => 'The club already has a coach assigned'], 409);
            }
            if ($club->getPresupuestoActual() < $data['salario']) {
                return $this->json(['message' => 'Insufficient club budget.'], 409);
            }
            $coach->setTeam($club);
            $club->addCoach($coach);
            $club->setPresupuestoActual($club->getPresupuestoActual() - $data['salario']);

            $this->notifier->notify('You have been assigned to a new club.', $coach->getEmail());
        }
        $coach->setSalario($data['salario']);

        $entityManager->persist($coach);
        $entityManager->flush();

        return $this->json($coach, 201);
    }

    /**
     * @Route("/{id}/assign-to-club", name="assign_to_club", methods={"POST"})
     */
    public function assignToClub(Coach $coach, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $clubId = $data['club_id'];
        $salario = $data['salario'];

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

        return $this->json($coach, 200);
    }

    /**
     * @Route("/{id}/remove-from-club", name="remove_from_club", methods={"POST"})
     */
    public function removeFromClub(Coach $coach, EntityManagerInterface $entityManager): JsonResponse
    {
        $club = $coach->getTeam();
        if (!$club) {
            return $this->json(['message' => 'This coach has not been assigned to any club.'], 400);
        }

        $club->setPresupuestoActual($club->getPresupuestoActual() + $coach->getSalario());

        $coach->setTeam(null);
        $club->removeCoach($coach);

        $entityManager->flush();

        $this->notifier->notify('You have been removed from your club.', $coach->getEmail());

        return $this->json(['message' => 'Coach removed from club successfully'], 200);
    }

    /**
     * @Route("/{id}", name="update", methods={"PUT"})
     */
    public function update(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);
        if (!$coach) {
            return $this->json(['message' => 'Coach not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $coach->setNombre($data['nombre']);
        $coach->setSalario($data['salario']);

        $entityManager->flush();

        return $this->json($coach);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $coach = $entityManager->getRepository(Coach::class)->find($id);
        if (!$coach) {
            return $this->json(['message' => 'Coach not found'], 404);
        }
        $entityManager->remove($coach);
        $entityManager->flush();

        return $this->json(['message' => 'Coach deleted successfully']);
    }
    }