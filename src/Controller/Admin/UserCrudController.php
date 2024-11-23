<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use function Symfony\Component\Translation\t;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

use App\Enum\Gender;
use App\Enum\Roles;
class UserCrudController extends AbstractCrudController
{
    public function __construct(private TranslatorInterface $translator)
    {
    }
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular($this->translator->trans('user', domain: 'user'))
            ->setEntityLabelInPlural($this->translator->trans('users', domain: 'user'))
            ->setSearchFields(['username', 'email'])
            ->setDefaultSort(['createdAt' => 'DESC'])
        ;
    }


    public function configureFields(string $pageName, ): iterable
    {
        yield IdField::new('id');
        yield TextField::new('username', $this->translator->trans('username', domain: 'user'));
        yield ChoiceField::new('gender', t('gender', domain: 'user'))
            ->setTranslatableChoices(Gender::cases())
            ->renderExpanded()
            ->setSortable(false);
        yield ChoiceField::new('roles', $this->translator->trans('roles', domain: 'roles'))
            ->setTranslatableChoices(Roles::cases())
            ->allowMultipleChoices()
            ->renderAsBadges()
            ->setSortable(false);
        yield EmailField::new('email');
        TextEditorField::new('about', $this->translator->trans('about', domain: 'user'));

    }

}
