<?php

namespace App\Controller\Admin;

use App\Enum\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use App\Entity\SpaceShip;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\Response;
use App\Service\PhinxService;
use function Symfony\Component\Translation\t;





class SpaceShipCrudController extends AbstractCrudController
{
    public function __construct(private TranslatorInterface $translator)
    {

    }
    public static function getEntityFqcn(): string
    {
        return SpaceShip::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', $this->translator->trans('spaceships', domain: 'spaceship'))
            ->setEntityLabelInSingular($this->translator->trans('spaceship', domain: 'spaceship'))
            ->setEntityLabelInPlural($this->translator->trans('spaceships', domain: 'spaceship'))
            ->setSearchFields(['name'])
            ->setDefaultSort(['createdAt' => 'DESC'])

        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('user', $this->translator->trans('author', domain: 'spaceship')))
            ->add(EntityFilter::new('likes', $this->translator->trans('likes', domain: 'spaceship')))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', $this->translator->trans('name', domain: 'spaceship'));
        yield AssociationField::new('user', $this->translator->trans('author', domain: 'spaceship'));
        yield TextField::new('categoryName', t('category', domain: 'spaceship'));

        yield TextareaField::new('description', $this->translator->trans('description', domain: 'spaceship'))
            ->hideOnIndex();
        $createdAt = DateTimeField::new('createdAt', $this->translator->trans('created_at', domain: 'spaceship'))->setFormTypeOptions([
            'years' => range(date('Y '), date('Y') + 5),
            'widget' => 'single_text',
        ]);
        if (Crud::PAGE_EDIT === $pageName) {
            yield $createdAt->setFormTypeOption('disabled', true);

        } else {
            yield $createdAt;
        }
    }

    /**
     * Seeding data in the database
     *
     * @param PhinxService $phinxService
     *
     * @return Response
     */
    public function seedData(PhinxService $phinxService): Response
    {
        $result = $phinxService->seed();
        if ($result['status'] === 0) {
            $this->addFlash('success', $this->translator->trans('data_seeded', domain: 'admin'));
        } else if (($result['status'] === 1)) {
            $this->addFlash('danger', $this->translator->trans('data_not_seeded', domain: 'admin'));
        }

        return new Response(null, 200);
    }




    /**
     * @param Actions $actions
     * @return Actions
     */
    public function configureActions(Actions $actions): Actions
    {
        $seed_data = Action::new('seed_data', $this->translator->trans('seed_data', domain: 'admin'), 'fas fa-database')
            ->addCssClass('btn btn-warning')
            ->linkToCrudAction('seedData')
            ->createAsGlobalAction();
        return $actions
            ->add(Crud::PAGE_INDEX, $seed_data)
            ->update(Crud::PAGE_INDEX, Action::NEW , function (Action $action): Action {
                return $action->setIcon('fas fa-plus')->setLabel($this->translator->trans('add_spaceship', domain: 'spaceship'));
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action): Action {
                return $action
                    ->setIcon('fas fa-trash')
                ;
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action): Action {
                return $action
                    ->setIcon('fas fa-edit')
                ;
            })
        ;
    }
}
