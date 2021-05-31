<?php

namespace App\Form;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use App\Entity\RodzajDokumentu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PismoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nazwaPliku')
            ->add('oznaczenie')
            ->add('rodzaj',EntityType::class,[
                'class'=>RodzajDokumentu::class,
                'choice_label' => 'nazwa'
                ])
            ->add('kierunek',ChoiceType::class,[
                // 'mapped' => false,
                'multiple' => false,
                'expanded' => true,
                'choices'=>[
                    'przychodzące od:'=> 1,
                    'wychodzące do:' => 2
                ],
                'label' => false
                ])
            ->add('strona',EntityType::class,[
                'class'=>Kontrahent::class,
                'choice_label' => 'nazwa',
                'label' => false,
                // 'mapped' => false,
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pismo::class,
        ]);
    }
}
