<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        
        return $this->render('@EasyAdmin/page/login.html.twig', [
            'last_username' => $lastUsername,
            'page_title' => $this->translator->trans('login_title', domain: 'admin'),
            'csrf_token_intention' => 'authenticate',
            'target_path' => $this->generateUrl('admin'),
            'username_label' => $this->translator->trans('username', domain: 'user'),
            'password_label' => $this->translator->trans('password', domain: 'user'),
            'sign_in_label' => $this->translator->trans('login_button', domain: 'admin'),
            'forgot_password_enabled' => true,
            'remember_me_enabled' => true,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
