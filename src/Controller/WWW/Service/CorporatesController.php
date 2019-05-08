<?php

declare(strict_types=1);

namespace App\Controller\WWW\Service;

use App\Utils\FormUtil;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Konstantin Grachev <me@grachevko.ru>
 */
final class CorporatesController extends AbstractController
{
    /**
     * @Route("/corporates", name="corporates")
     */
    public function __invoke(Request $request, Swift_Mailer $mailer): Response
    {
        $form = $this->createFormBuilder()
            ->add('name')
            ->add('telephone')
            ->add('checkbox', CheckboxType::class)
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = (object) $form->getData();

            $message = (new Swift_Message())
                ->setFrom(['no-reply@automagistre.ru' => 'Автомагистр'])
                ->setTo(['info@automagistre.ru'])
                ->setSubject('Запись на корпоративное обслуживание')
                ->setBody(<<<TEXT
                    Имя: {$data->name}
                    Телефон: {$data->telephone}
                    TEXT
                );

            $mailer->send($message);

            return new Response('OK');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->json([
                'error' => FormUtil::getErrorMessages($form),
            ]);
        }

        return $this->render('www/corporates.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
