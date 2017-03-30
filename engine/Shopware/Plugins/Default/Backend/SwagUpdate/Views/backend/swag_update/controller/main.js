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

//{namespace name=backend/swag_update/main}
//{block name="backend/swag_update/controller/main"}

Ext.define('Shopware.apps.SwagUpdate.controller.Main', {
    extend: 'Enlight.app.Controller',

    init: function() {
        var me = this;

        me.changelogStore = me.getStore('Changelog').load({
            callback: function(records, operation, success) {
                if (!success) {
                    Ext.Msg.alert(
                        '{s name="connection_error_title"}Connection error{/s}',
                        '{s name="connection_error_message"}Unable to connect to the update server.<br/>Please check your servers internet connection or try again later.{/s}'
                    );
                    return;
                }

                //check if an update is available
                if (me.changelogStore.getCount() <= 0) {
                    me.mainWindow = me.getView('NoUpdate').create().show();
                } else {
                    me.pluginsStore = me.getStore('Plugins').load();
                    me.requirementsStore = me.getStore('Requirements').load();

                    me.mainWindow = me.getView('Window').create({
                        changelog: me.changelogStore.first(),
                        pluginsStore: me.pluginsStore,
                        requirementsStore: me.requirementsStore
                    }).show();

                    me.pluginsStore.on('load', function() {
                        me.changeTabIcon(
                            me.mainWindow.tabPanel.items.items[2],
                            me.getHighestErrorLevel(me.pluginsStore)
                        );
                    });

                    me.requirementsStore.on('load', function() {
                        me.changeTabIcon(
                            me.mainWindow.tabPanel.items.items[1],
                            me.getHighestErrorLevel(me.requirementsStore)
                        );
                    });
                }
            }
        });

        me.callParent(arguments);

        me.control({
            'update-main-window': {
                startUpdate: me.onStartEvent,
                validateUpdate: me.onValidateUpdate
            },
            'update-ftp': {
                saveFtp: me.onSaveFtp
            }
        });
    },

    changeTabIcon: function(tabContainer, level) {
        var tab = tabContainer.tab;

        if (level == 20) {
            tab.setIconCls('sprite-cross');
        } else if (level == 10) {
            tab.setIconCls('sprite-exclamation');
        } else {
            tab.setIconCls('sprite-tick');
        }
    },

    onSaveFtp: function(win, form) {
        var me = this;

        if (!form.getForm().isValid()) {
            return;
        }

        var ftp = Ext.create('Shopware.apps.SwagUpdate.model.Ftp');

        form.getForm().updateRecord(ftp);

        ftp.save({
            success: function(record, operation) {
                win.destroy();

                me.getView('Progress').create({
                    ftp: record
                }).show();
            },

            failure: function(record, operation) {
                try {
                    var data = operation.request.proxy.reader.rawData;

                    if (data.error) {
                        Shopware.Notification.createStickyGrowlMessage({
                            title: '{s name="update_title"}Update{/s}',
                            text: data.error
                        });

                    } else {
                        Shopware.Notification.createStickyGrowlMessage({
                            title: '{s name="update_title"}Update{/s}',
                            text: '{s name="ftp_error"}An error occurred while validating the ftp data.{/s}'
                        });
                    }
                } catch (e) {
                    Shopware.Notification.createStickyGrowlMessage({
                        title: '{s name="update_title"}Update{/s}',
                        text: '{s name="ftp_error"}An error occurred while validating the ftp data.{/s}'
                    });
                }
            }
        });
    },

    onValidateUpdate: function(win, checkbox, requirementStore, pluginStore) {
        var me = this;

        var requirements = me.getRecordsWithErrorLevel(requirementStore, 20);

        if (requirements.length > 0) {
            Shopware.Notification.createGrowlMessage(
                '{s name="requirements_title"}Requirements{/s}',
                '{s name="requirements_info"}Please check the requirements tab. Their are requirements which prevents the update progress.{/s}'
            );

            checkbox.setValue(false);

            win.tabPanel.setActiveTab(1);

            return;
        }

        var plugins = me.getRecordsWithErrorLevel(pluginStore, 20);

        if (plugins.length > 0) {
            Shopware.Notification.createGrowlMessage(
                '{s name="incompatible_plugins_title"}Incompatible plugins{/s}',
                '{s name="incompatible_plugins_info"}Please check the incompatible plugin tab. You have installed plugins prevents the update progress.{/s}'
            );

            checkbox.setValue(false);

            win.tabPanel.setActiveTab(2);

            return;
        }

        win.updateButton.setDisabled(false);
    },

    onStartEvent: function(win) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=SwagUpdate action=isUpdateAllowed}',
            async: true,
            timeout: 180000,
            success: function(response) {
                if (!response || !response.responseText) {
                    return;
                }

                var result = Ext.decode(response.responseText);

                if (!result.success) {

                    Shopware.Notification.createStickyGrowlMessage({
                        title: '{s name="update_not_allowed"}Update not allowed{/s}',
                        text: result.error
                    });

                    return;
                }

                if (result.ftpRequired) {
                    me.getView('Ftp').create().show();
                } else {
                    me.getView('Progress').create().show();
                }
            }
        });

    },

    getRecordsWithErrorLevel: function(store, errorLevel) {
        var me = this, records = [];

        store.each(function(record) {
            if (record.get('errorLevel') == errorLevel) {
                records.push(record);
            }
        });

        return records;
    },

    getHighestErrorLevel: function(store) {
        var me = this, level = 0;

        store.each(function(record) {
            if (record.get('errorLevel') > level) {
                level = record.get('errorLevel');
            }
        });

        return level;
    }

});

//{/block}
