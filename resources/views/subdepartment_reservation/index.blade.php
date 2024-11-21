<style>
   .info-box {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
        text-align: center;
    }
</style>
@extends('layout.main')

@push('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css" defer>
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js" defer>
    </script>
@endpush


@section('title')
    رصيد بدل حجز - {{ $subDepartment }}
@endsection

@section('content')
<div class="row">
        <div class="container welcome col-11">
        <div class="d-flex justify-content-between">
                  <h3>احصائيات  التابعة لـ {{ $subDepartment }}</h3>
            </div>
        </div>
    </div>

    <br>

    <div class="row">
        <div class="container col-11 mt-3 p-0 pt-5 pb-4">
             <div class="col-lg-12">
             <div class="bg-white">
                    @if (session()->has('message'))
                        <div class="alert alert-info">
                            {{ session('message') }}
                        </div>
                    @endif
                    <div>
        <table id="users-table" class="display table table-responsive-sm table-bordered table-hover dataTable">
            <thead>
                <tr>
                    <th>الترتيب</th>
                    <th>اليوم</th>
                    <th>التاريخ</th>
                    <th>عدد المحجوزين</th>
                    <th>حجز جزئي العدد</th>
                    <th>حجز جزئي المبلغ</th>
                    <th>حجز كلي العدد</th>
                    <th>حجز كلي المبلغ</th>
                    <th>اجمالي المبلغ</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("subdepartment_reservation.getAll", $subDepartmentId) }}'
                      },
                columns: [
                    { data: null, name: 'order', orderable: false, searchable: false },
                    { data: 'day', name: 'day' },
                    { data: 'date', name: 'date' },
                    { data: 'prisoners_count', name: 'prisoners_count' },
                    { data: 'partial_reservation_count', name: 'partial_reservation_count' },
                    { data: 'partial_reservation_amount', name: 'partial_reservation_amount' },
                    { data: 'full_reservation_count', name: 'full_reservation_count' },
                    { data: 'full_reservation_amount', name: 'full_reservation_amount' },
                    { data: 'total_amount', name: 'total_amount' },
                ],
                "fnDrawCallback": function(oSettings) {
                    var api = this.api();
                    var pageInfo = api.page.info();
                    // Check if the total number of records is less than or equal to the number of entries per page
                    if (pageInfo.recordsTotal <= 10) { // Adjust this number based on your page length
                        $('.dataTables_paginate').css('visibility', 'hidden'); // Hide pagination
                    } else {
                        $('.dataTables_paginate').css('visibility', 'visible'); // Show pagination
                    }
                }
                createdRow: function(row, data, dataIndex) {
                    $('td', row).eq(0).html(dataIndex + 1);
                }
            });
        });

        function printReport(date) {
            window.open(`/subdepartment_statistics/print?date=${date}`, '_blank').focus();
        }
    </script>
@endpush
