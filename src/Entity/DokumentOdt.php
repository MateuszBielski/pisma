<?php

namespace App\Entity;

use App\Entity\Pismo;
use App\Service\GeneratorPodgladuOdt\GeneratorPodgladuOdt;
use App\Service\SciezkeZakonczSlashem;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;

/**
 * @ORM\Entity
 */
class DokumentOdt extends Pismo
{
    
    private $rozszerzeniePlikuPodgladu = 'html';
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
        $plikDoOdczytu = $this->adresZrodlaPrzedZarejestrowaniem;
        if(!strlen($plikDoOdczytu) && strlen($this->nazwaPliku)){
            new SciezkeZakonczSlashem($this->polozeniePoZarejestrowaniu);
            $plikDoOdczytu = $this->polozeniePoZarejestrowaniu.$this->nazwaPliku;
        }
        //na podstawie 
        //https://gist.github.com/lovasoa/1918801
        if (!file_exists($plikDoOdczytu)) return;
        $xml = new \XMLReader();
        $xml->open('zip://' . $plikDoOdczytu . '#content.xml');
        while ($xml->read()) {
            if ($xml->name == "text:p" && $xml->nodeType == \XMLReader::ELEMENT)
            {
                $this->tresc .= $xml->readString()."<BR>";

            }
        }
        $xml->close();
        //stała \XMLReader::ELEMENT -> 1;
        //stała \XMLReader::END_ELEMENT -> 15;
        //stała \XMLReader::TEXT -> 3;
    }
    public function SzablonNowyWidok(): string
    {
        return 'pismo/noweOdt.html.twig';
    }
    public function SzablonWidok(): string
    {
        return 'pismo/showOdt.html.twig';
    }
    public function UzupelnijDaneDlaGenerowaniaSzablonuNoweWidok(array &$parametry)
    {
        $parametry['sciezkaZnazwaPlikuPodgladuAktualnejStrony'] = $this->getSciezkaZnazwaPlikuPodgladuAktualnejStrony();
    }
    
    public function UzupelnijDaneDlaGenerowaniaSzablonuWidok(array &$parametry)
    {
        $parametry['sciezkaZnazwaPlikuPodgladuAktualnejStrony'] = $this->getSciezkaZnazwaPlikuPodgladuAktualnejStrony();
    }
    public function getSciezkaZnazwaPlikuPodgladuAktualnejStrony()
    {
        return $this->sciezkaZnazwaPlikuPodgladuAktualnejStrony;
    }
    public function setSciezkaZnazwaPlikuPodgladuAktualnejStrony(string $adr)
    {
        $this->sciezkaZnazwaPlikuPodgladuAktualnejStrony = $adr;
    }
    public function WidokXML()
    {
        $result = '';
        $xml = new \XMLReader();
        // $xml->open('zip://' . $this->adresZrodlaPrzedZarejestrowaniem . '#content.xml');
        $xml->open('zip://' . $this->adresZrodlaPrzedZarejestrowaniem . '#styles.xml');
        while ($xml->read()) {

            // if ($xml->name == "text:p" && $xml->nodeType == \XMLReader::ELEMENT)
            //     $this->tresc .= $xml->readString();
            // $result .= $xml->name." <BR> ";

            // if ($xml->name == "#text") {
            //     print($xml->readString());
            //     print("<br>");
            // }
            print($xml->name." ".$xml->localName."<br>");
            $jestAtrybut = $xml->moveToFirstAttribute();
            while($jestAtrybut)
            {
                print($xml->name." ".$xml->localName." ".$xml->value.", ");//.$xml->readString()
                $jestAtrybut = $xml->moveToNextAttribute();
            }
            print("<br>");
        }
        $xml->close();
        // return $result;
        /*
        atrybuty węzła:
        $xml->name nazwa w dłuższym formacie np. style:use-window-font-color
        $xml->localName nazwa doprecyzowanie czyli dla tego co powyżej : use-window-font-color
        $xml->value wartość: np true, #3465a4
        */
    }

    public function NazwaParametruZfoderemPodgladu(): string
    {
        return 'sciezka_do_podgladuOdt';
    }
    public function RozszerzeniePlikuPodgladu()
    {
        return $this->rozszerzeniePlikuPodgladu;
    }
    public function setFolderPodgladu(string $path)
    {
        $generator = new GeneratorPodgladuOdt();
        $this->sciezkaZnazwaPlikuPodgladuAktualnejStrony = $generator->UtworzAdresPodgladuStrony($path,$this->NazwaZrodlaBezRozszerzenia(),1);
    }
}
