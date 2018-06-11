let  mthis;

function delRow(oArg){
    let deleteRecord = oArg.getSource().getBindingContext().getObject();
    let dataset = mthis.getView().getViewData().prodotti;
    let len = dataset.length;
    for (let i = 0; i < len; i++) {
        if (dataset[i] === deleteRecord) {
            mthis.getView().getViewData().prodotti.splice(i, 1);
            mthis.oModel.refresh();
            break;
        }
    }
}

function addRow(oArg){
    let deleteRecord = oArg.getSource().getBindingContext().getObject();
    let dataset = mthis.getView().getViewData().prodotti;
    let len = dataset.length;
    for (let i = 0; i < len; i++) {
        if (dataset[i] === deleteRecord) {
            mthis.getView().getViewData().prodotti.splice(i, 0, jQuery.extend(true, {}, deleteRecord));
            mthis.oModel.refresh();
            break;
        }
    }
}

function addNRow(){
    mthis.getView().getViewData().prodotti.push({nome: "", desc: "", restano: "", prezzo: 0, gruppo: ""});
    mthis.oModel.refresh();
}

sap.ui.define([
    'jquery.sap.global', './Formatter', 'sap/ui/core/mvc/Controller', 'sap/ui/model/json/JSONModel', 'sap/m/MessageToast'
], function (jQuery, Formatter, Controller, JSONModel, MessageToast) {
    "use strict";

    var TableController = Controller.extend("App.Inventory", {

        godash: function () {
            window.location.href = '../Dashboard/dashboard.php'
        },

        onExit: function () {
            this.aProductCollection = [];
            this.oEditableTemplate.destroy();
            this.oModel.destroy();
            $.post("../../exit.php", function (d, e) {
                window.location.href = '../../index.php';
            })
        },

        addNew: addNRow,

        onInit: function (evt) {
            mthis = this;
            this.oModel = new JSONModel(this.getView().getViewData());
            this.oTable = this.getView().byId("idProductsTable");
            this.getView().setModel(this.oModel);
            this.oReadOnlyTemplate = this.getView().byId("idProductsTable").removeItem(0);
            this.rebindTable(this.oReadOnlyTemplate, "Navigation");
            this.oEditableTemplate = new sap.m.ColumnListItem({
                cells: [
                    new sap.m.Button({
                        icon: "sap-icon://delete",
                        type: sap.m.ButtonType.Reject,
                        press: delRow
                    }),
                    new sap.m.Button({
                        icon: "sap-icon://add",
                        type: sap.m.ButtonType.Accept,
                        press: addRow
                    }),
                    new sap.m.Input({
                        value: "{nome}"
                    }), new sap.m.Input({
                        value: "{desc}",
                    }), new sap.m.Input({
                        value: "{restano}",
                    }), new sap.m.Input({
                        value: "{prezzo}",
                    }), new sap.m.Input({
                        value: "{gruppo}",
                    })
                ]
            });
        },

        rebindTable: function (oTemplate, sKeyboardMode) {
            this.oTable.bindItems({
                path: "/prodotti",
                template: oTemplate,
                key: "id"
            }).setKeyboardMode(sKeyboardMode);
        },

        onEdit: function () {
            this.aProductCollection = jQuery.extend(true, [], this.oModel.getProperty("/prodotti"));
            this.getView().byId("editButton").setVisible(false);
            this.getView().byId("saveButton").setVisible(true);
            this.getView().byId("cancelButton").setVisible(true);
            this.rebindTable(this.oEditableTemplate, "Edit");
            this.getView().byId("nuevo").setVisible(true);
        },

        onSave: function () {
            this.getView().byId("saveButton").setVisible(false);
            this.getView().byId("cancelButton").setVisible(false);
            this.getView().byId("editButton").setVisible(true);
            this.rebindTable(this.oReadOnlyTemplate, "Navigation");
            $.post("./updateMagazzino.php", {data: this.getView().getViewData()}, function (d, e) {
                MessageToast.show(d)
            });
            this.getView().byId("nuevo").setVisible(false);
        },

        onCancel: function () {
            this.getView().byId("cancelButton").setVisible(false);
            this.getView().byId("saveButton").setVisible(false);
            this.getView().byId("editButton").setVisible(true);
            this.oModel.setProperty("/ProductCollection", this.aProductCollection);
            this.rebindTable(this.oReadOnlyTemplate, "Navigation");
            this.getView().byId("nuevo").setVisible(false);
        },

        onOrder: function () {
            MessageToast.show("Order button pressed");
        },
    });

    return TableController;

});
