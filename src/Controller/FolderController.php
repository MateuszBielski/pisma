<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Form\FolderType;
use App\Repository\FolderRepository;
use App\Service\PracaNaPlikach;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/folder")
 */
class FolderController extends AbstractController
{
    /**
     * @Route("/", name="folder_index", methods={"GET"})
     */
    public function index(FolderRepository $folderRepository): Response
    {
        return $this->render('folder/index.html.twig', [
            'folders' => $folderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/odczytZawartosciAjax", name="folder_odczytZawartosci_ajax", methods={"GET"})
     */
    public function odczytZawartosciAjax(Request $request, PracaNaPlikach $pnp)
    {
        $sciezka = $request->query->get("fraza");
        $sciezka = $pnp->NajglebszyMozliwyFolderZniepelnejSciezki($sciezka);
        $pisma = $pnp->UtworzPismaZfolderu($sciezka);
        $foldery = $pnp->PobierzWszystkieNazwyFolderowZfolderu($sciezka);
        // $foldery = $pnp->ZfolderuPobierzNazwyFolderowZakonczoneUkosnikiem($sciezka);
        $response = new Response();

        $response->setContent(json_encode([
            'dataAutocomplete' => $foldery,
            'dataHtml' => $this->render('pismo/listaNier.html.twig', [
                'pisma' => $pisma,
            ])
        ]));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response;
    }
    /**
     * @Route("/nazwyFolderowDlaAutocomplete", name="nazwy_folderow_dla_autocomplete", methods={"GET"})
     */
    public function nazwyFolderowDlaAutocomplete(Request $request, PracaNaPlikach $pnp)
    {
        $sciezka = $request->query->get("fraza");
        $sciezkaOstatniegoFolderu = $pnp->NajglebszyMozliwyFolderZniepelnejSciezki($sciezka);
        $sciezkaPozostaloscDoWyszukania = $pnp->CzescSciezkiZaFolderem($sciezka, $sciezkaOstatniegoFolderu);
        $foldery = $pnp->PobierzWszystkieNazwyFolderowZfolderu($sciezkaOstatniegoFolderu);

        $folderyPasujaceDoFrazy = $pnp->FitrujFolderyPasujaceDoFrazy($foldery, $sciezkaPozostaloscDoWyszukania);
        $pelneFoldery = rtrim($sciezkaOstatniegoFolderu,"/");
        $response = new Response();
        $response->setContent(
            json_encode([
                'foldery' => $folderyPasujaceDoFrazy,
                'pelneFoldery' => $pelneFoldery,
            ])
        );
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return  $response;
    }

    /**
     * @Route("/new", name="folder_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $folder = new Folder();
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($folder);
            $entityManager->flush();

            return $this->redirectToRoute('folder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('folder/new.html.twig', [
            'folder' => $folder,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="folder_show", methods={"GET"})
     */
    public function show(Folder $folder): Response
    {
        return $this->render('folder/show.html.twig', [
            'folder' => $folder,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="folder_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Folder $folder): Response
    {
        $form = $this->createForm(FolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('folder_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('folder/edit.html.twig', [
            'folder' => $folder,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="folder_delete", methods={"POST"})
     */
    public function delete(Request $request, Folder $folder): Response
    {
        if ($this->isCsrfTokenValid('delete' . $folder->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($folder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('folder_index', [], Response::HTTP_SEE_OTHER);
    }
}
