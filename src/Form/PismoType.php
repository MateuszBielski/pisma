<?php

namespace App\Form;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use App\Entity\RodzajDokumentu;
use App\Entity\Sprawa;
use App\Repository\KontrahentRepository;
use App\Repository\SprawaRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PismoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $date = new \DateTime();
        $endYear = $date->format('Y');

        $builder
            // ->add('nazwaPliku',TextType::class,[/*'attr' => ['size'=>"40"]*/])
            ->add('opis',TextareaType::class,['attr' => ["rows"=>"2", "cols"=>"50"]])
            ->add('dataDokumentu', DateType::class, [
                'widget' => 'choice',
                'years' => range(2001,$endYear+1),
                'format' => 'dd MM yyyy',
            ])
            
            // ->add('oznaczenie')
            ->add('rodzaj',EntityType::class,[
                'class'=>RodzajDokumentu::class,
                'choice_label' => 'nazwa',
                'attr' => ['class' => 'dlaSelect2','style'=>"width: 100%",'adresAjax' => '/rodzaj/dokumentu/indexAjaxSelect2'],
                'placeholder' => '...',//
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
                'attr' => ['class' => 'dlaSelect2','style'=>"width: 100%", 'adresAjax' => '/kontrahent/indexAjaxSelect2'],
                'label' => false,
                'query_builder' => function (KontrahentRepository $kr) {
                    return $kr->createQueryBuilder('k')
                    ->orderBy('k.nazwa', 'ASC');
                },
                //błędy przekazuje na początek całego formularza
                'error_bubbling' => true,
                'placeholder' => '...',
                // 'mapped' => false,
                ])
            ->add('sprawy',EntityType::class,[
                'required'=> false,
                'multiple' => true,
                'class'=>Sprawa::class,
                'choice_label' => function(Sprawa $s){return $s->getNazwa();},
                'attr'=>['class' => 'dlaSelect2','style'=>"width:100%", 'adresAjax' => '/sprawa/indexAjaxSelect2'],
                'label'=>'dotyczy',
                'query_builder' => function (SprawaRepository $sr){
                    return $sr->createQueryBuilder('s');
                    // ->setMaxResults(8)
                    // ->orderBy('s.nazwa','ASC');
                },
                'placeholder' => '...',
            ])
                /*
                */
                
            
            ->addEventSubscriber(new PismoEventSubscriber)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pismo::class,
        ]);
    }
}
