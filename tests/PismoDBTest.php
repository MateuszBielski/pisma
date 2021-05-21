<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PismoDBTest extends KernelTestCase
{
    private $entityManager;
    protected function _setUp(): void
    {
        $kernel = self::bootKernel();
        $doctrine = $this->entityManager = $kernel->getContainer()
        ->get('doctrine');

        $this->entityManager = $doctrine->getManager();
    }
    public function testSomething(): void
    {
        

       $this->assertEquals(2,2);
        //$routerService = self::$container->get('router');
        //$myCustomService = self::$container->get(CustomService::class);
    }
}
/*
class CatalogDBTest extends KernelTestCase
{
    private $entityManager;
    private $repCatalog;
    private $repChapter;
    private $repTableRow;
    
    protected function setUp()
    {
        $kernel = self::bootKernel();
        $doctrine = $this->entityManager = $kernel->getContainer()
        ->get('doctrine');

        $this->entityManager = $doctrine->getManager();
        $this->repCatalog = $doctrine->getRepository(Catalog::class);
        $this->repChapter = $doctrine->getRepository(Chapter::class);
        $this->repTableRow = $doctrine->getRepository(TableRow::class);
    }
    public function testCountChaptersPersistedCatalog()
    {
        $this->entityManager->getConnection()->beginTransaction();
        $catFile = 'resources/Norma3/Kat/2-28/';
        $catalog = new Catalog;
        $catalog->ReadFromDir($catFile,TABLE);//,CHAPTER
        $this->entityManager->persist($catalog);
        $this->entityManager->flush();
        $foundCatalog = $this->repCatalog->findOneBy(array('name'=>'KNR   2-28'));
        $this->entityManager->getConnection()->rollBack();
*/