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

//{namespace name=backend/custom_search/translation}

//{block name="backend/config/view/custom_search/facet/classes/productattributefacet"}

Ext.define('Shopware.apps.Config.view.custom_search.facet.classes.ProductAttributeFacet', {

    getClass: function() {
        return 'Shopware\\Bundle\\SearchBundle\\Facet\\ProductAttributeFacet';
    },

    createItems: function () {
        var me = this;

        return [
            me._createAttributeSelection(),
            me._createModeSelection(),
            {
                xtype: 'textfield',
                labelWidth: 150,
                name: 'formFieldName',
                allowBlank: false,
                helpText: '{s name="attribute_url"}{/s}',
                fieldLabel: '{s name="request_parameter"}{/s}',
                validator: Ext.bind(me.validateParameter, me)
            }, {
                xtype: 'textfield',
                labelWidth: 150,
                allowBlank: false,
                translatable: true,
                fieldLabel: '{s name="label"}{/s}',
                name: 'label'
            }, {
                xtype: 'textfield',
                labelWidth: 150,
                helpText: '{s name="attribute_template"}{/s}',
                name: 'template',
                fieldLabel: '{s name="template_file"}{/s}'
            }, {
                xtype: 'textfield',
                name: 'suffix',
                labelWidth: 150,
                translatable: true,
                fieldLabel: '{s name="suffix"}{/s}',
                helpText: '{s name="suffix_help_attribute"}{/s}'
            }, {
                xtype: 'numberfield',
                name: 'digits',
                minValue: 0,
                labelWidth: 150,
                translatable: true,
                value: 2,
                fieldLabel: '{s name="digits"}{/s}',
                helpText: '{s name="digits_help_attribute"}{/s}'
            }
        ];
    },

    _createModeSelection: function() {
        var me = this;

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'mode',
            labelWidth: 150,
            fieldLabel: '{s name="mode"}{/s}',
            valueField: 'key',
            allowBlank: false,
            forceSelection: true,
            tpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                    '<div class="x-boundlist-item">{literal}{[this.getRecordLabel(values)]}{/literal}</div>',
                '</tpl>',
                {
                    getRecordLabel: function(values) {
                        return me._getLabelOfObject(values);
                    }
                }
            ),
            displayTpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                    '{literal}{[this.getRecordLabel(values)]}{/literal}',
                '</tpl>',
                {
                    getRecordLabel: function(values) {
                        return me._getLabelOfObject(values);
                    }
                }
            ),
            displayField: 'label',
            queryMode: 'local',
            store: me._createModeStore()
        });
    },

    _getLabelOfObject: function(values) {
        var label = values.label;

        if (!label) {
            label = values.columnName;
        }
        if (values.helpText) {
            label += ' [' + values.helpText + ']';
        }
        return label;
    },

    _createModeStore: function() {
        return Ext.create('Ext.data.Store', {
            fields: ['key', 'label'],
            data: [
                { key: 'value_list', label: '{s name="value_list_mode"}{/s}' },
                { key: 'radio', label: '{s name="radio_mode"}{/s}' },
                { key: 'boolean', label: '{s name="boolean_mode"}{/s}' },
                { key: 'range', label: '{s name="range_mode"}{/s}' }
            ]
        })
    },

    _createAttributeSelection: function() {
        return Ext.create('Shopware.form.field.AttributeSingleSelection', {
            labelWidth: 150,
            name: 'field',
            allowBlank: false,
            fieldLabel: '{s name="product_attribute_sorting_field"}{/s}',
            store: this._createAttributeStore()
        });
    },

    _createAttributeStore: function() {
        return Ext.create('Ext.data.Store', {
            model: 'Shopware.model.Dynamic',
            proxy: {
                type: 'ajax',
                url: '{url controller="AttributeData" action="list"}',
                reader: Ext.create('Shopware.model.DynamicReader'),
                extraParams: {
                    table: 's_articles_attributes'
                }
            }
        });
    },

    validateParameter: function(value) {
        var me = this;
        var reg = new RegExp(/^[a-z][a-z0-9_]+$/);

        if (!reg.test(value)) {
            return '{s name="request_parameter_validation"}{/s}';
        }
        return true;
    }
});

//{/block}