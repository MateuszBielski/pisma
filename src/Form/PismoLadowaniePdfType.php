<?php

namespace App\Form;

use App\Entity\Pismo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;

class PismoLadowaniePdfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('plik',FileType::class,['label'=>'Załaduj plik z dysku',
        'multiple' => true,
        'mapped' => false,
        'required' =>false,
        'constraints' => [new All ([
            new File([
                'maxSize' => '10M',
                
                'mimeTypes' => [
                    'application/pdf',
                    'application/x-pdf',
                    
                ],
                'mimeTypesMessage' => 'Proszę wybrać prawidłowy dokument *.pdf',
            ])
            ])
            
        ]
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
