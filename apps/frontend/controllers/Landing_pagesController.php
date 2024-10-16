<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Landing_pagesController
 *
 * Handles the actions for landing pages related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class Landing_pagesController extends Controller
{
    /**
     * @var string
     */
    public $layout = 'full-page';

    /**
     * @var LandingPage|null
     */
    private $page;

    /**
     * View a single page details
     *
     * @param string $id
     * @param string $slug
     *
     * @return void
     * @throws CHttpException
     * @throws Exception
     */
    public function actionView($id, $slug)
    {
        $page = $this->page ?? $this->loadPageModel($id);

        if ($slug !== $page->slug) {
            $this->redirect(['view', 'id' => $id, 'slug' => $page->slug]);
            return;
        }

        $revision = $page->publishedRevision;

        if (empty($revision)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $variant = $revision->pickVariantToShow();

        if (empty($variant)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if (!$page->getIsPublished()) {
            if ((int)customer()->getId() !== (int)$page->customer_id) {
                throw new CHttpException(404, t('app', 'The requested page does not exist.'));
            }
            notify()->addWarning(t('landing_pages', 'This page is unpublished!'));
        }

        $this->setData([
            'pageMetaTitle'       => $this->getData('pageMetaTitle') . ' | ' . $revision->title,
            'pageMetaDescription' => StringHelper::truncateLength($revision->description, 150),
        ]);

        clientScript()->registerLinkTag(
            'canonical',
            null,
            createAbsoluteUrl($this->getRoute(), ['id' => $id, 'slug' => $slug])
        );
        clientScript()->registerLinkTag(
            'shortlink',
            null,
            createAbsoluteUrl($this->getRoute(), ['id' => $id, 'slug' => $slug])
        );

        $ipAddress = FilterVarHelper::ip((string)request()->getUserHostAddress()) ? (string)request()->getUserHostAddress() : null;
        $userAgent = substr((string)request()->getUserAgent(), 0, 255);

        // Like this we don't count opens for an unpublished page. Same logic applies for the clicks
        if ($page->getIsPublished() && !empty($ipAddress)) {
            $this->trackVisit($page, $revision, $variant, $ipAddress, $userAgent);
        }

        $this->render('view', compact('page', 'revision', 'variant'));
    }

    /**
     * Will track the clicks
     *
     * @param string $variant_uid
     * @param string $hash
     * @return void
     * @throws CHttpException
     */
    public function actionTrack_url($variant_uid, $hash)
    {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        $criteria = new CDbCriteria();
        $criteria->compare('variant_id', LandingPageRevisionVariant::decodeHashId((string)$variant_uid));

        /** @var LandingPageRevisionVariant|null $variant */
        $variant = LandingPageRevisionVariant::model()->find($criteria);
        if (empty($variant)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $revision = $variant->revision;
        $page     = $revision->page;

        $customerId = customer()->getId();
        if (!$page->getIsPublished() && empty($customerId)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $publishedVariant = $page->pickPublishedVariant();
        if (empty($publishedVariant)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        if ($publishedVariant->variant_id !== $variant->variant_id) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $hash = str_replace(['.', ' ', '-', '_', '='], '', $hash);
        $hash = substr($hash, 0, 40);

        $url = LandingPageUrl::model()->findByAttributes([
            'variant_id' => $variant->variant_id,
            'hash'       => $hash,
        ]);

        if (empty($url)) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        $url->destination = StringHelper::normalizeUrl($url->destination);

        // In order to stay in line to the fact that we dont count visits in an unpublished page we should not
        // count the clicks, but we should allow the redirect to happen
        if (!$page->getIsPublished()) {
            if (customer()->getId()) {
                $this->redirect($url->destination, true, 301);
            } else {
                throw new CHttpException(404, t('app', 'The requested page does not exist.'));
            }
        }

        // We decide here if to update the counters for the page/revision/variant
        $variantExistingIpAddress = LandingPageTrackUrl::model()->findByAttributes([
            'ip_address' => (string)request()->getUserHostAddress(),
            'url_id'     => (int)$url->url_id,
        ]);

        $pageExistingIpAddress = $revisionExistingIpAddress = null;
        if (empty($variantExistingIpAddress)) {
            // Find all the revision urls that are having the same destination hash as the $url that we want to track
            $criteria              = new CDbCriteria();
            $criteria->with        = [];
            $criteria->with['url'] = [
                'joinType' => 'INNER JOIN',
                'together' => true,
            ];
            $criteria->compare('url.hash', $hash);
            $criteria->compare('url.revision_id', $revision->revision_id);
            $criteria->compare('ip_address', (string)request()->getUserHostAddress());

            $revisionExistingIpAddress = LandingPageTrackUrl::model()->find($criteria);

            if (empty($revisionExistingIpAddress)) {
                // Find all the page urls that are having the same destination hash as the $url that we want to track
                $criteria              = new CDbCriteria();
                $criteria->with        = [];
                $criteria->with['url'] = [
                    'joinType' => 'INNER JOIN',
                    'together' => true,
                ];
                $criteria->compare('url.hash', $hash);
                $criteria->compare('url.page_id', $page->page_id);
                $criteria->compare('ip_address', (string)request()->getUserHostAddress());

                $pageExistingIpAddress = LandingPageTrackUrl::model()->find($criteria);
            }
        }

        $track             = new LandingPageTrackUrl();
        $track->url_id     = (int)$url->url_id;
        $track->ip_address = (string)request()->getUserHostAddress();
        $track->user_agent = substr((string)request()->getUserAgent(), 0, 255);

        try {
            // Trying to track the location also
            $location = IpLocation::findByIp($track->ip_address);
            if (!empty($location)) {
                $track->location_id = (int)$location->location_id;
            }

            $track->save(false);

            // Count the unique conversions for the variant if the ip_address has never been accessing this url
            if (empty($variantExistingIpAddress)) {
                $variantConversionsCountResult = $variant->updateCounters(
                    ['conversions_count' => 1],
                    'variant_id = :vid',
                    [':vid' => (int)$variant->variant_id]
                );
                if (!$variantConversionsCountResult) {
                    Yii::log('Error while trying to update the variant conversions counter', CLogger::LEVEL_ERROR);
                    return;
                }

                // Count the unique conversions for the revision if the ip_address has never been accessing this url
                if (empty($revisionExistingIpAddress)) {
                    $revisionConversionsCountResult = (int)$revision->updateCounters(
                        ['conversions_count' => 1],
                        'revision_id = :rid',
                        [':rid' => (int)$revision->revision_id]
                    );
                    if (!$revisionConversionsCountResult) {
                        Yii::log('Error while trying to update the revision conversions counter', CLogger::LEVEL_ERROR);
                        return;
                    }
                }

                // Count the unique conversions for the page if the ip_address has never been accessing this url
                if (empty($pageExistingIpAddress)) {
                    $pageConversionCountResult = (int)$page->updateCounters(
                        ['conversions_count' => 1],
                        'page_id = :pid',
                        [':pid' => (int)$page->page_id]
                    );
                    if (!$pageConversionCountResult) {
                        Yii::log('Error while trying to update the page conversions counter', CLogger::LEVEL_ERROR);
                        return;
                    }
                }
            }
        } catch (Exception $e) {
        }

        $this->redirect($url->destination, true, 301);
    }

    /**
     * @param LandingPage $page
     * @param LandingPageRevision $revision
     * @param LandingPageRevisionVariant $variant
     * @param string $ipAddress
     * @param string $userAgent
     * @return void
     */
    public function trackVisit(
        LandingPage $page,
        LandingPageRevision $revision,
        LandingPageRevisionVariant $variant,
        string $ipAddress,
        string $userAgent
    ): void {
        try {
            $revisionExistingIpAddress = $pageExistingIpAddress = null;
            $variantExistingIpAddress  = LandingPageTrackVisit::model()->findByAttributes([
                'ip_address'  => $ipAddress,
                'page_id'     => $page->page_id,
                'revision_id' => $revision->revision_id,
                'variant_id'  => (int)$variant->variant_id,
            ]);

            if (empty($variantExistingIpAddress)) {
                $revisionExistingIpAddress = LandingPageTrackVisit::model()->findByAttributes([
                    'ip_address'  => $ipAddress,
                    'page_id'     => $page->page_id,
                    'revision_id' => $revision->revision_id,
                ]);

                if (empty($revisionExistingIpAddress)) {
                    $pageExistingIpAddress = LandingPageTrackVisit::model()->findByAttributes([
                        'ip_address' => $ipAddress,
                        'page_id'    => $page->page_id,
                    ]);
                }
            }

            $track              = new LandingPageTrackVisit();
            $track->page_id     = $page->page_id;
            $track->revision_id = $revision->revision_id;
            $track->variant_id  = (int)$variant->variant_id;
            $track->ip_address  = $ipAddress;
            $track->user_agent  = $userAgent;

            // Trying to track the location also
            $location = IpLocation::findByIp($ipAddress);
            if (!empty($location)) {
                $track->location_id = (int)$location->location_id;
            }

            if (!$track->save()) {
                Yii::log('Cannot save the landing page visit data to db.', CLogger::LEVEL_ERROR);
                return;
            }

            // Count the views
            $pageViewsCountResult = (int)$page->updateCounters(
                ['views_count' => 1],
                'page_id = :pid',
                [':pid' => (int)$page->page_id]
            );
            if (!$pageViewsCountResult) {
                Yii::log('Error while trying to update the page views counter', CLogger::LEVEL_ERROR);
                return;
            }

            $revisionViewsCountResult = (int)$revision->updateCounters(
                ['views_count' => 1],
                'revision_id = :rid',
                [':rid' => (int)$revision->revision_id]
            );
            if (!$revisionViewsCountResult) {
                Yii::log('Error while trying to update the revision views counter', CLogger::LEVEL_ERROR);
                return;
            }

            $variantViewsCountResult = (int)$variant->updateCounters(
                ['views_count' => 1],
                'variant_id = :vid',
                [':vid' => (int)$variant->variant_id]
            );
            if (!$variantViewsCountResult) {
                Yii::log('Error while trying to update the variant views counter', CLogger::LEVEL_ERROR);
                return;
            }

            // We stop if this ip has already visited this variant/revision/page since we want to record only unique counters from now on
            if (!empty($variantExistingIpAddress)) {
                return;
            }

            $variantVisitorsCountResult = $variant->updateCounters(
                ['visitors_count' => 1],
                'variant_id = :vid',
                [':vid' => (int)$variant->variant_id]
            );
            if (!$variantVisitorsCountResult) {
                Yii::log('Error while trying to update the variant unique views counter', CLogger::LEVEL_ERROR);
                return;
            }

            if (!empty($revisionExistingIpAddress)) {
                return;
            }

            $revisionVisitorsCountResult = (int)$revision->updateCounters(
                ['visitors_count' => 1],
                'revision_id = :rid',
                [':rid' => (int)$revision->revision_id]
            );
            if (!$revisionVisitorsCountResult) {
                Yii::log('Error while trying to update the revision unique views counter', CLogger::LEVEL_ERROR);
                return;
            }
            if (!empty($pageExistingIpAddress)) {
                return;
            }

            // Count the visitors - unique views
            $pageVisitorsCountResult = (int)$page->updateCounters(
                ['visitors_count' => 1],
                'page_id = :pid',
                [':pid' => (int)$page->page_id]
            );
            if (!$pageVisitorsCountResult) {
                Yii::log('Error while trying to update the page unique views counter', CLogger::LEVEL_ERROR);
                return;
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param string $id
     * @return LandingPage
     * @throws CHttpException
     */
    public function loadPageModel(string $id): LandingPage
    {
        $model = LandingPage::model()->findByAttributes([
            'page_id' => (int)LandingPage::decodeHashId($id),
        ]);

        if ($model === null) {
            throw new CHttpException(404, t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * @param CAction $action
     *
     * @return bool
     * @throws CException
     */
    protected function beforeAction($action)
    {
        if ($action->getId() === 'view') {
            hooks()->addFilter('content_security_policy_header_policy_directives', function (array $cspDirectives = []): array {
                $this->page = LandingPage::model()->findByAttributes([
                    'page_id' => (int)LandingPage::decodeHashId((string)request()->getQuery('id', '')),
                ]);

                if (empty($this->page)) {
                    return $cspDirectives;
                }

                $isFullGuest  = customer()->getIsGuest() && user()->getIsGuest();
                $isPageOwner  = (int)$this->page->customer_id === (int)customer()->getId();
                $isUsingCname = !empty($this->page->domain) && request()->getHostInfo($this->page->domain->scheme) === $this->page->domain->getDomainNameWithSchema();
                $allowAnyJs   = $isFullGuest || $isPageOwner || $isUsingCname;

                if (!$allowAnyJs) {
                    $cspDirectives['script-src'] = ["'self'"];
                }

                return $cspDirectives;
            });
        }

        return parent::beforeAction($action);
    }
}
