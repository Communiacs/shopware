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

namespace Shopware\Bundle\EsBackendBundle\Commands;

use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Bundle\EsBackendBundle\EsAwareRepository;
use Shopware\Bundle\EsBackendBundle\EsBackendIndexer;
use Shopware\Bundle\ESIndexingBundle\LastIdQuery;
use Shopware\Commands\ShopwareCommand;
use Shopware\Models\Article\Article;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SyncBacklogCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('sw:es:backend:sync')
            ->setDescription('Synchronize events from the backlog to the live index.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->container->get('dbal_connection');

        $backlogs = $connection->fetchAll('SELECT * FROM s_es_backend_backlog ORDER BY id ASC LIMIT 20');

        if (empty($backlogs)) {
            $output->writeln('Backlog empty');

            return;
        }

        $registry = $this->container->get('shopware_attribute.repository_registry');

        $indexer = $this->container->get('shopware_es_backend.indexer');

        foreach ($backlogs as $backlog) {
            $criteria = new SearchCriteria($backlog['entity']);

            $repository = $registry->getRepository($criteria);

            if (!$repository instanceof EsAwareRepository) {
                continue;
            }

            $output->writeln(sprintf('Sync %s with id %s', $backlog['entity'], $backlog['entity_id']));

            if ($backlog['entity'] === Article::class) {
                $this->indexArticle($backlog['entity_id']);
            } else {
                $index = $this->getIndexName($repository->getDomainName());
                $indexer->indexEntities($index, $repository, [$backlog['entity_id']]);
            }
        }

        $ids = array_column($backlogs, 'id');

        $connection->executeUpdate(
            'DELETE FROM s_es_backend_backlog WHERE id IN (:ids)',
            ['ids' => $ids],
            ['ids' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
        );
    }

    private function indexArticle($id)
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query = $query
            ->select(['products.id', 'products.ordernumber'])
            ->from('s_articles_details', 'products')
            ->andWhere('products.id > :lastId')
            ->andWhere('products.articleID = :article')
            ->setParameter(':lastId', 0)
            ->setParameter(':article', $id)
            ->addOrderBy('products.id')
            ->setMaxResults(50);

        $indexer = $this->container->get('shopware_es_backend.indexer');

        $query = new LastIdQuery($query);

        $repository = $this->container->get('shopware_attribute.product_repository');

        $index = $this->getIndexName($repository->getDomainName());

        while ($numbers = $query->fetch()) {
            $indexer->indexEntities($index, $repository, $numbers);
        }
    }

    private function getIndexName($domainName)
    {
        $client = $this->container->get('shopware_elastic_search.client');

        $alias = EsBackendIndexer::buildAlias($domainName);

        $exist = $client->indices()->getAlias(['name' => $alias]);

        $index = array_keys($exist);

        return array_shift($index);
    }
}
