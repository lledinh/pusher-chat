<?php

namespace App\Controller;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Pusher\Pusher;
use Pusher\PusherException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class LoginController extends AbstractController
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


    public function checkMail($email, & $error)
    {
        $pos = strpos($email, "@");

        if ($pos === false || $pos == 0) {
            $error = "Incorrect email format.";
            return false;
        } else {
            return true;
        }
    }

    public function checkPassword($password, & $error)
    {

        if (strlen($password) < 8) {
            $error = "Password character count must be greater than 8.";
            return false;
        } else {
            return true;
        }
    }

    public function checkRepeatPassword($password, $repeatPassword, & $error)
    {
        if ($password != $repeatPassword) {
            $error = "Not the same password.";
            return false;
        } else {
            return true;
        }
    }

    public function checkName($name, & $error)
    {
        if (strlen($name) < 3) {
            $error = "Name character count must be greater than 3.";
            return false;
        }
        return true;
    }

    /**
     * @Route("/login_check", name="login_check")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function checkLogin(Request $request)
    {
        $typeAction = $request->request->get('rg');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $repeatPassword = $request->request->get('repeat_password');
        $name = $request->request->get('name');

        if ($typeAction == "sign-up") {
            $errorEmail = "";
            $errorPassword = "";
            $errorRepeatPassword = "";
            $errorName = "";
            $mailOk = $this->checkMail($email, $errorEmail);
            $passwordOk = $this->checkPassword($password, $errorPassword);
            $repeatPasswordOk = $this->checkRepeatPassword($password, $repeatPassword, $errorRepeatPassword);
            $nameOk = $this->checkName($name, $errorName);

            if ($mailOk && $passwordOk && $repeatPasswordOk && $nameOk) {
                $entityManager = $this->getDoctrine()->getManager();

                $user = new User();
                $user->setMail($email);
                $user->setName($name);
                $user->setPassword($password);
                $user->setIsDeleted(0);
                $entityManager->persist($user);
                $entityManager->flush();
            }

            return $this->render('login.html.twig', [
                'errorEmail' => $errorEmail,
                'errorPassword' => $errorPassword,
                'errorRepeatPassword' => $errorRepeatPassword,
                'errorName' => $errorName
            ]);
        }

        if ($typeAction == "sign-in") {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy([
                    'mail' => $email,
                    'password' => $password
                ]);

            if (!empty($user)) {
                $this->session->set('user', $user);
                $cookie = new Cookie("user_name", $user->getName(), time()+86400, '/', null, false, false);

                $response = $this->redirectToRoute('index');
                $response->headers->setCookie($cookie);

                return $response;
            }
        }

        if ($typeAction == "reset") {

        }

        return $this->render('login.html.twig', [
            'errorEmail' => "",
            'errorPassword' => "",
            'errorRepeatPassword' => "",
            'errorName' => ""
        ]);
    }

    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @return Response
     */
    public function loginForm(Request $request)
    {
        return $this->render('login.html.twig', [
            'errorEmail' => "",
            'errorPassword' => "",
            'errorRepeatPassword' => "",
            'errorName' => ""
        ]);

    }

}
