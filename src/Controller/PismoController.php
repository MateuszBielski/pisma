<?php

namespace App\Controller;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use App\Form\KontrahentType;
use App\Form\PismoLadowaniePdfType;
use App\Form\PismoType;
use App\Form\WyszukiwanieDokumentowType;
use App\Repository\KontrahentRepository;
use App\Repository\PismoRepository;
use App\Repository\SprawaRepository;
use App\Service\PismoPrzetwarzanie\PismoPrzetwarzanieNowe;
use App\Service\PracaNaPlikach;
use App\Service\PrzechwytywanieZselect2;
use App\Service\RozpoznawanieTekstu;
use App\Service\UruchomienieProcesu;
use App\Service\WyszukiwanieDokumentow;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Imagick;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/pismo")
 */
class PismoController extends AbstractController
{
    /**
     * @Route("/", name="pismo_index", methods={"GET"})
     */
    public function index(PismoRepository $pismoRepository): ?Response
    {
        // $pisma = $pismoRepository->findBy([], ['oznaczenie'=> 'DESC','dataDokumentu' => 'DESC']);
        $pisma = []; //od razu podmieniane ajaxem
        $foldPdf = $this->getParameter('sciezka_do_zarejestrowanych');
        // $entityManager = $this->getDoctrine()->getManager();
        foreach ($pisma as $p) {
            $p->UstalStroneIKierunek();
            $p->setSciezkaDoFolderuPdf($foldPdf);
            $p->UstalJesliTrzebaDateDokumentuZdatyMod();
            $p->setSciezkaGenerUrl($this->generateUrl('pismo_show', ['id' => $p->getId(), 'numerStrony' => 1]));
            //poniższe na okoliczność jednorazowego zapisu daty jeśli brakowało
            // $entityManager->persist($p);
            // $p->OpisZnazwyPliku();
        }
        //jeśli data jest w bazie, to nic nie robi
        // $entityManager->flush();
        // WyszukiwanieDokumentowType
        $wd = new WyszukiwanieDokumentow();
        $form = $this->createForm(WyszukiwanieDokumentowType::class, $wd);

        return $this->render('pismo/index.html.twig', [
            'pisma' => $pisma,
            'form' => $form->createView()
        ]);
    }
    /**
     *@Route("/noweIndex", name="pismo_nowe_index", methods={"GET","POST"}) 
     */
    public function NoweIndex(Request $request, SluggerInterface $slugger, PracaNaPlikach $pnp): Response
    {
        /*
        $skany = [];
        for($i = 7 ; $i < 12 ; $i++)$skany[] = 'skan'.$i.'.pdf';
        */
        // $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->getParameter('sciezka_do_skanow'));
        $pismo = new Pismo;
        $form = $this->createForm(PismoLadowaniePdfType::class, $pismo); //
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plikiPdf = $form->get('plik')->getData();
            foreach ($plikiPdf as $plikPdf) {
                if ($plikPdf) {
                    $originalFilename = pathinfo($plikPdf->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);

                    // $newFilename = $safeFilename.'-'.uniqid().'.'.$plikPdf->guessExtension();
                    $newFilename = $safeFilename . '.' . $plikPdf->guessExtension(); //bez unikalnego numeru
                    // echo $newFilename;
                    // Move the file to the directory where brochures are stored
                    try {
                        $plikPdf->move(
                            $this->getParameter('sciezka_do_skanow'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                }
            }

            // return $this->redirectToRoute('pismo_nowe_ze_skanu',['nazwa' => $newFilename, 'numerStrony'=> 1]);
            // return 
        }
        return $this->render('pismo/noweIndex.html.twig', [
            // 'skany' => $pnp->NazwyBezSciezkiZrozszerzeniem('pdf'),
            'pisma' => $pnp->UtworzPismaZfolderu($this->getParameter('sciezka_do_skanow'), 'pdf'),
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/indexAjax", name="pismo_indexAjax", methods={"GET","POST"})
     */
    public function indexAjax(PismoRepository $pr, Request $request): ?Response
    {
        $fraza = $request->query->get("fraza");
        $pisma = $pr->WyszukajPoFragmencieNazwyPliku($fraza);
        foreach ($pisma as $p) {
            $p->UstalStroneIKierunek();
            // $p->setSciezkaDoFolderuPdf($foldPdf);
            $p->UstalJesliTrzebaDateDokumentuZdatyMod();
            $p->setSciezkaGenerUrl($this->generateUrl('pismo_show', ['id' => $p->getId(), 'numerStrony' => 1]));
            //poniższe na okoliczność jednorazowego zapisu daty jeśli brakowało
            // $entityManager->persist($p);
        }
        $response = $this->render('pismo/listaRej.html.twig', [
            'pisma' => $pisma,
            'pismo_id' => -1,
        ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response;
    }
    /**
     * @Route("/indexAjaxWgOpisuKontrahIsprawy", name="pismo_indexAjax_WgOpisuKontrahIsprawy", methods={"GET","POST"})
     */
    public function indexAjaxWgOpisuKontrahIsprawy(PismoRepository $pr, SprawaRepository $sr, KontrahentRepository $kr, Request $request, Stopwatch $sw): ?Response
    {
        $wd = new WyszukiwanieDokumentow();
        $wd->UstawStopWatch($sw);
        $wd->UstawRepo($pr, $sr, $kr, $this);

        $sw->start('iAxWgOpisuKontrahIsprawy_form');
        $form = $this->createForm(WyszukiwanieDokumentowType::class, $wd);
        $sw->stop('iAxWgOpisuKontrahIsprawy_form');
        $sw->start('iAxWgOpisuKontrahIsprawy_handle');
        $form->handleRequest($request); //w tym momencie następuje wyszukanie w funkcji $wd->onPreSubmit()
        $sw->stop('iAxWgOpisuKontrahIsprawy_handle');
        $sw->start('iAxWgOpisuKontrahIsprawy_okreslenieRozmiaru');
        $wd->RozmiaryOkreslDlaWspolnegoPolozenia($this->getParameter('sciezka_do_zarejestrowanych'));
        $sw->stop('iAxWgOpisuKontrahIsprawy_okreslenieRozmiaru');
        $response = $this->render('pismo/3Kol_formPismaSprawyKontr.html.twig', [
            'pisma' => $wd->WyszukaneDokumenty(),
            'pismo_id' => -1,
            'sprawy' => $wd->WyszukaneSprawy(),
            'kontrahents' => $wd->WyszukaniKontrahenci(),
            'kontrahent_id' => -1,
            'form' => $form->createView(),
            // 'bazyNieRozszerzaj' => '',
        ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response;
    }

    /**
     * @Route("/indexAjaxOznaczenie", name="pismo_indexAjax_oznaczenie", methods={"GET","POST"})
     */
    public function indexAjaxOznaczenie(PismoRepository $pr, Request $request): ?Response //
    {
        $kierunekNum = $request->query->get("kierunek");
        $pismo = null;
        switch ($kierunekNum) {
            case 1: //przychodzące
                $pismo  = $pr->OstatniNumerPrzychodzacych();
                break;
            case 2: //wychodzące
                $pismo  = $pr->OstatniNumerWychodzacych();
                break;
        }
        if (!$pismo) $pismo = new Pismo;
        $response = $this->json(['odp' => $pismo->NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem()]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response;
    }
    /**
     * @Route("/rozpoznawanieAjax", name="pismo_rozpoznawanie_ajax", methods={"GET","POST"})
     */
    public function RozpoznawanieAjax(Request $request): ?Response //
    {
        // $fragmentWyrazonyUlamkami = [];
        $fragmentWyrazonyUlamkami = $request->query->get("wycinekUlamkowo");
        $polozenieObrazu = $this->getParameter('sciezka_do_png') . $request->query->get('adresObrazu');
        $rt = new RozpoznawanieTekstu;
        $rt->FolderDlaWydzielonychFragmentow($this->getParameter('sciezka_do_png'));
        $rozpoznanyTekst = $rt->RozpoznajObrazPoWspolrzUlamkowych($polozenieObrazu, $fragmentWyrazonyUlamkami);
        // $rozpoznanyTekst = $fragmentWyrazonyUlamkami;
        $response = $this->json([
            'odp' => $rozpoznanyTekst,
            'folder' => $this->getParameter('sciezka_do_png'),
            'obraz' => $polozenieObrazu,
        ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response;
    }

    /**
     * @Route("/new", name="pismo_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $pismo = new Pismo();
        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pismo);
            $entityManager->flush();

            return $this->redirectToRoute('pismo_index');
        }

        return $this->render('pismo/new.html.twig', [
            'pismo' => $pismo,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/noweZeSkanu/{nazwa}/{numerStrony}", name="pismo_nowe_ze_skanu", methods={"GET","POST"})
     */
    public function noweZeSkanuNew(Request $request, string $nazwa, PismoPrzetwarzanieNowe $przetwarzanie, Stopwatch $sw, $numerStrony = 1)
    {
        $przetwarzanie->setParametry([
            'FolderDlaPlikowPodgladu' => $this->getParameter('sciezka_do_png'),
            'DomyslnePolozeniePliku' => $this->getParameter('sciezka_do_skanow'),
            'SciezkaLubNazwaPliku' => $nazwa,
            'DocelowePolozeniePliku' => $this->getParameter('sciezka_do_zarejestrowanych'),
            'StopWatch' => $sw,
        ]);
        $przetwarzanie->PrzedFormularzem();
        $dokument = $przetwarzanie->NowyDokument();
        
        $form = $this->createForm(PismoType::class, $dokument);
        $form->handleRequest($request);
        
        $isSubmitted = $form->isSubmitted();
        $isValid = $isSubmitted ? $form->isValid() : false;
        $przetwarzanie->RezultatWalidacjiFormularza($isValid);
        $informacjeOutrwaleniuPlikow = $przetwarzanie->UtrwalPliki();
        
        if ($isSubmitted && $isValid && $informacjeOutrwaleniuPlikow->czyUtrwalone()) {
            $entityManager = $this->getDoctrine()->getManager();
            
            $entityManager->persist($dokument);
            $entityManager->flush();
            
            return $this->redirectToRoute('pismo_show', ['id' => $dokument->getId(), 'numerStrony' => $numerStrony]);
        }
        $sciezkiDoPodgladow = $dokument->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem();
        $sciezkiDlaStron = [];
        $num = 0;
        foreach ($sciezkiDoPodgladow as $sc) $sciezkiDlaStron[] = $this->generateUrl('pismo_nowe_ze_skanu', ['nazwa' => $nazwa, 'numerStrony' => ++$num]);
        $sciezkiDoPodgladowBezFolderuGlownego = $dokument->SciezkiDoPlikuPodgladowPrzedZarejestrowaniemBezFolderuGlownego();
        $pnp = $przetwarzanie->getPracaNaPlikach();//do usunięcia (pnp ma być tu niewidoczny)
        return $this->render('pismo/noweZeSkanu.html.twig', [
            // 'skany' => $pnp->NazwyBezSciezkiZrozszerzeniem('pdf'),
            //lista pism niezarejestrowanych powinna być dostępna z funkcji która filtruje tylko niezarejestrowane z danego folderu
            'pisma' => $pnp->UtworzPismaZfolderu($this->getParameter('sciezka_do_skanow'), 'pdf'),
            'pismo' => $dokument,
            'form' => $form->createView(),
            'sciezki_dla_stron' => $sciezkiDlaStron,
            'sciezka_png' => $sciezkiDoPodgladow[$numerStrony - 1],
            'sciezka_png_bez_fg' => $sciezkiDoPodgladowBezFolderuGlownego[$numerStrony - 1],
            'numerStrony' => $numerStrony,
            // 'kontrahentForm' => $kontrahentForm->createView(),
        ]);
    }


    public function noweZeSkanuOld(Request $request, string $nazwa, KontrahentRepository $kr, PismoRepository $pr, PracaNaPlikach $pnp, $numerStrony = 1): Response
    {
        // $pnp = new PracaNaPlikach;
        // $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->getParameter('sciezka_do_skanow'));
        $pismo = $pnp->UtworzPismoNaPodstawie($this->getParameter('sciezka_do_skanow'), $nazwa);
        $ostatniePismo = $pr->OstatniNumerPrzychodzacych();
        if (!$ostatniePismo) $ostatniePismo = new Pismo;
        $pismo->setOznaczenie($ostatniePismo->NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem());
        $pnp->setUruchomienieProcesu(new UruchomienieProcesu);
        $pnp->GenerujPodgladJesliNieMaDlaPisma($this->getParameter('sciezka_do_png'), $pismo);

        //przechwytywanie powinno być przeniesine do event subscribera i oddzielnie testowane
        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);

        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $pnp->PrzeniesPlikiPdfiPodgladu($this->getParameter('sciezka_do_zarejestrowanych'), $pismo)) {
            $entityManager = $this->getDoctrine()->getManager();
            $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo, $entityManager);
            $przechwytywanie->przechwyconyRodzajDokumentuDlaPismaUtrwal($pismo, $entityManager);

            $entityManager->persist($pismo);
            $entityManager->flush();

            return $this->redirectToRoute('pismo_show', ['id' => $pismo->getId(), 'numerStrony' => $numerStrony]);
        }
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem();
        $sciezkiDlaStron = [];
        $num = 0;
        foreach ($sciezkiDoPodgladow as $sc) $sciezkiDlaStron[] = $this->generateUrl('pismo_nowe_ze_skanu', ['nazwa' => $nazwa, 'numerStrony' => ++$num]);
        $sciezkiDoPodgladowBezFolderuGlownego = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniemBezFolderuGlownego();
        return $this->render('pismo/noweZeSkanu.html.twig', [
            // 'skany' => $pnp->NazwyBezSciezkiZrozszerzeniem('pdf'),
            'pisma' => $pnp->UtworzPismaZfolderu($this->getParameter('sciezka_do_skanow'), 'pdf'),
            'pismo' => $pismo,
            'form' => $form->createView(),
            'sciezki_dla_stron' => $sciezkiDlaStron,
            'sciezka_png' => $sciezkiDoPodgladow[$numerStrony - 1],
            'sciezka_png_bez_fg' => $sciezkiDoPodgladowBezFolderuGlownego[$numerStrony - 1],
            'numerStrony' => $numerStrony,
            // 'kontrahentForm' => $kontrahentForm->createView(),
        ]);
    }
    /**
     * @Route("/nowyDokumentOdt/{nazwa}/{numerStrony}", name="nowy_dokument_odt", methods={"GET","POST"})
     */
    public function nowyDokumentOdt($numerStrony = 1)
    {
        return new Response();
    }

    /**
     * @Route("/pobieranie/{id}", name="pismo_pobieranie", methods={"GET","POST"})
     */
    public function Pobieranie(Pismo $pismo): ?BinaryFileResponse
    {
        $file = $this->getParameter('sciezka_do_zarejestrowanych') . $pismo->getNazwaPliku();
        // $file = 'path/to/file.txt';
        $response = new BinaryFileResponse($file);
        //pobieranie pliku , nie wyswietlenia https://geekster.pl/symfony/dynamiczne-tworzenie-i-pobieranie-archiwum-zip/
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        return $response;
    }

    /**
     * @Route("/{id}/{numerStrony}", name="pismo_show", methods={"GET"})
     */
    public function show(int $id, int $numerStrony, PismoRepository $pismoRepository): Response //Pismo $pismo
    {
        // ;
        $pismo = $pismoRepository->find($id);
        $pismo->UstalStroneIKierunek();
        $pismo->setSciezkaGenerUrl($this->generateUrl('pismo_show', ['id' => $id, 'numerStrony' => $numerStrony]));
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
        $sciezkiDlaStron = [];
        $num = 0;
        foreach ($sciezkiDoPodgladow as $sc) $sciezkiDlaStron[] = $this->generateUrl('pismo_show', ['id' => $id, 'numerStrony' => ++$num]);
        $pisma = $pismoRepository->findBy([], ['dataDokumentu' => 'DESC']);
        foreach ($pisma as $p) {
            $p->UstalStroneIKierunek();
            $p->setSciezkaGenerUrl($this->generateUrl('pismo_show', ['id' => $p->getId(), 'numerStrony' => 1]));
        }

        return $this->render('pismo/show.html.twig', [
            'pismo' => $pismo,
            'pisma' => $pisma,
            'sciezki_dla_stron' => $sciezkiDlaStron,
            'sciezka_png' => $sciezkiDoPodgladow[$numerStrony - 1],
            'numerStrony' => $numerStrony,
        ]);
    }

    /**
     * @Route("/edit/{id}/{numerStrony}", name="pismo_edit", methods={"GET","POST"})
     */
    public function edit($numerStrony, Request $request, Pismo $pismo, PracaNaPlikach $pnp): Response
    {

        $pismo->UstalStroneIKierunek();

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);


        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);
        $id = $pismo->getId();


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo, $em);
            $przechwytywanie->przechwyconyRodzajDokumentuDlaPismaUtrwal($pismo, $em);
            foreach ($pismo->NiepotrzebneWyrazy() as $n) {
                // echo "id ".$n->getId()." ".$n->getWartosc()." ";

                $em->remove($n);
            }

            $em->flush();
            $pnp->UaktualnijNazwyPlikowPodgladu($pismo);
            $pnp->UaktualnijNazwePlikuPdf($this->getParameter('sciezka_do_zarejestrowanych'), $pismo);
            return $this->redirectToRoute('pismo_show', ['id' => $id, 'numerStrony' => $numerStrony]);
            // return $this->redirectToRoute('kontrahent_show',['id'=> $pismo->getStrona()->getId(),'pismo_id'=> $id,'numerStrony' => $numerStrony]);

        }
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
        //ponizsze poprawić bo dwa razy czyta folder z podglądem
        $sciezkiDoPodgladowBezFolderuGlownego = $pismo->SciezkiDoPlikuPodgladowZarejestrowanychBezFolderuGlownego();
        $sciezkiDlaStron = [];
        $num = 0;
        foreach ($sciezkiDoPodgladow as $sc) $sciezkiDlaStron[] = $this->generateUrl('pismo_edit', ['id' => $id, 'numerStrony' => ++$num]);
        return $this->render('pismo/edit.html.twig', [
            'pismo' => $pismo,
            'form' => $form->createView(),
            'sciezki_dla_stron' => $sciezkiDlaStron,
            'sciezka_png' => $sciezkiDoPodgladow[$numerStrony - 1],
            'sciezka_png_bez_fg' => $sciezkiDoPodgladowBezFolderuGlownego[$numerStrony - 1],
            'numerStrony' => $numerStrony,
        ]);
    }

    /**
     * @Route("/{id}", name="pismo_delete", methods={"POST"})
     */
    public function delete(Request $request, Pismo $pismo): Response
    {
        if ($this->isCsrfTokenValid('delete' . $pismo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($pismo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('pismo_index');
    }
    public function GenerujUrlPismoShow_IdStrona($id, $nrStrony)
    {
        return $this->generateUrl('pismo_show', ['id' => $id, 'numerStrony' => $nrStrony]);
    }
}
