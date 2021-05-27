<?php

namespace App\Form;

use App\Entity\Pismo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PismoLadowaniePdfType extends AbstractType
{
    //wiele plików, jeszcze nie sprawdzone:
    //https://stackoverflow.com/questions/61507463/symfony-4-upload-multiple-files-and-changing-max-size
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plik',FileType::class,['label'=>'Załaduj plik z dysku',
            'mapped' => false,
            'required' =>false,
            'constraints' => [
                new File([
                    'maxSize' => '10M',
                    
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf',
                        
                    ],
                    'mimeTypesMessage' => 'Proszę wybrać prawidłowy dokument *.pdf',
                ])
            ]])
        ;
    }
    /*
    FileType::class, [
        'label' => 'Brochure (PDF file)',
        // unmapped means that this field is not associated to any entity property
        'mapped' => false,
        // make it optional so you don't have to re-upload the PDF file
        // every time you edit the Product details
        'required' => false,
        // unmapped fields can't define their validation using annotations
        // in the associated entity, so you can use the PHP constraint classes
        'constraints' => [
            new File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'application/pdf',
                    'application/x-pdf',
                ],
                'mimeTypesMessage' => 'Please upload a valid PDF document',
            ])
        ],
    ])
    */

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pismo::class,
        ]);
    }
}
