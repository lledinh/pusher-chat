<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Pusher\Pusher;
use Pusher\PusherException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MessageController extends AbstractController
{
    private $session;
    private $logger;
    private $pusher;

    public function __construct(SessionInterface $session, LoggerInterface $logger)
    {
        $this->session = $session;
        $this->logger = $logger;
        try {
            $this->pusher = new Pusher("f63a595c360996836b72", "c0f550d0afeef0acdf9c", "873980", array('cluster' => 'eu'));
        } catch (PusherException $e) {
        }
    }

    /**
     * @Route("/message-template", name="message-template")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request) {

    }


}