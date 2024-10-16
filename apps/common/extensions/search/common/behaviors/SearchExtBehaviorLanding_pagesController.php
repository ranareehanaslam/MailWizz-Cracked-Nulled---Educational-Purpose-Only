<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class SearchExtBehaviorLanding_pagesController extends SearchExtBaseBehavior
{
    /**
     * @return array
     */
    public function searchableActions(): array
    {
        return [
            'index' => [
                'title'     => t('landing_pages', 'Landing pages'),
                'keywords'  => [
                    'landing pages', 'landing', 'pages',
                ],
                'skip'              => [$this, '_indexSkip'],
                'childrenGenerator' => [$this, '_indexChildrenGenerator'],
            ],
            'create' => [
                'keywords'  => ['landing page', 'create landing page', 'landing page create'],
                'skip'      => [$this, '_createSkip'],
            ],
        ];
    }

    /**
     * @param SearchExtSearchItem $item
     *
     * @return bool
     */
    public function _indexSkip(SearchExtSearchItem $item)
    {
        if (apps()->isAppName('customer')) {
            if (is_subaccount() && !subaccount()->canManageLandingPages()) {
                return true;
            }

            return false;
        }

        /** @var User $user */
        $user = user()->getModel();
        return !$user->hasRouteAccess($item->route);
    }

    /**
     * @param string $term
     * @param SearchExtSearchItem|null $parent
     *
     * @return array
     */
    public function _indexChildrenGenerator(string $term, ?SearchExtSearchItem $parent = null): array
    {
        $criteria = new CDbCriteria();

        if (apps()->isAppName('customer')) {
            $criteria->addCondition('page.customer_id = :cid');
            $criteria->params[':cid'] = (int)customer()->getId();
            $criteria->with = [];
            $criteria->with['page'] = [
                'joinType' => 'INNER JOIN',
                'together' => true,
            ];
        }

        $criteria->addCondition('(t.title LIKE :term OR t.description LIKE :term)');
        $criteria->params[':term'] = '%' . $term . '%';
        $criteria->order = 't.page_id DESC';
        $criteria->limit = 5;
        $criteria->group = 't.page_id';

        return LandingPageRevisionCollection::findAll($criteria)->map(function (LandingPageRevision $model) {
            $item        = new SearchExtSearchItem();
            $item->title = $model->title;
            $item->url   = createUrl('landing_pages/overview', ['id' => $model->page->getHashId()]);
            $item->score++;

            if (apps()->isAppName('customer')) {
                $item->buttons = [
                    CHtml::link(IconHelper::make('fa-dashboard'), ['landing_pages/overview', 'id' => $model->page->getHashId()], ['title' => t('landing_pages', 'Overview'), 'class' => 'btn btn-xs btn-primary btn-flat']),
                ];
            }

            return $item->getFields();
        })->all();
    }

    /**
     * @return bool
     */
    public function _createSkip(): bool
    {
        if (apps()->isAppName('customer')) {
            /** @var Customer $customer */
            $customer = customer()->getModel();
            return (int)$customer->getGroupOption('landing_pages.max_landing_pages', -1) == 0;
        }

        return true;
    }
}
