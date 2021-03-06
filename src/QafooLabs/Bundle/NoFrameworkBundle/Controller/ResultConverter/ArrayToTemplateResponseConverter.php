<?php

namespace QafooLabs\Bundle\NoFrameworkBundle\Controller\ResultConverter;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use QafooLabs\Bundle\NoFrameworkBundle\View\TemplateGuesser;
use QafooLabs\MVC\TemplateView;
use Twig\Environment;

/**
 * Convert array or {@link TemplateView} struct into templated response.
 *
 * Guess the template names with the same algorithm that @Template()
 * in Sensio's FrameworkExtraBundle uses.
*/
class ArrayToTemplateResponseConverter implements ControllerResultConverter
{
    private $twig;
    private $guesser;
    private $engine;

    public function __construct(Environment $twig, TemplateGuesser $guesser, string $engine)
    {
        $this->twig = $twig;
        $this->guesser = $guesser;
        $this->engine = $engine;
    }

    public function supports($result) : bool
    {
        return is_array($result) || $result instanceof TemplateView;
    }

    public function convert($result, Request $request) : Response
    {
        $controller = $request->attributes->get('_controller');

        if ( ! ($result instanceof TemplateView)) {
            $result = new TemplateView($result);
        }

        return $this->makeResponseFor(
            $controller,
            $result,
            $request->getRequestFormat()
        );
    }

    private function makeResponseFor($controller, TemplateView $templateView, $requestFormat) : Response
    {
        $viewName = $this->guesser->guessControllerTemplateName(
            $controller,
            $templateView->getActionTemplateName(),
            $requestFormat,
            $this->engine
        );

        return new Response(
            $this->twig->render($viewName, $templateView->getViewParams()),
            $templateView->getStatusCode(),
            $templateView->getHeaders()
        );
    }
}
