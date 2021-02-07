<br/>
<p class="error-classes"></p>
<table class="table border hidden" id="fc-files">
    <thead>
    <tr>
        <th>代码文件</th>
        <th>Action</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <tr class="hidden tpl">
        <td></td>
        <td></td>
        <td><input type="checkbox" name="classes[]" value=""></td>
    </tr>
    </tbody>
</table>
<pre class="hidden " style="background: #343a40;color:#fff;padding:10px;"></pre>
<script>
    function fillFiles(files) {
        let tr = $('#fc-files').find('.tpl');
        tr.siblings('tr').remove();

        console.log('files = ', files)
        for (let i in files) {
            let item = tr.clone().removeClass('hidden').removeClass('tpl');
            item.find('td:eq(0)').html(files[i].file)
            item.find('td:eq(1)').html(files[i].action)
            item.find('input[type=checkbox]').val(files[i].file);
            if (files[i].checked) {
                item.find('input[type=checkbox]').attr('checked', true);
            } else {
                item.find('input[type=checkbox]').attr('checked', false);
            }
            if (files[i].action == 'overwrite') {
                item.find('td:eq(0)').append(' <span class="label label-danger">Diff</span>');
            }

            $('#fc-files').find('tbody').append(item);

        }
    }

    function fillResult(data) {

        let str = '';
        for (let i in data) {
            if (data[i].isDone == true) {
                str += data[i].file + " 创建成功\n";
            } else {
                str += data[i].file + " 创建失败\n";
            }
        }
        $('pre').removeClass('hidden').html(str);
    }
</script>
