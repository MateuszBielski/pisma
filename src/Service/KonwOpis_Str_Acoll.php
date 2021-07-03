<?php

namespace App\Service;

use App\Entity\WyrazWciagu;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class KonwOpis_Str_Acoll
{
    public function Acoll_to_string(Collection $coll):string
    {
        $iter =  $coll->getIterator();
        $iter->uasort(function($w1,$w2){
                $a = $w1->getKolejnosc();
                $b = $w2->getKolejnosc();
                if ($a == $b) return 0;
                return ($a < $b)?-1:1;
            });
        $arr = iterator_to_array($iter);
        $result = '';
        foreach($arr as $wyraz)
        {
            $result .=$wyraz->getWartosc()." ";//$wyraz->getId()." ".
        }
        return rtrim($result," ");
    }

    public function String_to_collection(?string $str)
    {
        $res = new ArrayCollection();
        if($str == null)return $res;
        $arr = explode(" ",$str);
        $num = 1;
        foreach($arr as $r)
        {
            $w = new WyrazWciagu($r);
            $w->setKolejnosc(($num++));
            $res[] = $w;
        }

        return $res;
    }
   
}

