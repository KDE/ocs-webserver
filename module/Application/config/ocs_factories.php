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


use Laminas\Authentication\AuthenticationService;

return [
    'Application\Model\Factory\CacheFactory' => Application\Model\Factory\CacheFactory::class,

    /*
    * ======================== Service Factories =============================================
    */
    AuthenticationService::class => Application\Model\Service\Factory\AuthenticationServiceFactory::class,

    Application\Model\Service\AclService::class                    => Application\Model\Service\Factory\AclServiceFactory::class,
    Application\Model\Service\ActivityLogService::class            => Application\Model\Service\Factory\ActivityLogServiceFactory::class,
    Application\Model\Service\AuthAdapter::class                   => Application\Model\Service\Factory\AuthAdapterFactory::class,
    Application\Model\Service\AuthManager::class                   => Application\Model\Service\Factory\AuthManagerFactory::class,
    Application\Model\Service\RegisterManager::class               => Application\Model\Service\Factory\RegisterManagerFactory::class,
    Application\Model\Service\AvatarService::class                 => Application\Model\Service\Factory\AvatarServiceFactory::class,
    Application\Model\Service\BbcodeService::class                 => Application\Model\Service\Factory\BbcodeServiceFactory::class,
    Application\Model\Service\CollectionService::class             => Application\Model\Service\Factory\CollectionServiceFactory::class,
    Application\Model\Service\CurrentStoreReader::class            => Application\Model\Service\Factory\CurrentStoreReaderFactory::class,
    Application\Model\Service\CurrentUser::class                   => Application\Model\Service\Factory\CurrentUserFactory::class,
    Application\Model\Service\DwhdataService::class                => Application\Model\Service\Factory\DwhdataServiceFactory::class,
    Application\Model\Service\HtmlPurifyService::class             => Application\Model\Service\Factory\HtmlPurifyServiceFactory::class,
    Application\Model\Service\InfoService::class                   => Application\Model\Service\Factory\InfoServiceFactory::class,
    Application\Model\Service\EmailBuilder::class                  => Application\Model\Service\Factory\EmailBuilderFactory::class,
    Application\Model\Service\EmailBuilderFileTemplate::class      => Application\Model\Service\Factory\EmailBuilderFileTemplateFactory::class,
    Application\Model\Service\LoginHistoryService::class           => Application\Model\Service\Factory\LoginHistoryServiceFactory::class,
    Application\Model\Service\Mailer::class                        => Application\Model\Service\Factory\MailerFactory::class,
    Application\Model\Service\MemberDeactivationLogService::class  => Application\Model\Service\Factory\MemberDeactivationLogServiceFactory::class,
    Application\Model\Service\MemberEmailService::class            => Application\Model\Service\Factory\MemberEmailServiceFactory::class,
    Application\Model\Service\MemberService::class                 => Application\Model\Service\Factory\MemberServiceFactory::class,
    Application\Model\Service\MemberSettingValueService::class     => Application\Model\Service\Factory\MemberSettingValueServiceFactory::class,
    Application\Model\Service\PploadService::class                 => Application\Model\Service\Factory\PploadServiceFactory::class,
    Application\Model\Service\ProjectCategoryService::class        => Application\Model\Service\Factory\ProjectCategoryServiceFactory::class,
    Application\Model\Service\ProjectCloneService::class           => Application\Model\Service\Factory\ProjectCloneServiceFactory::class,
    Application\Model\Service\ProjectCommentsService::class        => Application\Model\Service\Factory\ProjectCommentsServiceFactory::class,
    Application\Model\Service\ProjectModerationService::class      => Application\Model\Service\Factory\ProjectModerationServiceFactory::class,
    Application\Model\Service\ProjectPlingsService::class          => Application\Model\Service\Factory\ProjectPlingsServiceFactory::class,
    Application\Model\Service\ProjectService::class                => Application\Model\Service\Factory\ProjectServiceFactory::class,
    Application\Model\Service\ProjectTagRatingsService::class      => Application\Model\Service\Factory\ProjectTagRatingsServiceFactory::class,
    Application\Model\Service\ProjectUpdatesService::class         => Application\Model\Service\Factory\ProjectUpdatesServiceFactory::class,
    Application\Model\Service\ReviewProfileDataService::class      => Application\Model\Service\Factory\ReviewProfileDataServiceFactory::class,
    Application\Model\Service\SectionService::class                => Application\Model\Service\Factory\SectionServiceFactory::class,
    Application\Model\Service\SectionSupportService::class         => Application\Model\Service\Factory\SectionSupportServiceFactory::class,
    Application\Model\Service\SolrService::class                   => Application\Model\Service\Factory\SolrServiceFactory::class,
    Application\Model\Service\SpamService::class                   => Application\Model\Service\Factory\SpamServiceFactory::class,
    Application\Model\Service\StatDownloadService::class           => Application\Model\Service\Factory\StatDownloadServiceFactory::class,
    Application\Model\Service\StoreService::class                  => Application\Model\Service\Factory\StoreServiceFactory::class,
    Application\Model\Service\StoreTemplateReader::class           => Application\Model\Service\Factory\StoreTemplateReaderFactory::class,
    Application\Model\Service\TagGroupService::class               => Application\Model\Service\Factory\TagGroupServiceFactory::class,
    Application\Model\Service\TagService::class                    => Application\Model\Service\Factory\TagServiceFactory::class,
    Application\Model\Service\ViewsService::class                  => Application\Model\Service\Factory\ViewsServiceFactory::class,
    Application\Model\Service\Ocs\HttpTransport\OAuthServer::class => Application\Model\Service\Ocs\HttpTransport\Factory\OAuthServerFactory::class,
    Application\Model\Service\Ocs\OAuth::class                     => Application\Model\Service\Ocs\Factory\OAuthFactory::class,
    Application\Model\Service\Ocs\Ldap::class                      => Application\Model\Service\Ocs\Factory\LdapFactory::class,
    Application\Model\Service\WebsiteOwnerService::class           => Application\Model\Service\Factory\WebsiteOwnerServiceFactory::class,
    Application\Model\Service\Ocs\Gitlab::class                    => Application\Model\Service\Ocs\Factory\GitlabFactory::class,
    Application\Model\Service\Ocs\Forum::class                     => Application\Model\Service\Ocs\Factory\ForumFactory::class,
    Application\Model\Service\Ocs\Matrix::class                    => Application\Model\Service\Ocs\Factory\MatrixFactory::class,
    Application\Model\Service\Ocs\Mastodon::class                  => Application\Model\Service\Ocs\Factory\MastodonFactory::class,
    Application\Model\Service\Verification\WebsiteProject::class   => Application\Model\Service\Verification\Factory\WebsiteProjectFactory::class,
    Application\Model\Service\Ocs\ServerManager::class             => Application\Model\Service\Ocs\Factory\ServerManagerFactory::class,

    /*
    * ======================== Repository Factories =============================================
    */
    Application\Model\Repository\BaseRepository::class             => Application\Model\Repository\Factory\BaseRepositoryFactory::class,

    Application\Model\Repository\ActivityLogRepository::class           => Application\Model\Repository\Factory\ActivityLogRepositoryFactory::class,
    Application\Model\Repository\ActivityLogTypesRepository::class      => Application\Model\Repository\Factory\ActivityLogTypesRepositoryFactory::class,
    Application\Model\Repository\AuthenticationRepository::class        => Application\Model\Repository\Factory\AuthenticationRepositoryFactory::class,
    Application\Model\Repository\BrowseListTypesRepository::class       => Application\Model\Repository\Factory\BrowseListTypesRepositoryFactory::class,
    Application\Model\Repository\CollectionProjectsRepository::class    => Application\Model\Repository\Factory\CollectionProjectsRepositoryFactory::class,
    Application\Model\Repository\CommentsRepository::class              => Application\Model\Repository\Factory\CommentsRepositoryFactory::class,
    Application\Model\Repository\ConfigOperatingSystemRepository::class => Application\Model\Repository\Factory\ConfigOperatingSystemRepositoryFactory::class,
    Application\Model\Repository\ConfigStoreCategoryRepository::class   => Application\Model\Repository\Factory\ConfigStoreCategoryRepositoryFactory::class,
    Application\Model\Repository\ConfigStoreCategoryTagRepository::class   => Application\Model\Repository\Factory\ConfigStoreCategoryTagRepositoryFactory::class,
    Application\Model\Repository\ConfigStoreRepository::class           => Application\Model\Repository\Factory\ConfigStoreRepositoryFactory::class,
    Application\Model\Repository\ConfigStoreTagRepository::class        => Application\Model\Repository\Factory\ConfigStoreTagRepositoryFactory::class,
    Application\Model\Repository\HiveContentCategoryRepository::class   => Application\Model\Repository\Factory\HiveContentCategoryRepositoryFactory::class,
    Application\Model\Repository\HiveContentRepository::class           => Application\Model\Repository\Factory\HiveContentRepositoryFactory::class,
    Application\Model\Repository\ImageRepository::class                 => Application\Model\Repository\Factory\ImageRepositoryFactory::class,
    Application\Model\Repository\LoginHistoryRepository::class          => Application\Model\Repository\Factory\LoginHistoryRepositoryFactory::class,
    Application\Model\Repository\MailTemplateRepository::class          => Application\Model\Repository\Factory\MailTemplateRepositoryFactory::class,
    Application\Model\Repository\MediaViewsRepository::class            => Application\Model\Repository\Factory\MediaViewsRepositoryFactory::class,
    Application\Model\Repository\MemberDeactivationLogRepository::class => Application\Model\Repository\Factory\MemberDeactivationLogRepositoryFactory::class,
    Application\Model\Repository\MemberDownloadHistoryRepository::class => Application\Model\Repository\Factory\MemberDownloadHistoryRepositoryFactory::class,
    Application\Model\Repository\MemberEmailRepository::class           => Application\Model\Repository\Factory\MemberEmailRepositoryFactory::class,
    Application\Model\Repository\MemberExternalIdRepository::class      => Application\Model\Repository\Factory\MemberExternalIdRepositoryFactory::class,
    Application\Model\Repository\MemberPayoutRepository::class          => Application\Model\Repository\Factory\MemberPayoutRepositoryFactory::class,
    Application\Model\Repository\MemberPaypalAddressRepository::class   => Application\Model\Repository\Factory\MemberPaypalAddressRepositoryFactory::class,
    Application\Model\Repository\MemberRepository::class                => Application\Model\Repository\Factory\MemberRepositoryFactory::class,
    Application\Model\Repository\MemberRoleRepository::class            => Application\Model\Repository\Factory\MemberRoleRepositoryFactory::class,
    Application\Model\Repository\MemberScoreRepository::class           => Application\Model\Repository\Factory\MemberScoreRepositoryFactory::class,
    Application\Model\Repository\MemberSettingGroupRepository::class    => Application\Model\Repository\Factory\MemberSettingGroupRepositoryFactory::class,
    Application\Model\Repository\MemberSettingItemRepository::class     => Application\Model\Repository\Factory\MemberSettingItemRepositoryFactory::class,
    Application\Model\Repository\MemberSettingValueRepository::class    => Application\Model\Repository\Factory\MemberSettingValueRepositoryFactory::class,
    Application\Model\Repository\MemberTokenRepository::class           => Application\Model\Repository\Factory\MemberTokenRepositoryFactory::class,
    Application\Model\Repository\ProjectCategoryRepository::class       => Application\Model\Repository\Factory\ProjectCategoryRepositoryFactory::class,
    Application\Model\Repository\ProjectFollowerRepository::class       => Application\Model\Repository\Factory\ProjectFollowerRepositoryFactory::class,
    Application\Model\Repository\ProjectPlingsRepository::class         => Application\Model\Repository\Factory\ProjectPlingsRepositoryFactory::class,
    Application\Model\Repository\ProjectRatingRepository::class         => Application\Model\Repository\Factory\ProjectRatingRepositoryFactory::class,
    Application\Model\Repository\ProjectRepository::class               => Application\Model\Repository\Factory\ProjectRepositoryFactory::class,
    Application\Model\Repository\ProjectSubCategoryRepository::class    => Application\Model\Repository\Factory\ProjectSubCategoryRepositoryFactory::class,
    Application\Model\Repository\ProjectUpdatesRepository::class        => Application\Model\Repository\Factory\ProjectUpdatesRepositoryFactory::class,
    Application\Model\Repository\ProjectWidgetDefaultRepository::class  => Application\Model\Repository\Factory\ProjectWidgetDefaultRepositoryFactory::class,
    Application\Model\Repository\ProjectWidgetRepository::class         => Application\Model\Repository\Factory\ProjectWidgetRepositoryFactory::class,
    Application\Model\Repository\ProjectGalleryPictureRepository::class => Application\Model\Repository\Factory\ProjectGalleryPictureRepositoryFactory::class,
    Application\Model\Repository\ProjectModerationRepository::class     => Application\Model\Repository\Factory\ProjectModerationRepositoryFactory::class,
    Application\Model\Repository\ProjectCcLicenseRepository::class      => Application\Model\Repository\Factory\ProjectCcLicenseRepositoryFactory::class,
    Application\Model\Repository\ProjectCloneRepository::class          => Application\Model\Repository\Factory\ProjectCloneRepositoryFactory::class,
    Application\Model\Repository\PayoutStatusRepository::class          => Application\Model\Repository\Factory\PayoutStatusRepositoryFactory::class,
    Application\Model\Repository\PaypalIpnRepository::class             => Application\Model\Repository\Factory\PaypalIpnRepositoryFactory::class,
    Application\Model\Repository\PaypalValidStatusRepository::class     => Application\Model\Repository\Factory\PaypalValidStatusRepositoryFactory::class,
    Application\Model\Repository\PploadCollectionsRepository::class     => Application\Model\Repository\Factory\PploadCollectionsRepositoryFactory::class,
    Application\Model\Repository\PploadFilesRepository::class           => Application\Model\Repository\Factory\PploadFilesRepositoryFactory::class,
    Application\Model\Repository\ReportCommentsRepository::class        => Application\Model\Repository\Factory\ReportCommentsRepositoryFactory::class,
    Application\Model\Repository\ReportProductsRepository::class        => Application\Model\Repository\Factory\ReportProductsRepositoryFactory::class,
    Application\Model\Repository\SectionCategoryRepository::class       => Application\Model\Repository\Factory\SectionCategoryRepositoryFactory::class,
    Application\Model\Repository\SectionRepository::class               => Application\Model\Repository\Factory\SectionRepositoryFactory::class,
    Application\Model\Repository\SectionSupportRepository::class        => Application\Model\Repository\Factory\SectionSupportRepositoryFactory::class,
    Application\Model\Repository\SessionRepository::class               => Application\Model\Repository\Factory\SessionRepositoryFactory::class,
    Application\Model\Repository\SpamKeywordsRepository::class          => Application\Model\Repository\Factory\SpamKeywordsRepositoryFactory::class,
    Application\Model\Repository\SponsorRepository::class               => Application\Model\Repository\Factory\SponsorRepositoryFactory::class,
    Application\Model\Repository\StatPageViewsRepository::class         => Application\Model\Repository\Factory\StatPageViewsRepositoryFactory::class,
    Application\Model\Repository\SupportRepository::class               => Application\Model\Repository\Factory\SupportRepositoryFactory::class,
    Application\Model\Repository\SuspicionLogRepository::class          => Application\Model\Repository\Factory\SuspicionLogRepositoryFactory::class,
    Application\Model\Repository\TagGroupItemRepository::class          => Application\Model\Repository\Factory\TagGroupItemRepositoryFactory::class,
    Application\Model\Repository\TagGroupRepository::class              => Application\Model\Repository\Factory\TagGroupRepositoryFactory::class,
    Application\Model\Repository\TagRatingRepository::class             => Application\Model\Repository\Factory\TagRatingRepositoryFactory::class,
    Application\Model\Repository\TagsRepository::class                  => Application\Model\Repository\Factory\TagsRepositoryFactory::class,
    Application\Model\Repository\TagTypeRepository::class               => Application\Model\Repository\Factory\TagTypeRepositoryFactory::class,
    Application\Model\Repository\TagObjectRepository::class             => Application\Model\Repository\Factory\TagObjectRepositoryFactory::class,
    Application\Model\Repository\VideoRepository::class                 => Application\Model\Repository\Factory\VideoRepositoryFactory::class,
];
