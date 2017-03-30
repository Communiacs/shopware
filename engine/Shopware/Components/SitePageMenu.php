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

namespace Shopware\Components;

use Doctrine\DBAL\Connection;

/**
 * Class SitePageMenu
 * @package Shopware\Components
 */
class SitePageMenu
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns a shop page tree for the provided shop id.
     *
     * @param $shopId
     * @param $activeId
     * @return array
     */
    public function getTree($shopId, $activeId)
    {
        $query = $this->getQuery($shopId);

        /**@var $statement \PDOStatement*/
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $menu = [];
        foreach ($data as $site) {
            $key = !empty($site['mapping']) ? $site['mapping'] : $site['group'];

            if ($this->overrideExisting($menu, $key, $site)) {
                $menu[$key] = [];
            }

            $menu[$key][] = $site;
        }

        $result = [];
        foreach ($menu as $key => $group) {
            $sites = $this->buildSiteTree(0, $group, $activeId);
            $result[$key] = $sites;
        }

        return $result;
    }

    /**
     * @param $parentId
     * @param $sites
     * @param $activeId
     * @return array
     */
    private function buildSiteTree($parentId, $sites, $activeId)
    {
        $result = [];
        foreach ($sites as $index => $site) {
            $site['active'] = ($site['id'] == $activeId);

            if ($site['parentID'] != $parentId) {
                continue;
            }
            $id = $site['id'];

            //call recursive for tree building
            $site['subPages'] = $this->buildSiteTree(
                $site['id'],
                $sites,
                $activeId
            );

            if (!$site['active'] && count($site['subPages']) > 0) {
                $site['active'] = max(array_column($site['subPages'], 'active'));
            }

            $site['childrenCount'] = count($site['subPages']);

            $result[$id] = $site;
        }

        return array_values($result);
    }

    /**
     * @param $shopId
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQuery($shopId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'page.id',
            'page.description',
            'page.link',
            'page.target',
            'page.parentID',
            'groups.key as `group`',
            'mapping.key as mapping'
        ]);

        $query->from('s_cms_static', 'page');

        $query->leftJoin(
            'page',
            's_cms_static_groups',
            'groups',
            'groups.active = 1'
        );

        $query->leftJoin(
            'groups',
            's_cms_static_groups',
            'mapping',
            'groups.mapping_id = mapping.id'
        );

        $query->leftJoin(
            'groups',
            's_core_shop_pages',
            'shops',
            'groups.id = shops.group_id AND shops.shop_id = :shopId'
        );

        $query->andWhere('groups.active = 1')
            ->andWhere("CONCAT('|', page.grouping, '|') LIKE CONCAT('%|', groups.key, '|%')")
            ->andWhere('(mapping.id IS NULL OR shops.shop_id IS NOT NULL)')
            ->andWhere('(mapping.id IS NULL OR mapping.active=1)')
            ->andWhere('(page.shop_ids IS NULL OR page.shop_ids LIKE :staticShopId)');

        $query
            ->orderBy('parentID', 'ASC')
            ->addOrderBy('mapping.key')
            ->addOrderBy('page.position')
            ->addOrderBy('page.description');

        $query->setParameter(':shopId', $shopId)
            ->setParameter(':staticShopId', '%|' . $shopId . '|%');


        return $query;
    }

    /**
     * Checks if the provided menu contains already an entry for the provided site.
     * If the provided site contains a mapping but the existing not, override the existing.
     * @param $menu
     * @param $key
     * @param $site
     * @return bool
     */
    public function overrideExisting($menu, $key, $site)
    {
        return (!empty($site['mapping']) && empty($menu[$key][0]['mapping']));
    }
}
