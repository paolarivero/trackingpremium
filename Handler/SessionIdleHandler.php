<?php
namespace NvCarga\Bundle\Handler;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SessionIdleHandler
{

    protected $session;
    protected $securityContext;
    protected $router;
    protected $maxIdleTime;

    public function __construct(SessionInterface $session, SecurityContextInterface $securityContext, RouterInterface $router, $maxIdleTime = 0)
    {
        $this->session = $session;
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->maxIdleTime = $maxIdleTime;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        if ($this->maxIdleTime > 0) {
            $this->session->start();
            $lastused = $this->session->getMetadataBag()->getLastUsed();
            $lapse = time() - $lastused;
            // $isFullyAuthenticated = $this->securityContext->isGranted('IS_AUTHENTICATED_FULLY'); && $isFullyAuthenticated == true

            if ($lapse > $this->maxIdleTime) {
                $this->securityContext->setToken(null);
                $message = 'Se cerró la sesión por inactividad durante '. $this->maxIdleTime .'seg'; 
                $this->session->getFlashBag()->set('info', $message);

                //$event->setResponse(new RedirectResponse($this->router->generate('admin_user')));
                $event->setResponse(new RedirectResponse($this->router->generate('login')));
            }
        }
    }
}
?>
