<?php

namespace App\Service;

use Twig\Environment;
use GuzzleHttp\Client;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Api\TransactionalEmailsApi;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BrevoEmailService
{
    private $params;
    private $brevoApiKey;
    private $twig;
    private $urlGenerator;
    private $defaultSenderEmail;
    private $defaultSenderName;

    public function __construct(
        ParameterBagInterface $params,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->params = $params;
        $this->brevoApiKey = $this->params->get('brevo_api_key');
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        // Définir les valeurs par défaut pour l'expéditeur
        $this->defaultSenderEmail = 'nika.mamian@gmail.com';
        $this->defaultSenderName = 'Brevo';
    }

    public function mailSender($email, $name, $activationToken)
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $this->brevoApiKey);
        $apiInstance = new TransactionalEmailsApi(new Client(), $config);

        $activationUrl = $this->urlGenerator->generate('app_activate_account', [
            'token' => $activationToken
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $sendSmtpEmail = new SendSmtpEmail([
            'subject' => 'Welcome to FROOVE',
            'sender' => [
                'name' => $this->defaultSenderName,
                'email' => $this->defaultSenderEmail,
            ],
            'replyTo' => [
                'name' => $this->defaultSenderName,
                'email' => $this->defaultSenderEmail,
            ],
            'to' => [['name' => $name, 'email' => $email]],
            'htmlContent' => $this->twig->render('emails/welcome.html.twig', [
                'name' => $name,
                'activationUrl' =>$activationUrl,
            ]),
            'params' => ['bodyMessage' => 'made just for you!']
        ]);

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            print_r($result);
        } catch (\Exception $e) {
            echo 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ', $e->getMessage(), PHP_EOL;
        }
    }
}