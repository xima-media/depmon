<?php

namespace Xima\DepmonBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xima\DepmonBundle\Entity\Form\Filter;
use Xima\DepmonBundle\Entity\Project;
use Xima\DepmonBundle\Util\VersionHelper;


class FilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projects', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label' => 'Project',
            ])
            ->add('dependencySearch', TextType::class, [
                'required' => false,
                'label' => 'Dependencies',
                'attr' => [
                    'class' => 'search text-search',
                    'placeholder' => 'Search dependencies ...'
                ],

            ])
            ->add('showIndirectlyDependencies', CheckboxType::class, [
                'required' => false,
                'label' => 'Show indirectly dependencies',
                'attr' => [
                    'class' => 'dark'
                ],
            ])
            ->add('states', ChoiceType::class, [
                'choices'  => [
                    'Up to date' => VersionHelper::STATE_UP_TO_DATE,
                    'Pinned out of date' => VersionHelper::STATE_PINNED_OUT_OF_DATE,
                    'Out of date' => VersionHelper::STATE_OUT_OF_DATE,
                    'Insecure' => VersionHelper::STATE_INSECURE,
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label' => 'States',
            ])
            ->add('reset', ResetType::class)
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Filter::class);
    }

}