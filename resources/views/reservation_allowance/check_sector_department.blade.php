
@if($check_sector != 0 || $check_department != 0)
<div class="modal" id="my_modal_alert" tabindex="-1" role="dialog" style="display: block">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">تحذير</h5>
            <button type="button" class="close" id="delete" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            @if($check_sector != 0)
                <p>يوجد لديك عدد {{$check_sector}} غير مقيدين فى القطاع المختار</p>
            @endif

            @if($check_department != 0)
                <p>يوجد لديك عدد {{$check_department}} غير مقيدين فى الادارة المختارة</p>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" id="save_allowances" class="btn btn-primary">استمر فى الحفظ</button>
            <button type="button" id="closeButton" class="btn btn-secondary" data-dismiss="modal">اغلاق</button>
        </div>
        </div>
    </div>
</div>
@endif


<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script>
        $(document).ready(function() {
            $('#closeButton').on('click', function() {
                $('#my_modal_alert').modal('display') = 'none';
            });

            $('#save_allowances').on('click', function() {
                document.getElementById('add_create_all').submit();
            });
        });
</script>
