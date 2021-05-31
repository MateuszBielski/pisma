<?php

namespace App\Form;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use App\Entity\RodzajDokumentu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PismoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $date = new \DateTime();
        $endYear = $date->format('Y');
        $builder
            ->add('nazwaPliku')
            ->add('dataDokumentu', DateType::class, [
                'widget' => 'choice',
                'years' => range(2001,$endYear+1),
                'format' => 'dd MM yyyy',
            ])
            
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
