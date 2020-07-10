<?php

declare(strict_types=1);
namespace Paustian\BookModule\Controller;

use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;




class ImportController extends AbstractController
{
    //I am working on an idea to be able to import simple html files saved from open office
    //I would have a template in odt format that you would then save as html and then
    //the module would be able to import it. I have to work out some rules, but it clearly would work
    //You need to clean out a bunch of <span> tags it puts in, but otherwise it works well
    //and would be easy to parse into html. I do have to investigate tables however.
}