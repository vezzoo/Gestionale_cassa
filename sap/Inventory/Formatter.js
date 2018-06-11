sap.ui.define(function() {
    "use strict";

    var Formatter = {

        weightState :  function (fValue) {
            try {
                fValue = parseFloat(fValue);
                if (fValue < 10) {
                    return "Error";
                } else if (fValue < 20) {
                    return "Warning";
                } else if (fValue >= 20) {
                    return "None";
                } else {
                    return "Error";
                }
            } catch (err) {
                return "None";
            }
        }
    };

    return Formatter;

}, /* bExport= */ true);
