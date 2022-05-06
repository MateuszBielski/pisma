<?php

use App\Entity\Pismo;

namespace App\Entity;

use Exception;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;

class DokumentOdt extends Pismo
{
    private string $tresc = '';
    public function Tresc(): string
    {
        $this->OdczytajTresc();
        return $this->tresc;
    }
    public function UrlWidokNowe()
    {
        if (!isset($this->router)) throw new Exception(
            'należy ustawić router dla pisma'
        );
        return $this->router->generate('nowy_dokument_odt',[
                'nazwa' => $this->nazwaPliku,
                'numerStrony' => $this->numerStrony]);
    }
    private function OdczytajTresc()
    {
       //na podstawie 
       //https://gist.github.com/lovasoa/1918801
        $xml = new \XMLReader();
        $xml->open('zip://' . $this->adresZrodlaPrzedZarejestrowaniem . '#content.xml');
        while ($xml->read()) {
            if ($xml->name == "text:p" && $xml->nodeType == \XMLReader::ELEMENT)
            $this->tresc .= $xml->readString();
        }
        $xml->close();
        //stała \XMLReader::ELEMENT -> 1;
        //stała \XMLReader::END_ELEMENT -> 15;
    }

}
