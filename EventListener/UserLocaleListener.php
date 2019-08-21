<?php
// src/NvCarga/Bundle/EventListener/UserLocaleListener.php
namespace NvCarga\Bundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * Stores the locale of the user in the session after the
 * login. This can be used by the LocaleListener afterwards.
 */
class UserLocaleListener
{
    /**
     * @var Session
     */
    private $session;
    
    /**
     * @var Swift_Transport_EsmtpTransport
     */
    //private $transport;
    

    public function __construct(Session $session)
    {
        $this->session = $session;
        // $this->transport = $transport;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $maincompany = $user->getMaincompany();
        $mylocale = $maincompany->getAcronym();
        if (null !== $mylocale) {
            $this->session->set('_locale', $mylocale);
        }
        /*
        $mailparams = $maincompany->getMailparams();
        if ($mailparams) {
            if ($mailparams->getUser()) {
                $this->transport->setUserName($mailparams->getUser());
            }
            if ($mailparams->getPassword()) {
                $this->transport->setPassword($mailparams->getPassword());
            }
            if ($mailparams->getHost()) {
                $this->transport->setHost($mailparams->getHost());
            }
            if ($mailparams->getPort()) {
                $this->transport->setPort($mailparams->getPort());
            }
            if ($mailparams->getEncryption()) {
                $this->transport->setEncryption($mailparams->getEncryption());
            }
            
        }
        */
    }

    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => array(array('onInteractiveLogin', 15)),
        );
    }
}
?>
