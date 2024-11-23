<?php

namespace App\Controller\Admin;

use App\Entity\SpaceShip;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Repository\SpaceShipRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;



class DashboardController extends AbstractDashboardController
{
    private string $avatarBasePath = '';

    public function __construct(
        private TranslatorInterface $translator,
        #[Autowire('%avatars_directory%')] string $avatarDirName,
        private SpaceshipRepository $spaceshipRepository
    ) {
        $this->avatarBasePath = $avatarDirName;
    }
    #[Route('/admin/{_locale}', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $spaceshipCrudUrl = $adminUrlGenerator->setController(SpaceShipCrudController::class)->generateUrl();
        $usersCrudUrl = $adminUrlGenerator->setController(UserCrudController::class)->generateUrl();
        $limit = 5;
        $latestShips = $this->spaceshipRepository->findLatest($limit);
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'DashboardController',
            'spaceship_crud_url' => $spaceshipCrudUrl,
            'users_crud_url' => $usersCrudUrl,
            'latest_ships' => $latestShips
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->translator->trans('admin_dashboard', domain: 'admin'))
            ->setTranslationDomain('admin')
            ->setFaviconPath('favicon.ico')
            ->setLocales(
                [
                    'ru' => '🇷🇺 Русский',
                    'en' => '🇬🇧 English',
                ]
            )
            ->renderContentMaximized();
    }




    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard($this->translator->trans('admin_dashboard', domain: 'admin'), 'fa fa-dashboard');
        yield MenuItem::linkToCrud($this->translator->trans('spaceships', domain: 'spaceship'), 'fas fa-rocket', SpaceShip::class);
        yield MenuItem::linkToCrud($this->translator->trans('users', domain: 'admin'), 'fas fa-users', User::class);
        yield MenuItem::linktoUrl($this->translator->trans('back_to_site', domain: 'admin'), 'fas fa-home', '/', );
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setDefaultSort([
                'id' => 'ASC',
            ])
            ->showEntityActionsInlined()
            ->setPaginatorPageSize(8)
            ->setPaginatorRangeSize(2)
            ->hideNullValues();
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $avatar = $user->getAvatar();
        $userMenu = parent::configureUserMenu($user);
        if ($avatar) {
            $userMenu->setAvatarUrl($avatar)
                ->setMenuItems([
                    MenuItem::linkToRoute($this->translator->trans('profile', domain: 'admin'), 'fa fa-user', 'profile_admin')->setPermission('ROLE_ADMIN'),
                    MenuItem::linkToLogout($this->translator->trans('logout', domain: 'admin'), 'fa fa-sign-out'),
                ]);
        }

        return $userMenu;
    }
}
