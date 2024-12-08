
@if($check_sector != 0 || $check_department != 0)

<input type="hidden" value="{{$check_sector}}" name="check_sector" id="check_sector">
<input type="hidden" value="{{$check_department}}" name="check_department" id="check_department">


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
        $(document).ready(function() {

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
