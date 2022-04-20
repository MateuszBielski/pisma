<?php

namespace App\Form;

use App\Entity\Folder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FolderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sciezkaMoja',TextType::class,['attr' => [
                // 'id'=>"input_find_pismo_wgSprawy",
                'size'=>"60",
                'placeholder' => '/'
                ]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Folder::class,
        ]);
    }
}
