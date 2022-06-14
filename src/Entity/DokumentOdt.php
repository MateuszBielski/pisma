<?php

namespace App\Entity;

use App\Entity\Pismo;
use Exception;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;

class DokumentOdt extends Pismo
{
    protected string $tresc = '';
    public function Tresc(): string
    {
        $this->OdczytajTresc();
        return $this->tresc;
    }
    /** będzie używana wspólna ścieżka w kontrolerze, różnicowanie na typy zwiększy ilość route w kontrolerze */
    // public function UrlWidokNowe()
    // {
    //     if (!isset($this->router)) throw new Exception(
    //         'należy ustawić router dla pisma'
    //     );
    //     return $this->router->generate('nowy_dokument_odt', [
    //         'nazwa' => $this->nazwaPliku,
    //         'numerStrony' => $this->numerStrony
    //     ]);
    // }
    private function OdczytajTresc()
    {
        //na podstawie 
        //https://gist.github.com/lovasoa/1918801
        if(!file_exists($this->adresZrodlaPrzedZarejestrowaniem)) return;
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
