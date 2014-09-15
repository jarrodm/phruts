<?php

namespace Phruts\Actions;

use Phruts\Config\ForwardConfig;

class ForwardAction extends \Phruts\Action {
    public function execute(
        \Phruts\Config\ActionConfig $mapping,
        $form,
        \Symfony\Component\HttpFoundation\Request $request,
        \Symfony\Component\HttpFoundation\Response $response
    ) {

        // Action Config defines the parameter for the forward configuration
        $parameter = $mapping->getParameter();
        if(empty($parameter)) {
            throw new \Phruts\Exception\IllegalArgumentException('Need to specify a parameter for this ForwardAction');
        }

        // Original strategy, let's assume it is a path
        if(!preg_match('/^[A-z]+$/', $parameter)) {
            $forward = new ForwardConfig();
            $forward->setPath($parameter);
            $forward->setContextRelative(true);
            return $forward;
        } else {
            // This implementation of forward action is slightly different that the original
            // where the forward config must be defined and the key passed as parameter
            // so that nextAction/action chaining is implemented
            // TODO: Create an action chaining forward path
            $forward = $mapping->findForwardConfig($parameter);
            if(empty($forward)) {
                throw new \Phruts\Exception('ForwardAction parameter should reference a forward config name');
            }
            return $forward;
        }
    }
}
