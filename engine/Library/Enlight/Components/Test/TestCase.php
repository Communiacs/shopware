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
use PHPUnit\DbUnit\DataSet\XmlDataSet;

/**
 * Basic class for each specified test case.
 *
 * The Enlight_Components_Test_TestCase is the basic class for all specified test cases.
 * The enlight test case basic class extends PHPUnit\Framework\TestCase and sets the database link automatically.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Components_Test_TestCase extends PHPUnit\Framework\TestCase
{
    /**
     * @var PHPUnit_Extensions_Database_ITester The IDatabaseTester for this testCase
     */
    protected $databaseTester;

    /**
     * Sets up the fixture, for example, open a network connection.
     */
    protected function setUp()
    {
        parent::setUp();

        // Clear entitymanager to prevent weird 'model shop not persisted' errors.
        Shopware()->Models()->clear();

        $this->databaseTester = null;
        if (method_exists($this, 'getSetUpOperation')) {
            $this->getDatabaseTester()->setSetUpOperation($this->getSetUpOperation());
        }
        if (method_exists($this, 'getDataSet')) {
            $this->getDatabaseTester()->setDataSet($this->getDataSet());
        }
        if ($this->databaseTester !== null) {
            $this->getDatabaseTester()->onSetUp();
        }
    }

    /**
     * Performs operation returned by getSetUpOperation().
     */
    protected function tearDown()
    {
        if ($this->databaseTester !== null) {
            if (method_exists($this, 'getTearDownOperation')) {
                $this->getDatabaseTester()->setTearDownOperation($this->getTearDownOperation());
            }
            if (method_exists($this, 'getDataSet')) {
                $this->getDatabaseTester()->setDataSet($this->getDataSet());
            }
            $this->getDatabaseTester()->onTearDown();
        }

        $this->databaseTester = null;

        set_time_limit(0);
        ini_restore('memory_limit');
    }

    /**
     * Gets the IDatabaseTester for this testCase. If the IDatabaseTester is
     * not set yet, this method calls newDatabaseTester() to obtain a new
     * instance.
     *
     * @return Enlight_Components_Test_Database_DefaultTester
     */
    protected function getDatabaseTester()
    {
        if ($this->databaseTester === null) {
            $this->databaseTester = $this->newDatabaseTester();
        }

        return $this->databaseTester;
    }

    /**
     * Creates a IDatabaseTester for this testCase.
     *
     * @return Enlight_Components_Test_Database_DefaultTester
     */
    protected function newDatabaseTester()
    {
        return new Enlight_Components_Test_Database_DefaultTester();
    }

    /**
     * Creates a new XMLDataSet with the given $xmlFile. (absolute path.)
     *
     * @param string $xmlFile
     *
     * @return XmlDataSet
     */
    protected function createXMLDataSet($xmlFile)
    {
        return new XmlDataSet($xmlFile);
    }

    /**
     * Allows to set a shopware config
     *
     * @param string $name
     * @param mixed  $value
     */
    protected function setConfig($name, $value)
    {
        Shopware()->Container()->get('config_writer')->save($name, $value);
        Shopware()->Container()->get('cache')->clean();
        Shopware()->Container()->get('config')->setShop(Shopware()->Shop());
    }
}
