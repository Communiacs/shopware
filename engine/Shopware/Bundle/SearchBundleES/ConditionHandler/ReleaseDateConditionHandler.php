<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\SearchBundleES\ConditionHandler;

use ONGR\ElasticsearchDSL\Query\RangeQuery;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Condition\ReleaseDateCondition;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ReleaseDateConditionHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return ($criteriaPart instanceof ReleaseDateCondition);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        /**@var ReleaseDateCondition $criteriaPart */
        $date = new \DateTime();
        $intervalSpec = 'P' . $criteriaPart->getDays() . 'D';
        $interval = new \DateInterval($intervalSpec);

        $dateNow = new\DateTime();

        switch ($criteriaPart->getDirection()) {
            case ReleaseDateCondition::DIRECTION_FUTURE:
                $date->add($interval);
                $range = [
                    'lte' => $date->format('Y-m-d'),
                    'gt' => $dateNow->format('Y-m-d')
                ];
                break;

            case ReleaseDateCondition::DIRECTION_PAST:
                $date->sub($interval);
                $range = [
                    'gte' => $date->format('Y-m-d'),
                    'lte' => $dateNow->format('Y-m-d'),
                ];
                break;

            default:
                return;
        }

        $filter = new RangeQuery('formattedReleaseDate', $range);

        if ($criteria->hasBaseCondition($criteriaPart->getName())) {
            $search->addFilter($filter);
        } else {
            $search->addPostFilter($filter);
        }
    }
}
