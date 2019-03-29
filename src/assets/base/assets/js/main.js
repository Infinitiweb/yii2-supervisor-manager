$(document).ready(function () {
    SupervisorManagerNew.init();
});

let SupervisorManagerNew = {
    container: '#supervisor-manager-widget',

    /**
     * @param event
     */
    init: function (event) {
        $(document).on('click', '.processControl', SupervisorManagerNew.processControl);
        $(document).on('click', '.supervisorControl', SupervisorManagerNew.supervisorControl);
        $(document).on('click', '.groupControl [data-action]', SupervisorManagerNew.groupControl);
        $(document).on('click', '.groupControl [data-group-process-delete]', SupervisorManagerNew.groupProcessDelete);
        $(document).on('click', '.processList .showLog', SupervisorManagerNew.showLog);
        $(document).on('submit', '#createGroupForm', SupervisorManagerNew.createGroup);
    },

    /**
     * @param response
     * @returns {boolean}
     */
    responseHandler: function (response) {
        if (response['success']) {
            SupervisorManagerNew.pjaxReload();

            return true;
        }

        let $logModal = $('#errorModal');

        $logModal.find('.modal-body p').html(response['message']);
        $logModal.modal();
    },

    pjaxReload: function () {
        $.pjax.reload({container: SupervisorManagerNew.container, timeout: 2000});
    },

    /**
     * @param event
     */
    supervisorControl: function (event) {
        let actionType = $(this).data('action');

        if (actionType == 'refresh') {
            $.pjax.reload({container: SupervisorManagerNew.container, timeout: 2000});

            return;
        } else if (actionType == 'restart') {
            let doRestart = confirm('Restart supervisor? All processes will be killed');

            if (!doRestart) {
                return;
            }
        }

        $.post('/supervisor/default/supervisor-control', {
            actionType: actionType
        }, SupervisorManagerNew.responseHandler);
    },

    /**
     * @param event
     */
    processControl: function (event) {
        let processName = $(this).data('process-name'),
            actionType = $(this).data('action-type');

        $.post('/supervisor/default/process-control', {
            processName: processName,
            actionType: actionType
        }, SupervisorManagerNew.pjaxReload);
    },

    /**
     * @param event
     */
    createGroup: function (event) {
        let formData = $(this).serialize();

        $.post('/supervisor/default/create-group', formData, SupervisorManagerNew.responseHandler);

        $(this).trigger("reset");
        $('#createGroup').modal('hide');

        return false;
    },

    /**
     * @param event
     */
    showLog: function (event) {
        let processName = $(this).data('process-name'),
            logType = $(this).data('log-type');

        $.post('/supervisor/default/get-process-log', {
            processName: processName,
            logType: logType
        }, function (response) {
            let $logModal = $('#errorModal'),
                message = null;

            if (response['success']) {
                $logModal = $('#processOutputModal');

                message = response['processLog'].replace(/\n/g, '<br>');
            } else {
                message = response['message'];
            }

            $logModal.find('.modal-body p').html(message);
            $logModal.modal();
        });
    },

    /**
     * @param event
     */
    groupControl: function (event) {
        let actionUrl = '/supervisor/default/group-control';

        if ($(event.currentTarget).hasClass('processConfigControl')) {
            actionUrl = '/supervisor/default/process-config-control';
        }

        var actionType = $(this).data('action'),
            groupName = $(this).parents('.groupControl').data('groupName'),
            needConfirm = $(this).data('need-confirm');

        if (typeof needConfirm != 'undefined') {
            if (!confirm("Are you sure?")) {
                return;
            }
        }

        $.post(actionUrl, {
            actionType: actionType,
            groupName: groupName
        }, SupervisorManagerNew.responseHandler);
    },

    /**
     * @param event
     */
    groupProcessDelete: function (event) {
        let groupName = $(this).parents('.groupControl').data('groupName');

        $.post('/supervisor/default/count-group-processes', {
            groupName: groupName
        }).done(function (response) {
            let actionName = 'deleteGroupProcess';

            if (response['count'] == 1) {
                if (!confirm("1 process left, do you want to delete group?")) {
                    return false;
                }

                actionName = 'deleteProcess';
            }

            call(actionName);
        });

        function call(actionType) {
            $.post('/supervisor/default/process-config-control', {
                actionType: actionType,
                groupName: groupName
            }, SupervisorManagerNew.responseHandler);
        }
    },
};
