<?php

namespace App\Controller;

use App\Entity\Coach;
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
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $coaches = $entityManager->getRepository(Coach::class)->findAll();
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
        $coach->setSalario($data['salario']);

        $entityManager->persist($coach);
        $entityManager->flush();

        return $this->json($coach, 201);
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