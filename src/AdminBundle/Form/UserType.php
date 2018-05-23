<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Form;

use AdminBundle\Service\RolesHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    /**
     * @var RolesHelper
     */
    private $rolesHelper;

    /**
     * @param $class
     * @param \AdminBundle\Service\RolesHelper $rolesHelper
     */
    public function __construct($class, RolesHelper $rolesHelper)
    {
        $this->rolesHelper = $rolesHelper;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('enabled')
        ->add('username')
        ->add('password')
        ->add('email')
        ->add('roles', ChoiceType::class, [
        'choices' => $this->rolesHelper->getRoles(),
        'expanded' => true,
        'multiple' => true,
        ]);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
          'data_class' => 'AppBundle\Entity\User',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'adminbundle_user';
    }
}
