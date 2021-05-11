function upload() {
    var formData = new FormData();
    formData.append('file', $('#imgupload')[0].files[0]);

    $.ajax({
        url: 'upload.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (json) {
            if (json['success']) {
                prompt_func('上传成功', 'info');
            } else {
                prompt_func(json['error'], 'warning');
            }
            setTimeout(function () {location.reload();}, 500);
        }
    });
    $('#imgupload')[0].value = '';
}

function download() {
    var filename = $(this).parent().attr("filename");
    var form = $('<form method="POST" target="_blank"></form>');
    form.attr('action', 'download.php');
    var input = $('<input type="hidden" name="filename" value="' + filename + '"></input>')
    $(document.body).append(form);
    $(form).append(input);
    form.submit();
    form.remove();
}

function deletefile() {
    var filename = $(this).parent().attr("filename");
    var data = {
        "filename": filename
    };
    $.ajax({
        url: 'delete.php',
        type: 'POST',
        data: data,
        success: function (json) {
            if (json['success']) {
                prompt_func('删除成功', 'info');
            } else {
                prompt_func(json['error'], 'danger');
            }
            setTimeout(function () {location.reload();}, 500);
        }
    });
}

$(document).ready(function () {
    $("#imgupload").change(upload);
    $(".download").click(download);
    $(".delete").click(deletefile);
})