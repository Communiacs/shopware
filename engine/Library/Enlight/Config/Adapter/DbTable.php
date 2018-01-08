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

/**
 * Database adapter for the enlight config classes.
 *
 * The Enlight_Config_Adapter_DbTable is an adapter to read enlight configurations out of the database.
 * It supports an automatically serialization of the configuration data, supports configuration sections and
 * update and create columns.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Config_Adapter_DbTable extends Enlight_Config_Adapter
{
    /**
     * The table instance.
     *
     * @var null|string|Enlight_Components_Table
     */
    protected $_table;

    /**
     * The namespace column in the database table.
     *
     * @var null|string
     */
    protected $_namespaceColumn;

    /**
     * The name column in the database table.
     *
     * @var null|string
     */
    protected $_nameColumn = 'name';

    /**
     * The value column in the database table.
     *
     * @var null|string
     */
    protected $_valueColumn = 'value';

    /**
     * The section column in the database table.
     *
     * @var null|string
     */
    protected $_sectionColumn = 'section';

    /**
     * The dirty column in the database table.
     *
     * @var null|string
     */
    protected $_dirtyColumn = 'dirty';

    /**
     * The automatic serialization option value.
     *
     * @var bool
     */
    protected $_automaticSerialization = false;

    /**
     * The created column in the database table.
     *
     * @var null|string
     */
    protected $_createdColumn = 'created';

    /**
     * The created column in the database table.
     *
     * @var null|string
     */
    protected $_updatedColumn = 'updated';

    /**
     * Database adapter which performs all operations on the database
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Sets the options of an array.
     *
     * @param array $options
     *
     * @return Enlight_Config_Adapter
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            switch ($key) {
                case 'nameColumn':
                case 'namespaceColumn':
                case 'valueColumn':
                case 'createdColumn':
                case 'updatedColumn':
                    $this->{'_' . $key} = (string) $option;
                    break;
                case 'automaticSerialization':
                    $this->{'_' . $key} = (bool) $option;
                    break;
                case 'sectionColumn':
                case 'table':
                    $this->{'_' . $key} = $option;
                    break;
                case 'db':
                    $this->{'_' . $key} = $option;
                    break;
                default:
                    break;
            }
        }

        return parent::setOptions($options);
    }

    /**
     * @param null $name
     *
     * @return Enlight_Components_Table
     */
    public function getTable($name = null)
    {
        if ($name !== null) {
            return new Enlight_Components_Table([
               'name' => $name,
               'db' => $this->_db, ]
            );
        }
        if (!$this->_table instanceof Enlight_Components_Table) {
            $this->_table = new Enlight_Components_Table([
                'name' => $this->_table,
                'db' => $this->_db, ]
            );
        }

        return $this->_table;
    }

    /**
     * Reads a section from the data store.
     *
     * @param Enlight_Config $config
     *
     * @return Enlight_Config_Adapter_DbTable
     */
    public function read(Enlight_Config $config)
    {
        $name = $this->_namePrefix . $config->getName() . $this->_nameSuffix;
        $section = $config->getSection();

        $data = [];

        $extends = $config->getExtends();
        $currentSection = is_array($section) ? implode(':', $section) : $section;
        while ($currentSection !== null) {
            $data += $this->readSection($name, $currentSection);
            $currentSection = isset($extends[$currentSection]) ? $extends[$currentSection] : null;
        }

        $config->setData($data);

        return $this;
    }

    /**
     * Saves the data changes in the data store.
     *
     * @param Enlight_Config $config
     * @param array          $fields
     * @param bool           $update     If false, existing rows are not updated
     * @param bool           $force      If true, existing dirty columns are updated
     * @param bool           $allowReset If true, updating existing columns with existing value will reset dirty flag
     *
     * @return Enlight_Config_Adapter_DbTable
     */
    public function write(Enlight_Config $config, $fields = null, $update = true, $force = false, $allowReset = false)
    {
        if (!$this->_allowWrites) {
            return $this;
        }

        $name = $this->_namePrefix . $config->getName() . $this->_nameSuffix;
        $section = explode($config->getSectionSeparator(), $config->getSection());

        $dbTable = $this->getTable($this->_namespaceColumn === null ? $name : null);
        $db = $dbTable->getAdapter();

        if ($fields === null) {
            $fields = $config->getDirtyFields();
        }
        if (empty($fields)) {
            return $this;
        }

        $where = [];
        $updateData = [];
        $insertData = [];

        if ($this->_namespaceColumn !== null) {
            $insertData[$this->_namespaceColumn] = $name;
            $where[] = $db->quoteInto($this->_namespaceColumn . '=?', $name);
        }
        if ($this->_updatedColumn !== null) {
            $updateData[$this->_updatedColumn] = new Zend_Date();
            $insertData[$this->_updatedColumn] = new Zend_Date();
        }
        if ($this->_createdColumn !== null) {
            $insertData[$this->_createdColumn] = new Zend_Date();
        }

        if ($section !== null) {
            if (is_array($this->_sectionColumn)) {
                foreach ($this->_sectionColumn as $key => $sectionColumn) {
                    if (isset($section[$key])) {
                        $where[] = $db->quoteInto($sectionColumn . '=?', $section[$key]);
                        $insertData[$sectionColumn] = $section[$key];
                    }
                }
            } else {
                $where[] = $db->quoteInto($this->_sectionColumn . '=?', $section);
                $insertData[$this->_sectionColumn] = $section;
            }
        }

        foreach ((array) $fields as $field) {
            $fieldWhere = $where;
            $fieldWhere[] = $db->quoteInto($this->_nameColumn . '=?', $field);

            $row = $dbTable->fetchRow($fieldWhere);

            if ($row !== null) {
                if ($update) {
                    $data = $updateData;
                    $newValue = $config->get($field);
                    if ($this->_automaticSerialization) {
                        $newValue = serialize($newValue);
                    }

                    if (!$force && $row[$this->_dirtyColumn] == 1 && ($row[$this->_valueColumn] != $newValue || !$allowReset)) {
                        continue;
                    }

                    if ($allowReset && $row[$this->_valueColumn] == $newValue) {
                        $data[$this->_dirtyColumn] = 0;
                    } else {
                        $data[$this->_valueColumn] = $newValue;
                    }

                    $dbTable->update($data, $fieldWhere);
                }
            } else {
                $data = $insertData;
                $data[$this->_nameColumn] = $field;
                $data[$this->_dirtyColumn] = 0;
                if ($this->_automaticSerialization) {
                    $data[$this->_valueColumn] = serialize($config->get($field));
                } else {
                    $data[$this->_valueColumn] = $config->get($field);
                }
                $dbTable->insert($data);
            }
        }
        $config->setDirtyFields(array_diff($config->getDirtyFields(), $fields));

        return $this;
    }

    /**
     * Removes the data from the data store.
     *
     * @param Enlight_Config $config
     * @param array          $fields
     * @param bool           $deleteDirty
     *
     * @return Enlight_Config_Adapter_DbTable
     */
    public function delete(Enlight_Config $config, $fields = null, $deleteDirty = false)
    {
        $name = $this->_namePrefix . $config->getName() . $this->_nameSuffix;
        $section = explode($config->getSectionSeparator(), $config->getSection());

        $dbTable = $this->getTable($this->_namespaceColumn === null ? $name : null);
        $db = $dbTable->getAdapter();

        if ($fields === null) {
            $fields = $config->getDirtyFields();
        }
        if (empty($fields)) {
            return $this;
        }

        $where = [];
        $insertData = [];

        if ($this->_namespaceColumn !== null) {
            $insertData[$this->_namespaceColumn] = $name;
            $where[] = $db->quoteInto($this->_namespaceColumn . '=?', $name);
        }

        if ($section !== null) {
            if (is_array($this->_sectionColumn)) {
                foreach ($this->_sectionColumn as $key => $sectionColumn) {
                    if (isset($section[$key])) {
                        $where[] = $db->quoteInto($sectionColumn . '=?', $section[$key]);
                        $insertData[$sectionColumn] = $section[$key];
                    }
                }
            } else {
                $where[] = $db->quoteInto($this->_sectionColumn . '=?', $section);
                $insertData[$this->_sectionColumn] = $section;
            }
        }

        $where[] = $db->quoteInto($this->_nameColumn . ' IN (?)', $fields);
        if (!$deleteDirty) {
            $where[] = $db->quoteInto($this->_dirtyColumn . '=?', 0);
        }

        $dbTable->delete($where);

        return $this;
    }

    /**
     * @param $name
     * @param $section
     *
     * @return array
     */
    protected function readSection($name, $section)
    {
        $dbTable = $this->getTable($this->_namespaceColumn === null ? $name : null);

        $select = $dbTable->select()->from($dbTable->info('name'), [
            $this->_nameColumn, $this->_valueColumn,
        ]);

        if ($this->_namespaceColumn !== null) {
            $select->where($this->_namespaceColumn . '=?', $name);
        }

        if ($section !== null && $this->_sectionColumn !== null) {
            if (is_array($this->_sectionColumn)) {
                $section = explode(':', $section);
                foreach ($this->_sectionColumn as $key => $sectionColumn) {
                    if (!empty($section[$key])) {
                        $select->where($sectionColumn . '=?', $section[$key]);
                    }
                }
            } elseif ($this->_sectionColumn !== null) {
                $select->where($this->_sectionColumn . '=?', $section);
            }
        }

        if ($this->_valueColumn !== '*') {
            $data = $dbTable->getAdapter()->fetchPairs($select);
        } else {
            $data = $dbTable->getAdapter()->fetchAssoc($select);
        }

        if ($this->_automaticSerialization) {
            foreach ($data as $key => $value) {
                $data[$key] = unserialize($value);
            }
        }

        return $data;
    }
}
