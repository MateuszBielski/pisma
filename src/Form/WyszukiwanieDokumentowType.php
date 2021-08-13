<?php

namespace App\Form;

use App\Service\WyszukiwanieDokumentow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WyszukiwanieDokumentowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $date = new \DateTime();
        $endYear = $date->format('Y');
        $builder
            ->add('dokument',TextType::class,['attr' => [
                // 'id'=>"input_find_pismo_wgOpisu",
                'size'=>"30",
                'placeholder' => 'opis...'
                ]])
            ->add('sprawa',TextType::class,['attr' => [
                // 'id'=>"input_find_pismo_wgSprawy",
                'size'=>"30",
                'placeholder' => 'opis...'
                ]])
            ->add('kontrahent',TextType::class,['attr' => [
                // 'id'=>"input_find_pismo_wgKontrahenta",
                'size'=>"30",
                'placeholder' => 'nazwa...'
                ]])
            ->add('czyDatyDoWyszukiwania',CheckboxType::class,[
                'label' => 'uwzględnij daty',
                'required' => false,
                'value' => false,
                ])
            ->add('poczatekData',DateType::class,[
                'widget' => 'choice',
                'years' => range(2001,$endYear+1),
                'format' => 'dd MM yyyy',])
            ->add('koniecData',DateType::class,[
                'widget' => 'choice',
                'years' => range(2001,$endYear+1),
                'format' => 'dd MM yyyy',])
            ->addEventSubscriber(new WyszDokEventSubsc)
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WyszukiwanieDokumentow::class,
        ]);
    }
}