<?php

namespace App\Controller;

use App\Entity\Kontrahent;
use App\Entity\Pismo;
use App\Form\KontrahentType;
use App\Form\PismoLadowaniePdfType;
use App\Form\PismoType;
use App\Repository\KontrahentRepository;
use App\Repository\PismoRepository;
use App\Repository\SprawaRepository;
use App\Service\PracaNaPlikach;
use App\Service\PrzechwytywanieZselect2;
use App\Service\RozpoznawanieTekstu;
use App\Service\UruchomienieProcesu;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Imagick;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
        $pisma = $pismoRepository->findBy([], ['dataDokumentu' => 'DESC']);//findAll();
        $foldPdf = $this->getParameter('sciezka_do_zarejestrowanych');
        $entityManager = $this->getDoctrine()->getManager();
        foreach($pisma as $p)
        {
            $p->UstalStroneIKierunek();
            $p->setSciezkaDoFolderuPdf($foldPdf);
            $p->UstalJesliTrzebaDateDokumentuZdatyMod();
            $p->setSciezkaGenerUrl($this->generateUrl('pismo_show',['id'=> $p->getId(), 'numerStrony' => 1 ]));
            //poniższe na okoliczność jednorazowego zapisu daty jeśli brakowało
            // $entityManager->persist($p);
            // $p->OpisZnazwyPliku();
        }
        //jeśli data jest w bazie, to nic nie robi
        // $entityManager->flush();
            
        return $this->render('pismo/index.html.twig', [
            'pisma' => $pisma,
        ]);;
    }
    /**
     *@Route("/noweIndex", name="pismo_nowe_index", methods={"GET","POST"}) 
     */
    public function NoweIndex(Request $request, SluggerInterface $slugger): Response
    {
        /*
        $skany = [];
        for($i = 7 ; $i < 12 ; $i++)$skany[] = 'skan'.$i.'.pdf';
        */
        // $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->getParameter('sciezka_do_skanow'));
        $pismo = new Pismo;
        $form = $this->createForm(PismoLadowaniePdfType::class, $pismo);//
        $form->handleRequest($request);
        
        $pnp = new PracaNaPlikach;
        if ($form->isSubmitted() && $form->isValid()) {
            $plikiPdf= $form->get('plik')->getData();
            foreach($plikiPdf as $plikPdf)
            {
                if ($plikPdf) {
                    $originalFilename = pathinfo($plikPdf->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    
                    // $newFilename = $safeFilename.'-'.uniqid().'.'.$plikPdf->guessExtension();
                    $newFilename = $safeFilename.'.'.$plikPdf->guessExtension();//bez unikalnego numeru
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
            'pisma' => $pnp->UtworzPismaZfolderu($this->getParameter('sciezka_do_skanow'),'pdf'),
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
        foreach($pisma as $p)
        {
            $p->UstalStroneIKierunek();
            // $p->setSciezkaDoFolderuPdf($foldPdf);
            $p->UstalJesliTrzebaDateDokumentuZdatyMod();
            $p->setSciezkaGenerUrl($this->generateUrl('pismo_show',['id'=> $p->getId(), 'numerStrony' => 1 ]));
            //poniższe na okoliczność jednorazowego zapisu daty jeśli brakowało
            // $entityManager->persist($p);
        }
        $response = $this->render('pismo/listaRej.html.twig',[
            'pisma' => $pisma,
            'pismo_id' => -1,
            ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response; 
    }
    /**
     * @Route("/indexAjaxWgOpisuKontrahIstrony", name="pismo_indexAjax_WgOpisuKontrahIstrony", methods={"GET","POST"})
     */
    public function indexAjaxWgOpisuKontrahIstrony(PismoRepository $pr,SprawaRepository $sr,KontrahentRepository $kr, Request $request): ?Response
    {
        $opisPisma = $request->query->get("opisPisma");
        $opisSprawy = $request->query->get("opisSprawy");
        $nazwaKontrahenta = $request->query->get("nazwaKontrahenta");
        $pisma = $pr->WyszukajPoFragmentachOpisuKontrahIsprawy($opisPisma,$opisSprawy,$nazwaKontrahenta);
        
        $sprawy = [];
        if (strlen($opisSprawy))
        $sprawy = $sr->wyszukajPoFragmentachWyrazuOpisu($opisSprawy);

        $kontrahenci = [];
        if (strlen($nazwaKontrahenta))
        $kontrahenci = $kr->WyszukajPoFragmencieNazwy($nazwaKontrahenta);
        
        foreach($pisma as $p)
        {
            $p->UstalStroneIKierunek();
            // $p->setSciezkaDoFolderuPdf($foldPdf);
            $p->UstalJesliTrzebaDateDokumentuZdatyMod();
            $p->setSciezkaGenerUrl($this->generateUrl('pismo_show',['id'=> $p->getId(), 'numerStrony' => 1 ]));
            //poniższe na okoliczność jednorazowego zapisu daty jeśli brakowało
            // $entityManager->persist($p);
        }
        // $response = $this->render('pismo/listaRej.html.twig',[
        $response = $this->render('pismo/w3KolPismaSprawyKontr.html.twig',[
            'pisma' => $pisma,
            'pismo_id' => -1,
            'sprawy' => $sprawy,
            'kontrahents' => $kontrahenci,
            'kontrahent_id' => -1,
            // 'bazyNieRozszerzaj' => '',
            ]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response; 
    }
    
    /**
     * @Route("/indexAjaxOznaczenie", name="pismo_indexAjax_oznaczenie", methods={"GET","POST"})
     */
    public function indexAjaxOznaczenie(PismoRepository $pr, Request $request): ?Response//
    {
        $kierunekNum = $request->query->get("kierunek");
        $pismo = null;
        switch($kierunekNum)
        {
            case 1 : //przychodzące
            $pismo  = $pr->OstatniNumerPrzychodzacych();
            break;
            case 2 : //wychodzące
            $pismo  = $pr->OstatniNumerWychodzacych();
            break;
        }
        if(!$pismo)$pismo = new Pismo;
        $response = $this->json(['odp'=> $pismo->NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem()]);
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response; 
    }
    /**
     * @Route("/rozpoznawanieAjax", name="pismo_rozpoznawanie_ajax", methods={"GET","POST"})
     */
    public function RozpoznawanieAjax(Request $request): ?Response//
    {
        // $fragmentWyrazonyUlamkami = [];
        $fragmentWyrazonyUlamkami = $request->query->get("wycinekUlamkowo");
        $polozenieObrazu = $this->getParameter('sciezka_do_png').$request->query->get('adresObrazu');
        $rt = new RozpoznawanieTekstu;
        $rt->FolderDlaWydzielonychFragmentow($this->getParameter('sciezka_do_png'));
        $rozpoznanyTekst = $rt->RozpoznajObrazPoWspolrzUlamkowych($polozenieObrazu,$fragmentWyrazonyUlamkami);
        // $rozpoznanyTekst = $fragmentWyrazonyUlamkami;
        $response = $this->json(['odp'=> $rozpoznanyTekst,
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
    public function noweZeSkanu(Request $request, string $nazwa, $numerStrony = 1, KontrahentRepository $kr, PismoRepository $pr): Response
    {
        $pnp = new PracaNaPlikach;
        // $pnp->PobierzWszystkieNazwyPlikowZfolderu($this->getParameter('sciezka_do_skanow'));
        $pismo = $pnp->UtworzPismoNaPodstawie($this->getParameter('sciezka_do_skanow'),$nazwa);
        $ostatniePismo = $pr->OstatniNumerPrzychodzacych() ;
        if(!$ostatniePismo)$ostatniePismo = new Pismo;
        $pismo->setOznaczenie($ostatniePismo->NaPodstawieMojegoOznZaproponujOznaczenieZaktualnymRokiem());
        $pnp->setUruchomienieProcesu(new UruchomienieProcesu);
        $pnp->GenerujPodgladJesliNieMaDlaPisma($this->getParameter('sciezka_do_png'),$pismo);

        
        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);
        
        
        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);
        // echo "\nXXXX".$nowaNazwaKontrahenta."YYY".($utworzycNowegoKontrahenta ? "tak":"nie");
        
        
        if ($form->isSubmitted() && $form->isValid() && $pnp->PrzeniesPlikiPdfiPodgladu($this->getParameter('sciezka_do_zarejestrowanych'),$pismo)) {
            $entityManager = $this->getDoctrine()->getManager();
            $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo,$entityManager);
            $przechwytywanie->przechwyconyRodzajDokumentuDlaPismaUtrwal($pismo,$entityManager);
            
            $entityManager->persist($pismo);
            $entityManager->flush();

            return $this->redirectToRoute('pismo_index');
        }
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniem(); 
        $sciezkiDlaStron = [];
        $num = 0;
        foreach($sciezkiDoPodgladow as $sc)$sciezkiDlaStron[] = $this->generateUrl('pismo_nowe_ze_skanu',['nazwa'=> $nazwa, 'numerStrony' => ++$num ]);
        $sciezkiDoPodgladowBezFolderuGlownego = $pismo->SciezkiDoPlikuPodgladowPrzedZarejestrowaniemBezFolderuGlownego();
        return $this->render('pismo/noweZeSkanu.html.twig', [
            // 'skany' => $pnp->NazwyBezSciezkiZrozszerzeniem('pdf'),
            'pisma' => $pnp->UtworzPismaZfolderu($this->getParameter('sciezka_do_skanow'),'pdf'),
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
     * @Route("/pobieranie/{id}", name="pismo_pobieranie", methods={"GET","POST"})
     */
    public function Pobieranie(Pismo $pismo): ?BinaryFileResponse
    {
        $file = $this->getParameter('sciezka_do_zarejestrowanych').$pismo->getNazwaPliku();
        // $file = 'path/to/file.txt';
        $response = new BinaryFileResponse($file);
        //pobieranie pliku , nie wyswietlenia https://geekster.pl/symfony/dynamiczne-tworzenie-i-pobieranie-archiwum-zip/
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        return $response;
    }

    /**
     * @Route("/{id}/{numerStrony}", name="pismo_show", methods={"GET"})
     */
    public function show(int $id,int $numerStrony, PismoRepository $pismoRepository): Response//Pismo $pismo
    {
        // ;
        $pismo = $pismoRepository->find($id);
        $pismo->UstalStroneIKierunek();
        $pismo->setSciezkaGenerUrl($this->generateUrl('pismo_show',['id'=> $id, 'numerStrony' => $numerStrony ]));
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
        $sciezkiDlaStron = [];
        $num = 0;
        foreach($sciezkiDoPodgladow as $sc)$sciezkiDlaStron[] = $this->generateUrl('pismo_show',['id'=> $id, 'numerStrony' => ++$num ]);
        $pisma = $pismoRepository->findBy([], ['dataDokumentu' => 'DESC']);
        foreach($pisma as $p){
            $p->UstalStroneIKierunek();
            $p->setSciezkaGenerUrl($this->generateUrl('pismo_show',['id'=> $p->getId(), 'numerStrony' => 1 ]));
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
    public function edit($numerStrony, Request $request, Pismo $pismo): Response
    {
        
        $pismo->UstalStroneIKierunek();

        $przechwytywanie = new PrzechwytywanieZselect2;
        $przechwytywanie->przechwycNazweStronyDlaPisma($request);
        $przechwytywanie->przechwycRodzajDokumentuDlaPisma($request);
        
        
        $form = $this->createForm(PismoType::class, $pismo);
        $form->handleRequest($request);
        $id = $pismo->getId();
        
        
        if ($form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();
            $przechwytywanie->przechwyconaNazweStronyDlaPismaUtrwal($pismo,$em);
            $przechwytywanie->przechwyconyRodzajDokumentuDlaPismaUtrwal($pismo,$em);
            foreach($pismo->NiepotrzebneWyrazy() as $n)
            {
                // echo "id ".$n->getId()." ".$n->getWartosc()." ";

                $em->remove($n);
            }
            
            $em->flush();
            $pnp = new PracaNaPlikach;
            $pnp->UaktualnijNazwyPlikowPodgladu($pismo);
            $pnp->UaktualnijNazwePlikuPdf($this->getParameter('sciezka_do_zarejestrowanych'),$pismo);
            return $this->redirectToRoute('pismo_show',['id'=> $id,'numerStrony' => $numerStrony]);
            // return $this->redirectToRoute('kontrahent_show',['id'=> $pismo->getStrona()->getId(),'pismo_id'=> $id,'numerStrony' => $numerStrony]);
        
        }
        $sciezkiDoPodgladow = $pismo->SciezkiDoPlikuPodgladowZarejestrowanych();
        //ponizsze poprawić bo dwa razy czyta folder z podglądem
        $sciezkiDoPodgladowBezFolderuGlownego = $pismo->SciezkiDoPlikuPodgladowZarejestrowanychBezFolderuGlownego();
        $sciezkiDlaStron = [];
        $num = 0;
        foreach($sciezkiDoPodgladow as $sc)$sciezkiDlaStron[] = $this->generateUrl('pismo_edit',['id'=> $id, 'numerStrony' => ++$num ]);
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
        if ($this->isCsrfTokenValid('delete'.$pismo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($pismo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('pismo_index');
    }
}

