<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryDeviseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Liste de quelques pays européens
        $europeCountries = [
            'France' => 'France',
            'Allemagne' => 'Germany',
            'Espagne' => 'Spain',
            'Italie' => 'Italy',
            'Belgique' => 'Belgium',
            'Suisse' => 'Switzerland',
            'Pays-Bas' => 'Netherlands',
        ];

        $builder
            ->add('country', ChoiceType::class, [
                'choices' => $europeCountries,
                'label' => 'Pays',
                'placeholder' => 'Sélectionnez votre pays',
                'required' => true,
            ])
            ->add('devise', ChoiceType::class, [
                'choices' => [
                    'Euro (€)' => 'EUR',
                    'Dollar ($)' => 'USD'
                ],
                'label' => 'Devise',
                'placeholder' => 'Sélectionnez votre devise',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
