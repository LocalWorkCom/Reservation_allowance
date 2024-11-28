<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @yield('title')
    </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/images/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('frontend/images/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('frontend/images/favicon/site.webmanifest') }}">
    <script type="application/javascript" src="{{ asset('frontend/js/bootstrap.min.js')}}"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <link rel="stylesheet" href="{{ asset('frontend/styles/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/styles/all.min.css') }}">

    <link href="{{ asset('frontend/styles/font-awesome.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/styles/webfonts/fa-regular-400.woff2') }}">
    <link rel="stylesheet" href="{{ asset('frontend/styles/webfonts/fa-regular-400.woff2') }}">

    <!-- Bootstrap-->
    <link href="{{ asset('frontend/styles/bootstrap.min.css') }}" rel="stylesheet" id="bootstrap-css">
    <link rel="manifest" href="{{ asset('frontend/styles/datatables.css') }}">
    @stack('style')
    <link rel="stylesheet" href="{{ asset('frontend/styles/index.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/styles/responsive.css') }}">
    <!-- Select 2-->
    <link rel="stylesheet" href="{{ asset('frontend/styles/select2/select2.min.css') }}">
    <style>
        .select2-container .select2-selection--single {
            height: 45px;
            font-size: 14px;
            border: 0.2px solid #d9d4d4;
            border-radius: 10px;
            background-color: #f8f8f8;
            direction: rtl;
            display: flex !important;
            padding-top: 8px !important;

        }

        .select2-container--default[dir="rtl"] .select2-selection--single .select2-selection__arrow {
            left: 1px;
            right: auto;
            padding-top: 5px !important;
            top: 9px !important;
        }

        .select2-container--default .select2-selection--multiple {
            height: 45px;
            font-size: 14px;
            border: 0.2px solid #d9d4d4;
            border-radius: 10px;
            background-color: #f8f8f8;
            direction: rtl;
        }

        .select2-results__option--selectable {
            cursor: pointer;
            display: flex !important;
        }

        .custom-select {
            width: 100%;
        }

        .select2-container--default .select2-results__option--disabled {
            color: #999;
            display: flex !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {

            top: -9px !important;
        }
    </style>
    <script src="{{ asset('frontend/js/jquary.js') }}"></script>
    <script src="{{ asset('frontend/js/datatable.js') }}"></script>
    <script src="{{ asset('frontend/js/flatpickr.js') }}"></script>
    <script src="{{ asset('frontend/js/select2/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            function resetModal() {
                $('#saveExternalDepartment')[0].reset();
                $('.text-danger').html('');
            }
            $("#saveExternalDepartment").on("submit", function(e) {

                e.preventDefault();

                // Serialize the form data
                var formData = $(this).serialize(); // Changed to $(this)

                // Submit AJAX request
                $.ajax({
                    url: $(this).attr('action'), // Changed to $(this)
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Handle success response
                            $('#from_departement').empty();
                            $.ajax({

                                url: "{{ route('external.departments') }}",
                                type: 'get',
                                success: function(response) {
                                    // Handle success response
                                    var selectOptions =
                                        '<option value="">اختر الادارة</option>';
                                    response.forEach(function(department) {
                                        selectOptions += '<option value="' +
                                            department.id +
                                            '">' + department.name +
                                            '</option>';
                                    });
                                    $('#from_departement').html(
                                        selectOptions
                                    ); // Assuming you have a select element with id 'from_departement'

                                },
                                // error: function(xhr, status, error) {
                                //     // Handle error response
                                //     console.error(xhr.responseText);
                                // }
                            });
                            // Optionally, you can close the modal after successful save
                            resetModal();
                            $('#extern-department').modal('hide'); // Changed modal ID
                        } else {
                            $.each(response.message, function(key, value) {
                                $('#' + key + '-error').html(value[0]);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        if (xhr.status == 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('#' + key + '-error').html(value[0]);
                            });
                        }
                    }
                });
            });
        });

        function toggleDropdown() {
            var dropdownMenu = document.getElementById("dropdownMenu");
            if (dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            } else {
                dropdownMenu.style.display = "block";
            }
        }
        window.onclick = function(event) {
            if (!event.target.matches('.btn')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }

        function toggleDropdown2() {
            var dropdownMenu = document.getElementById("dropdownMenu2");
            if (dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            } else {
                dropdownMenu.style.display = "block";
            }
        }
        window.onclick = function(event) {
            if (!event.target.matches('.btn2')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu2");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }

        function toggleDropdown3() {
            var dropdownMenu = document.getElementById("dropdownMenu3");
            if (dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            } else {
                dropdownMenu.style.display = "block";
            }
        }

        window.onclick = function(event) {
            if (!event.target.matches('.btn3')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu3");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }

        function toggleDropdown4() {
            var dropdownMenu = document.getElementById("dropdownMenu4");
            if (dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            } else {
                dropdownMenu.style.display = "block";
            }
        }

        window.onclick = function(event) {
            if (!event.target.matches('.btn4')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu4");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }

        function toggleDropdown5() {
            var dropdownMenu = document.getElementById("dropdownMenu5");
            if (dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            } else {
                dropdownMenu.style.display = "block";
            }
        }

        window.onclick = function(event) {
            if (!event.target.matches('.btn5')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu5");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }

        function toggleDropdown6() {
            var dropdownMenu = document.getElementById("dropdownMenu6");
            if (dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            } else {
                dropdownMenu.style.display = "block";
            }
        }

        window.onclick = function(event) {
            if (!event.target.matches('.btn6')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu6");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }

        function toggleDropdown9() {
            var dropdownMenu = document.getElementById("dropdownMenu9");
            if (dropdownMenu.style.display === "block") {
                dropdownMenu.style.display = "none";
            } else {
                dropdownMenu.style.display = "block";
            }
        }

        window.onclick = function(event) {
            if (!event.target.matches('.btn9')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu9");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }
        //  for header collapsing

        $(document).ready(function() {
            $('.navbar-toggler').click(function() {
                $('.navbar-collapse').toggleClass('show');
            });

            // Close navbar when clicking outside the menu area
            $(document).click(function(event) {
                var clickover = $(event.target);
                var $navbar = $('.navbar-collapse');
                var _opened = $navbar.hasClass('show');
                if (_opened === true && !clickover.hasClass('side-nav')) {
                    $navbar.removeClass('show');
                }
            });
        });

        // for file upload ******


        function updateFileInput() {
            var fileInput = document.getElementById('fileInput');
            var filesNum = document.getElementById('files_num').value;
            if (filesNum) {
                fileInput.disabled = false;
            } else {
                fileInput.disabled = true;
                document.getElementById('fileList').innerHTML = '';
            }
        }

        function uploadFils() {
            const files = document.getElementById('fileInput').files;
            const fileList = document.getElementById('fileList');
            const filesNum = parseInt(document.getElementById('files_num').value);
            // if (!filesNum) {
            //     alert("Please choose the number of books first.");
            //     document.getElementById('fileInput').value = '';
            //     return false;
            // }
            if (files.length === 0) {
                //alert("Please choose files.");
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: 'من فضلك أختر الملفات المطلوبه',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__slow'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
                return false;
            }
            if (files.length > filesNum) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: 'لا يمكنك أضافه اكثر من' + filesNum + ' ملف.',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__slow'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
                // alert('لا يمكنك أضافه اكثر من' + filesNum + ' ملف.');
                document.getElementById('fileInput').value = '';
                return false;
            }
            if (files.length < filesNum) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: 'لا يمكن اضافه ملفات أقل من ' + filesNum + ' ملف.',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__slow'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
                // alert('لا يمكن اضافه ملفات أقل من ' + filesNum + ' ملف.');
                document.getElementById('fileInput').value = '';
                return false;
            }
            fileList.innerHTML = ''; // Clear previous list
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                listItem.dataset.filename = file.name;
                const fileName = document.createElement('span');
                fileName.textContent = file.name;
                const deleteButton = document.createElement('button');
                deleteButton.className = 'btn btn-danger btn-sm';
                deleteButton.textContent = 'Delete';
                deleteButton.onclick = function() {
                    fileList.removeChild(listItem);
                    document.getElementById('fileInput').value = '';
                };
                listItem.appendChild(fileName);
                listItem.appendChild(deleteButton);
                fileList.appendChild(listItem);
            }
        }

        function toggleDropdown4(event) {
            event.stopPropagation();
            const menu4 = document.getElementById('dropdownMenu4');
            const menu5 = document.getElementById('dropdownMenu5');
            const menu6 = document.getElementById('dropdownMenu6');

            if (menu4.style.display === 'block') {
                menu4.style.display = 'none';
            } else {
                menu4.style.display = 'block';
                menu5.style.display = 'none';
                menu6.style.display = 'none';
            }
        }

        function toggleDropdown5(event) {
            event.stopPropagation();
            const menu4 = document.getElementById('dropdownMenu4');
            const menu5 = document.getElementById('dropdownMenu5');
            const menu6 = document.getElementById('dropdownMenu6');

            if (menu5.style.display === 'block') {
                menu5.style.display = 'none';
            } else {
                menu5.style.display = 'block';
                menu4.style.display = 'none';
                menu6.style.display = 'none';
            }
        }

        function toggleDropdown6(event) {
            event.stopPropagation();
            const menu4 = document.getElementById('dropdownMenu4');
            const menu5 = document.getElementById('dropdownMenu5');
            const menu6 = document.getElementById('dropdownMenu6');

            if (menu6.style.display === 'block') {
                menu6.style.display = 'none';
            } else {
                menu6.style.display = 'block';
                menu4.style.display = 'none';
                menu5.style.display = 'none';
            }
        }

        document.addEventListener('click', function(event) {
            const menu4 = document.getElementById('dropdownMenu4');
            const menu5 = document.getElementById('dropdownMenu5');
            const menu6 = document.getElementById('dropdownMenu6');


            if (!event.target.closest('.btn4') && !event.target.closest('#dropdownMenu4')) {
                menu4.style.display = 'none';
            }

            if (!event.target.closest('.btn5') && !event.target.closest('#dropdownMenu5')) {
                menu5.style.display = 'none';
            }

            if (!event.target.closest('.btn6') && !event.target.closest('#dropdownMenu6')) {
                menu6.style.display = 'none';
            }

        });
    </script>

    <script>
        flatpickr(
            "#start_time, #end_time, #fromTime,#toTime, #start_time_edit, #end_time_edit, #start_time_show, #end_time_show, #fromTime, #toTime", {
                enableTime: true, // Enable time picker
                noCalendar: true, // Disable calendar view
                dateFormat: "h:i K", // Set format for 12-hour time with AM/PM
                time_24hr: false, // Use 12-hour format (set to true for 24-hour format)
                minuteIncrement: 1 // Set minute increment step
            });
    </script>
</head>

<body>
    @include('layout.header')

    <main>
        @yield('content')
    </main>


    @include('layout.footer')
</body>



</html>
