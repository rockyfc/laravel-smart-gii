let AppRequest = (function () {

    let request = function (url, data, method, success, error, completed) {
        $.ajax({
            type: method,
            url: url,
            data: data,
            dataType: 'json',
            success: function (response) {
                if (success) {
                    success(response);
                } else {
                    window.location.reload();
                }
            },
            error: function (xhr, status) {
                console.log('xhr', xhr);
                if (xhr.status == 422) {
                    if (typeof (xhr.responseJSON) == 'string') {
                        //$.toast(xhr.responseJSON);
                        //return;
                    }
                    if (typeof (xhr.responseJSON.message) == 'string') {
                        //$.toast(xhr.responseJSON.message);
                        //return;
                    }
                }
                if (xhr.status == 429) {
                    //$.toast('你太快了，休息休息~');
                    //return;
                }

                if (error) {
                    error(xhr);
                }
            },
            completed: function (xhr, status) {
                if (completed) {
                    completed();
                }
            }
        });
    }


    return {
        init: function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        },

        //提交表单
        submitForm: function (form, success, error, completed) {
            let jqForm = $(form);
            request(
                jqForm.attr('action'),
                jqForm.serialize(),
                jqForm.attr('method'),
                success,
                error,
                completed);
        },

        //删除
        delete: function (url, warn, success, error, completed) {

            if (warn && !confirm('确定删除吗？')) {
                return false;
            }
            request(
                url,
                null,
                'POST',
                success,
                error,
                completed
            );

        },

        get: function (url, data, success, error, completed) {
            request(
                url,
                data,
                'GET',
                success,
                error,
                completed
            );
        },

        post: function (url, data, success, error, completed) {
            request(
                url,
                data,
                'POST',
                success,
                error,
                completed
            );
        },


    };
})();
