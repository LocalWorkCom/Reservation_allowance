
<!--<div class="modal" id="my_modal_alert" tabindex="-1" role="dialog" style="display: block">
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
</div>-->

@if($check_sector != 0 || $check_department != 0)

<input type="hidden" value="{{$check_sector}}" name="check_sector" id="check_sector">
<input type="hidden" value="{{$check_department}}" name="check_department" id="check_department">


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
        $(document).ready(function() {
            /*$('#closeButton').on('click', function() {
                $('#my_modal_alert').modal('display') = 'none';
            });

            $('#save_allowances').on('click', function() {
                document.getElementById('add_create_all').submit();
            });*/

            var check_sector = document.getElementById('check_sector').value;
            var check_department = document.getElementById('check_department').value;

            var text = "";
            if(check_sector != 0){
                text = "يوجد لديك عدد "+check_sector+" غير مقيدين فى القطاع المختار"
            }
            if(check_department != 0){
                text = "يوجد لديك عدد "+check_department+" غير مقيدين فى الادارة المختار"
            }

            Swal.fire({
                title: 'تحذير',
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم استمر فى الحفظ, نقل',
                cancelButtonText: 'إلغاء',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {  
                    document.getElementById('add_create_all').submit();
                } else {

                }
            });
        
        });
</script>
@endif
