<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestSubscriber implements EventSubscriberInterface
{

  function __construct()
  {
    $this->locale_array = ["de", "en", "es", "fr", "it", "pl", "pt"];
  }

  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::REQUEST => [["onKernelRequest", 20]],
    ];
  }

  public function onKernelRequest(RequestEvent $event)
  {
    
    $request = $event->getRequest();
    $session = $request->getSession();

    if(substr($request->attributes->get('_route'), 0, 3) == "app" || $request->attributes->get('_controller') == "error_controller"){
      $uri = explode('/', $request->getRequestUri());
      if(!in_array($uri[1], $this->locale_array)){
        $locale = 'en';
        if (array_key_exists("HTTP_ACCEPT_LANGUAGE", $_SERVER)) {
          $l = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2);
          if(in_array($l, $this->locale_array)){
            $locale = $l;
          }
        }
        return $event->setResponse(new RedirectResponse('/' . $locale . $request->getRequestUri()));
      }
    }
    

    if(!$session->get('views')){
      $session->set('views', []);
    }
    
  }
}