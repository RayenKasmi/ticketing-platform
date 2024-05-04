<?php

namespace App\Controller;

use App\Form\ChangeAccountInfoType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    private $passwordHasher;

    #[Route( path :'/profile', name: 'app_profile')]
    public function index(): Response
    {
        if(!$this->getUser())
            return $this->redirectToRoute('app_home');

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    #[Route(path : '/profile/change-account-info', name: 'app_change_account_info')]
    public function changeAccountInfo(Request $request, EntityManagerInterface $entityManager): Response
    {
        if(!$this->getUser())
            return $this->redirectToRoute('app_home');

        $form = $this->createForm(ChangeAccountInfoType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getUser()->setFirstname($form->get('firstname')->getData());
            $this->getUser()->setLastname($form->get('lastname')->getData());
            $entityManager->persist($this->getUser());
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_profile');
        }


        return $this->render('profile/change_account_info.html.twig', [
            'controller_name' => 'ProfileController',
            'changeAccountInfoForm' => $form,
        ]);
    }

    #[Route(path : '/profile/change-password', name: 'app_change_password')]
    public function changePassword(Request $request,UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if(!$this->getUser())
            return $this->redirectToRoute('app_home');

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$userPasswordHasher->isPasswordValid($this->getUser(), $form->get('currentPassword')->getData()))
            {
                $form->get('currentPassword')->addError(new FormError('The current password is incorrect.'));
                return $this->render('profile/change_password.html.twig', [
                    'controller_name' => 'ProfileController',
                    'changePasswordForm' => $form,
                ]);
            }

            if($form->get('newPassword')->getData() != $form->get('confirmNewPassword')->getData())
            {
                $form->get('confirmNewPassword')->addError(new FormError('The new password and confirm password do not match.'));
                return $this->render('profile/change_password.html.twig', [
                    'controller_name' => 'ProfileController',
                    'changePasswordForm' => $form,
                ]);
            }

            if($form->get('currentPassword')->getData() == $form->get('newPassword')->getData())
            {
                $form->get('newPassword')->addError(new FormError('The new password cannot be the same as the current password.'));
                return $this->render('profile/change_password.html.twig', [
                    'controller_name' => 'ProfileController',
                    'changePasswordForm' => $form,
                ]);
            }

            // encode the plain password
            $this->getUser()->setPassword(
                $userPasswordHasher->hashPassword(
                    $this->getUser(),
                    $form->get('newPassword')->getData()
                )
            );
            $entityManager->persist($this->getUser());
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/change_password.html.twig', [
            'controller_name' => 'ProfileController',
            'changePasswordForm' => $form,
        ]);
    }
}
