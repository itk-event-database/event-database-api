<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

class YamlType extends AbstractType implements DataTransformerInterface
{
    public function getParent()
    {
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
        'schema' => null,
        ]);
    }

    public function transform($value)
    {
        return $value ? Yaml::dump($value, PHP_INT_MAX, 2) : '';
    }

    public function reverseTransform($value)
    {
        // @TODO: Use schema to validate YAML.
        try {
            return Yaml::parse($value);
        } catch (\Exception $ex) {
            throw new TransformationFailedException($ex->getMessage());
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'adminbundle_feed_configuration';
    }
}
