<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('api/register', name: 'app_auth.register', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Create new User',
        content: new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(ref: new Model(type: User::class, groups: ['userRegister']))
        )
    )]
    #[OA\Tag(name: 'Auth')]
    #[Security(name: 'Bearer')]
    public function register(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {  
        $userEntry = $serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $validator->validate($userEntry);
        if ($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST , [], true);
        }

        $userEntry->setRoles(['ROLE_USER']);
        $userEntry->setPassword($userPasswordHasher->hashPassword($userEntry, $userEntry->getPassword()));
        $userEntry->setCreatedAt(new DateTimeImmutable());
        $userEntry->setStatus('on');

        $entityManager->persist($userEntry);
        $entityManager->flush();

        $context = SerializationContext::create();
        $jsonMonstredex = $serializer->serialize($userEntry, 'json', $context);

        return new JsonResponse($jsonMonstredex, Response::HTTP_CREATED, [], true);
    }
}
