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

namespace Application\Form\Factory;

use Application\Form\ProductForm;
use Application\Model\Repository\ImageRepository;
use Application\Model\Repository\MemberExternalIdRepository;
use Application\Model\Repository\ProjectCategoryRepository;
use Application\Model\Repository\TagsRepository;
use Application\Model\Service\MemberService;
use Application\Model\Service\Ocs\Gitlab;
use Application\Model\Service\ProjectCategoryService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class ProductFormFactory
 *
 * @package Application\Form\Factory
 */
class ProductFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $imageRepository = $container->get(ImageRepository::class);
        $tagsRepository = $container->get(TagsRepository::class);
        $gitlabService = $container->get(Gitlab::class);
        $memberService = $container->get(MemberService::class);
        $memberExternalIdRepository = $container->get(MemberExternalIdRepository::class);
        $projectCategoryService = $container->get(ProjectCategoryService::class);
        $projectCategoryRepository = $container->get(ProjectCategoryRepository::class);

        return new ProductForm($imageRepository, $tagsRepository, $gitlabService, $memberService, $memberExternalIdRepository, $projectCategoryService, $projectCategoryRepository);
    }
}
