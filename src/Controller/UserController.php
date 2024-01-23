<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('api/user/{id}', name: 'app_user.getById', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the user profile who owns the Id',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['getUserProfile']))
        )
    )]
    #[OA\Tag(name: 'User')]
    #[Security(name: 'Bearer')]
    public function getUserProfile(int $id, UserRepository $repository, SerializerInterface $serializer): JsonResponse
    {       
        $user = $repository->find($id);

        $context = SerializationContext::create()->setGroups(['getUserProfile']);
        $jsonUser= $serializer->serialize($user, 'json', $context);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('api/user/{id}', name: 'app_user.updateUser', methods: ['PUT', 'PATCH'])]
    #[OA\RequestBody(
        description: 'Update your credentials',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: new Model(type: User::class, groups: ['userRegister']))
        )
    )]
    #[OA\Tag(name: 'User')]
    #[Security(name: 'Bearer')]
    public function updateUser(int $id, #[CurrentUser] User $currentUser, Request $request, UserRepository $repository, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($id !== $currentUser->getId()) {
            return new JsonResponse($serializer->serialize('Unauthorized ! You can only update your account.', 'json'), JsonResponse::HTTP_UNAUTHORIZED , [], true);
        };

        $user = $repository->find($id);
        $updateUser = $serializer->deserialize($request->getContent(), User::class, 'json');
        
        $errors = $validator->validate($updateUser);
        if ($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST , [], true);
        }

        $user->setUsername($updateUser->getUsername() ?? $user->getUsername());
        $user->setUpdateAt(new DateTimeImmutable());
        
        if ($user->getPassword() !== $updateUser->getPassword()) {
            $user->setPassword($userPasswordHasher->hashPassword($updateUser, $updateUser->getPassword()));
        };

        $entityManager->persist($user);
        $entityManager->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, []);
    }
}
