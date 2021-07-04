<?php

namespace App\Form;

use App\Entity\Pismo;
use App\Entity\Sprawa;
use App\Entity\WyrazWciagu;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SprawaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('opis',TextareaType::class)
            ->add('dokumenty',EntityType::class,[
                'class'=>Pismo::class,
                'multiple'=> true,
                'choice_label' => 'nazwaPliku',
                'required' => false
            ])
            // ->add('dokumenty')
            ->addEventSubscriber(new SprawaEventSubscriber)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sprawa::class,
        ]);
    }
}
