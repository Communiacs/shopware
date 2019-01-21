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

namespace Shopware\Bundle\ESIndexingBundle\DependencyInjection\Factory;

use Elasticsearch\Client;
use Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES2;
use Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES5;
use Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES6;
use Shopware\Bundle\ESIndexingBundle\TextMappingInterface;

class TextMappingFactory
{
    /**
     * @var bool
     */
    private $esEnabled;

    /**
     * @var string
     */
    private $version;

    /**
     * @param bool          $esEnabled
     * @param string | null $version
     */
    public function __construct($esEnabled = false, $version = null)
    {
        $this->esEnabled = $esEnabled;
        $this->version = $version;
    }

    /**
     * @param Client $client
     *
     * @return TextMappingInterface
     */
    public function factory(Client $client)
    {
        if (!$this->esEnabled) {
            return new TextMappingES2();
        }

        if (!$this->version) {
            try {
                $info = $client->info();
                $this->version = $info['version']['number'];
            } catch (\Exception $e) {
                return new TextMappingES2();
            }
        }

        switch (true) {
            case version_compare($this->version, '6', '>='):
                return new TextMappingES6();
            case version_compare($this->version, '5', '>='):
                return new TextMappingES5();
            default:
                return new TextMappingES2();
        }
    }
}
