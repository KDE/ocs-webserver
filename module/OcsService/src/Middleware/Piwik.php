<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OcsService\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Mvc\View\Http\ViewManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Psr\Http\Message\ServerRequestInterface;

class Piwik implements MiddlewareInterface
{

    /**
     * @var PhpRenderer
     */
    private $renderer;
    /**
     * @var ViewManager
     */
    private $view;
    private $app_config;

    public function __construct(
        PhpRenderer $renderer,
        ViewManager $view,
        array $config
    ) {
        $this->renderer = $renderer;
        $this->view = $view;
        $this->app_config = $config;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $project_id = (int)$request->getAttribute('project_id');

        if (false == $this->app_config['experimental']['piwik']['stats_widget']['enabled']) {
            return new HtmlResponse('');
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('ocsservice/piwik/piwik');
        $viewModel->setVariable('project_id', $project_id);

//        $layout = $this->view->getViewModel();
//        $layout->setVariable(
//            'content',
//            $this->renderer->render($viewModel)
//        );

        return new HtmlResponse($this->renderer->render($viewModel));

//        $response = $this->response->withStatus(200);
//        $response->getBody()->write(
//            $this->renderer->render('error::404')
//        );
//
//        return $response;
    }

}