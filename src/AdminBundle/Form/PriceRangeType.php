<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 */
class PriceRangeType extends AbstractType
{

    public function getParent()
    {
        return TextType::class;
    }

  /**
   * @return string
   */
    public function getName()
    {
        return 'adminbundle_pricerange';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}
