<?php

namespace App\Controller;

use App\Services\ChatChannelCounter;
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
    private $channelCounter;

    public function __construct(SessionInterface $session, LoggerInterface $logger, ChatChannelCounter $channelCounter)
    {
        $this->session = $session;
        $this->logger = $logger;
        $this->channelCounter = $channelCounter;
        try {
            $this->pusher = new Pusher("f63a595c360996836b72", "c0f550d0afeef0acdf9c", "873980", array('cluster' => 'eu'));
        } catch (PusherException $e) {
        }
    }

    /**
     * @Route("/salon", name="salon")
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

        $channels = $this->channelCounter->getDefaultsChannels();
        $channelsCount = $this->channelCounter->getChannelsCounts();

        return $this->render('salon.html.twig', [
            'channels' => $channels,
            'channelsCount' => $channelsCount
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
     * @Route("/connect-channel", name="connect-channel")
     * @param Request $request
     * @return Response
     */
    public function connect(Request $request) {

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