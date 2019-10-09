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

class HomeController extends AbstractController
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
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request) {
        $user = $this->session->get('user', null);

        if (!empty($user)) {
            $userID = $user->getId();
            $presenceData = array('name' => $user->getName(), 'email' => $user->getMail(), 'test' => 'test');
        }
        else {
            $userID = "";
            $presenceData = array('name' => '', 'email' => '');
        }

        return $this->render('index.html.twig', [
            'user_id' => $userID,
            'name' => $presenceData['name'],
            'email' => $presenceData['email']
        ]);
    }

    /**
     * @Route("/auth-pusher", name="auth_pusher")
     * @param Request $request
     * @return Response
     */
    public function authPusher(Request $request) {
        $user = $this->session->get('user', null);

        if (!empty($user)) {
            $userID = $user->getId();
            $presenceData = array('name' => $user->getName(), 'email' => $user->getMail(), 'test' => 'test');
        }
        else {
            return new Response("Forbidden", 403);
        }

        $socketID = $request->request->get('socket_id');
        $channelName = $request->request->get('channel_name');
        try {
            $res = $this->pusher->presence_auth($channelName, $socketID, $userID, $presenceData);
        } catch (PusherException $e) {
            return new Response("socketID = " . $socketID . " channel = " . $channelName . " " . $e->getMessage());
        }

        return new Response($res);
    }

    /**
     * @Route("/push-presence", name="push-presence")
     * @param Request $request
     * @return Response
     */
    public function pushPresence(Request $request) {
        try {
            $this->pusher->trigger("presence-chan", 'my-event', array('message' => 'hello world'));
            $this->pusher->trigger("my-channel", 'my-event', array('message' => 'hello world'));
        } catch (PusherException $e) {

        }

        return new Response("ok");
    }

}