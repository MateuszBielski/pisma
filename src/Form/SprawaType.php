<?php

namespace App\Form;

use App\Entity\Sprawa;
use App\Entity\WyrazWciagu;
use Doctrine\Common\Collections\ArrayCollection;
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
            // ->add('dokumenty')
        ;
        $builder->get('opis')
        ->addModelTransformer(new CallbackTransformer(
            function ($opis) {
                //to jest wywoływane po funkcji sprawa->getOpis()
                $result = '';
                if($opis == null)
                return 'brak opisu';
                return $opis;
                // foreach($opis as $wyraz)
                // {
                //     $result .=$wyraz."+";
                // }
                return rtrim($result," ");
            },
            function ($opis) {
                // transform the string back to an array
                $opisArr = new ArrayCollection();
                $arr = explode(" ",$opis);
                $kolejnosc = 0;
                foreach($arr as $w)
                {
                    $wyraz = new WyrazWciagu;
                    $wyraz->setWartosc($w);
                    $wyraz->setKolejnosc($kolejnosc++);
                    $opisArr[] = $wyraz;
                }
                return $opisArr;
            }
        ))
    ;/*
        $builder->get('tags')
        ->addModelTransformer(new CallbackTransformer(
            function ($tagsAsArray) {
                // transform the array to a string
                return implode(', ', $tagsAsArray);
            },
            function ($tagsAsString) {
                // transform the string back to an array
                return explode(', ', $tagsAsString);
            }
        ))
    ;
    */
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sprawa::class,
        ]);
    }
}
