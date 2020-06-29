<?php

/*
 * This file is part of [petzka/contao-indisign-bundle].
 *
 * (c) Moritz Petzka <info@petzka.com>
 *
 * @license LGPL-3.0-or-later
 */


namespace Petzka\ContaoNewsSearch\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use Petzka\ContaoIndesignBundle\Export\ExportArticles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ExportArticles
     */
    private $exportArticles;

    /**
     * DownloadController constructor.
     * @param ContaoFramework $framework
     * @param RequestStack $requestStack
     * @param Connection $connection
     * @param ExportArticles $exportArticles
     */
    public function __construct(ContaoFramework $framework, RequestStack $requestStack, Connection $connection, ExportArticles $exportArticles)
    {
        $this->framework = $framework;
        $this->requestStack = $requestStack;
        $this->connection = $connection;
        $this->exportArticles = $exportArticles;

        $this->framework->initialize();
    }

    /**
     * This controller searches for articles by topic and category
     */
    public function searchAction()
    {
        $this->exportArticles->prepareExport();
        exit;
    }
}
