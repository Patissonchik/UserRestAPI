<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    private UserService $userService;
    private SerializerInterface $serializer;

    public function __construct(UserService $userService, SerializerInterface $serializer)
    {
        $this->userService = $userService;
        $this->serializer = $serializer;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $userDTO = new UserDTO();
        $form = $this->createForm(UserType::class, $userDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userService->createUser($userDTO);
            return new JsonResponse(['status' => 'User created!'], 201);
        }

        return new JsonResponse(['status' => 'Invalid data'], 400);
    }

    #[Route('/user/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            return new JsonResponse(['status' => 'User not found!'], 404);
        }

        $userDTO = new UserDTO();
        $form = $this->createForm(UserType::class, $userDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->updateUser($user, $userDTO);
            return new JsonResponse(['status' => 'User updated!']);
        }

        return new JsonResponse(['status' => 'Invalid data'], 400);
    }

    #[Route('/user/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            return new JsonResponse(['status' => 'User not found!'], 404);
        }
        $this->userService->deleteUser($user);
        return new JsonResponse(['status' => 'User deleted!']);
    }

    #[Route('/user/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            return new JsonResponse(['status' => 'User not found!'], 404);
        }

        $data = $this->serializer->serialize($user, 'json');
        return new JsonResponse(json_decode($data));
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        
        if ($error) {
            return new JsonResponse(['error' => $error->getMessage()], 401);
        }
    
        return new JsonResponse(['status' => 'Login successful', 'username' => $lastUsername], 200);
    }
}
