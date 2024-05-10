<?php

namespace App\Controller;

use App\Service\CurrencyConverterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;


class CurrencyConversionController extends AbstractController
{
    #[Route('/currency-conversion', name: 'app_currency_conversion', methods: ['POST'])]
    public function convertCurrency(Request $request, SessionInterface $session): Response
    {
        $currency = $request->request->get('currency');

        $session->set('currency', $currency);

        $referrer = $request->headers->get('referer');

        return new RedirectResponse($referrer);
    }
}
