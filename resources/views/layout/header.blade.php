<div class="all-nav">
    <div class="upper-navbar d-flex">
        <div class="second-section d-flex col-md-9 col-sm-6">
            <div class="dropdown">
                @php
                    $user = auth()->user();
                @endphp

                @if (!empty($user))
                    <button class="btn btn-2 mt-3" onclick="toggleDropdown()">
                        <i class="fa-solid fa-angle-down mx-2"></i>
                        {{ $user->name }}
                        <i class="fa-solid fa-user mx-2"></i>
                    </button>
                    <div id="dropdownMenu" class="dropdown-menu">
                        <a href="{{ route('logout') }}">تسجيل خروج <i class="fa-solid fa-right-from-bracket"></i></a>
                    </div>
                @else
                    <button class="btn btn-2 mt-3">
                        <a href="{{ route('login') }}" style="color: #ffffff; text-decoration:none;">سجل الدخول <i
                                class="fa-solid fa-user mx-2"></i></a>
                    </button>
                @endif
            </div>
            <button class="btn2 btn-2 mx-5" style="border-inline: 1px solid rgb(41, 41, 41); height: 100%;"
                onclick="toggleDropdown2()">
                <a class="bell mx-md-5">
                    <i class="fa-regular fa-bell"></i>
                </a>
            </button>
            <div id="dropdownMenu2" class="dropdown-menu2">
                <p>notification notification notification notification </p>
                <hr>
                <p>notification notification notification notification </p>
                <hr>
                <p>notification notification notification notification </p>
                <hr>
                <p>notification notification notification notification </p>
                <hr>
                <p>notification notification notification notification </p>
                <hr>
            </div>
            <div class="input-group mx-2">
            {{--     <button type="button" id="search-btn" class="btn mt-4" data-mdb-ripple-init>
                    <i class="fas fa-search"></i>
                </button>
                <div class="form-outline mt-4">
                    <input type="search" id="q" name="q" class="form-control" placeholder="بحث"
                        @isset($q)
                        value="{{ $q }}" @endisset />
                </div>
             --}} {{--    <select name="search" id="search" class="mt-4" style="direction:rtl;">
                    <option value="users"
                        @isset($search) @if ($search == 'users') selected @endif @endisset>
                        المستخدمين
                    </option>
                    <option value="dept"
                        @isset($search) @if ($search == 'dept') selected @endif @endisset>
                        الادارات
                    </option>
                    <option value="sect"
                        @isset($search) @if ($search == 'sect') selected @endif @endisset>
                        القطاعات
                    </option>
                    <option value="emps"
                        @isset($search) @if ($search == 'emps') selected @endif @endisset>
                        الموظفين
                    </option>
                    <!--    <option value="export">الصادر</option>
                    <option value="import">الوارد</option> -->
                </select> --}}
            </div>
        </div>
        <div class="first-section d-flex justify-content-between mt-1 ">
            <h2 style="color: #FFFFFF">{{ showUserDepartment() }} -</h2>
            <h2> شئون القوة</h2>
            <img class="mt-2" src="{{ asset('frontend/images/logo.svg') }}" alt="">
        </div>
    </div>
    <div class="navbar navbar-expand-md mb-4" role="navigation" dir="rtl">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars side-nav"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav d-flex justify-content-between w-100">
                <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('frontend/images/home.svg') }}" alt="logo">
                        <h6>الرئيسية</h6>
                    </a>
                </li>
                {{-- <li class="nav-item {{ request()->routeIs('user.index') ? 'active' : '' }} ">
                    <a href="{{ route('user.index', 0) }}">
                        <img src="{{ asset('frontend/images/users.svg') }}" alt="logo">
                        <h6>المستخدمين </h6>
                    </a>
                </li> --}}
                <li class="nav-item {{ request()->routeIs('user.employees') ? 'active' : '' }} btn3  @isset($search) @if ($search == 'emps') active @endif @endisset"
                    onclick="toggleDropdown3(event)">

                    @if (Auth::user()->rule_id == 2)
                        <a href="{{ route('user.employees') }}">
                            <img src="{{ asset('frontend/images/employees.svg') }}" alt="logo">
                            <h6 class="btn3">موظفين الوزارة</h6>
                        </a>
                    @endif
                    @if (Auth::user()->rule_id != 2)
                        <a href="{{ route('user.employees') }}">
                            <img src="{{ asset('frontend/images/employees.svg') }}" alt="logo">
                            <h6 class="btn3">موظفين القوة</h6>
                        </a>
                    @endif


                </li>

                <li class="nav-item {{ request()->routeIs('reservation_allowances') || request()->routeIs('reservation_allowances.index') || request()->routeIs('reservation_allowances.create') || request()->routeIs('ReservationStaticsCredit.index') ? 'active' : '' }}"
                    onclick="toggleDropdown5(event)">
                    <a href="#">
                        <img src="{{ asset('frontend/images/employees.svg') }}" alt="logo">
                        <h6 class="btn5">بدل حجز <i class="fa-solid fa-angle-down"></i></h6>
                    </a>
                    <div id="dropdownMenu5" class="dropdown-menu5">
                        <ul>
                            <div class="row col-12">
                                <div class="col-12">
                                    <li
                                        class="{{ request()->routeIs('reservation_allowances.create') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo"
                                            style="margin-left: 7px;">
                                        <a href="{{ route('reservation_allowances.search_employee_new') }}">اضافة بدل
                                            حجز اختيارى</a>
                                    </li>
                                    <li
                                        class="{{ request()->routeIs('reservation_allowances.create') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo"
                                            style="margin-left: 7px;">
                                        <a href="{{ route('reservation_allowances.create.all') }}">اضافة بدل حجز
                                            بالهويات</a>
                                    </li>
                                    <li
                                        class="{{ request()->routeIs('reservation_allowances.create') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo"
                                            style="margin-left: 7px;">
                                        <a href="{{ route('reservation_allowances.index') }}">عرض موظفين بدل الحجز</a>
                                    </li>

                                    @if (auth()->check() && auth()->user()->rule_id == 2)
                                    <li class="{{ request()->routeIs('reservation_fetch.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo"
                                            style="margin-left: 7px;">
                                        <a href="{{ route('reservation_fetch.index') }}">بحث بدل حجز</a>
                                    </li>
                                    @endif
                                    

                                    @if (auth()->check() && auth()->user()->rule_id == 2)
                                    <li class="{{ request()->routeIs('reservation_fetch.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo"
                                            style="margin-left: 7px;">
                                        <a href="{{ route('Reserv_statistic_sector.index') }}">احصائيات بدل حجز</a>
                                    </li>
                                    @endif

                                    @if (auth()->check() && auth()->user()->rule_id == 2)
                                    <li class="{{ request()->routeIs('reservation_report.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo" style="margin-left: 7px;">
                                        <a href="{{ route('reserv_report.index') }}">تقارير بدل حجز</a>
                                    </li>
                                    @endif

                                </div>
                            </div>
                        </ul>
                    </div>
                </li>




                {{-- @if (Auth::user()->hasPermission('view sectors')) --}}
                @if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2 || Auth::user()->rule->id == 4)
                    <li
                        class="nav-item {{ request()->routeIs('sectors.index') ? 'active' : '' }} @isset($search) @if ($search == 'dept') active @endif @endisset">
                        <a href="{{ route('sectors.index') }}">
                            <img src="{{ asset('frontend/images/managements.svg') }}" alt="logo">
                            <h6>القطاعات</h6>
                        </a>
                    </li>
                @else
                    <li
                        class="nav-item {{ request()->routeIs('departments.index') ? 'active' : '' }} @isset($search) @if ($search == 'dept') active @endif @endisset">
                        <a href="{{ route('departments.index', ['id' => Auth::user()->sector]) }}">
                            <img src="{{ asset('frontend/images/managements.svg') }}" alt="logo">
                            <h6>الأدارات</h6>
                        </a>
                    </li>
                @endif

                {{-- @endif --}}
                @if (Auth::user()->hasPermission('view job') ||
                        Auth::user()->hasPermission('view VacationType') ||
                        Auth::user()->hasPermission('view grade') ||
                        Auth::user()->hasPermission('view Government') ||
                        Auth::user()->hasPermission('view Rule') ||
                        Auth::user()->hasPermission('view Permission'))
                    <li class="nav-item {{ request()->routeIs('grads.index') || request()->routeIs('job.index') || request()->routeIs('qualifications.index') || request()->routeIs('government.all') || request()->routeIs('regions.index') || request()->routeIs('sectors.index') || request()->routeIs('points.index') || request()->routeIs('vacationType.index') || request()->routeIs('violations.index') || request()->routeIs('rule.index') || request()->routeIs('permission.index') || request()->routeIs('working_time.index') || request()->routeIs('working_trees.list') || request()->routeIs('absence.index') ? 'active' : '' }}"
                        onclick="toggleDropdown4(event)">
                        <a href="#">
                            <img src="{{ asset('frontend/images/settings.svg') }}" alt="logo">
                            <h6 class="btn4">الإعدادات <i class="fa-solid fa-angle-down"></i></h6>
                        </a>
                        <div id="dropdownMenu4" class="dropdown-menu4">
                            <ul>
                                <div class="row col-12 d-flex justify-content-around">
                                    <div class="col-6">
                                        @if (Auth::user()->hasPermission('view Setting'))
                                            <li class="{{ request()->routeIs('settings.index') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/police.svg') }}" alt="logo"
                                                    style="margin-left: 7px;">
                                                <a href="{{ route('settings.index') }}">الاعدادات</a>
                                            </li>
                                        @endif
                                        @if (Auth::user()->hasPermission('view grade'))
                                            <li class="{{ request()->routeIs('grads.index') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/police.svg') }}" alt="logo"
                                                    style="margin-left: 7px;">
                                                <a href="{{ route('grads.index') }}">الرتب العسكرية</a>
                                            </li>
                                        @endif
                                        @if (Auth::user()->hasPermission('view job'))
                                            <li class="{{ request()->routeIs('job.index') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/jobs.svg') }}" alt="logo"
                                                    style="margin-left: 7px;">
                                                <a href="{{ route('job.index') }}">المسمى الوظيفى
                                                </a>
                                            </li>
                                        @endif
                                        {{-- @if (Auth::user()->hasPermission('view Qualification'))
                                            <li class="{{ request()->routeIs('qualifications.index') ? 'active' : '' }}">
                                    <img src="{{ asset('frontend/images/governorates.svg') }}" alt="logo"
                                        style="margin-left: 7px;">
                                    <a href="{{ route('qualifications.index') }}">المؤهلات</a>
                </li>
                @endif --}}
                                        @if (Auth::user()->hasPermission('view Government'))
                                            <li class="{{ request()->routeIs('government.all') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/governorates.svg') }}"
                                                    alt="logo" style="margin-left: 7px;">
                                                <a href="{{ route('government.all') }}">المحافظات</a>
                                            </li>
                                        @endif



                                    </div>
                                    <div class="col-6">
                                        @if (Auth::user()->hasPermission('view VacationType'))
                                            <li
                                                class="{{ request()->routeIs('vacationType.index') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/holidays.svg') }}" alt="logo"
                                                    style="margin-left: 7px;">
                                                <a href="{{ route('vacationType.index') }}">أنواع الأجازات</a>
                                            </li>
                                        @endif

                                        @if (Auth::user()->hasPermission('view VacationType'))
                                            <li class="{{ request()->routeIs('nationality.index') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/holidays.svg') }}" alt="logo"
                                                    style="margin-left: 7px;">
                                                <a href="{{ route('nationality.index') }}">الدول والجنسيات</a>
                                            </li>
                                        @endif


                                        @if (Auth::user()->hasPermission('view Rule'))
                                            <li class="{{ request()->routeIs('rule.index') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/task.svg') }}" alt="logo"
                                                    style="margin-left: 7px;">
                                                <a href="{{ route('rule.index') }}">المهام</a>
                                            </li>
                                        @endif
                                        @if (Auth::user()->hasPermission('view Permission'))
                                            <li class="{{ request()->routeIs('permission.index') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/permission.svg') }}"
                                                    alt="logo" style="margin-left: 7px;">
                                                <a href="{{ route('permission.index') }}">الصلاحيات</a>
                                            </li>
                                        @endif
                                        {{--  @if (Auth::user()->hasPermission('view WorkingTime'))

            <li class="{{ request()->routeIs('working_time.index') ? 'active' : '' }}">
                <img src="{{ asset('frontend/images/governorates.svg') }}" alt="logo" style="margin-left: 7px;">
                <a href="{{ route('working_time.index') }}">فترات العمل</a>
            </li>
            @endif

            @if (Auth::user()->hasPermission('view WorkingTree'))

            <li class="{{ request()->routeIs('working_trees.list') ? 'active' : '' }}">
                <img src="{{ asset('frontend/images/permission.svg') }}" alt="logo" style="margin-left: 7px;">
                <a href="{{ route('working_trees.list') }}">نظام العمل</a>
            </li>
            @endif --}}

                                        {{--    @if (Auth::user()->hasPermission('view Absence'))

            <li class="{{ request()->routeIs('absence.index') ? 'active' : '' }}">
                <img src="{{ asset('frontend/images/permission.svg') }}" alt="logo" style="margin-left: 7px;">
                <a href="{{ route('absence.index') }}">مسميات العجز
                </a>
            </li>
            @endif --}}
                                        {{--   @if (Auth::user()->hasPermission('view Region'))
                                            <li class="{{ request()->routeIs('regions.index') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/governorates.svg') }}"
                                                    alt="logo" style="margin-left: 7px;">
                                                <a href="{{ route('regions.index', ['id' => 0]) }}">المناطق</a>
                                            </li>
                                        @endif --}}

                                        {{--  @if (Auth::user()->hasPermission('view Sector'))
                                            <li class="{{ request()->routeIs('sectors.index') ? 'active' : '' }}">
                                                <img src="{{ asset('frontend/images/governorates.svg') }}"
                                                    alt="logo" style="margin-left: 7px;">
                                                <a href="{{ route('sectors.index') }}">القطاعات</a>
                                            </li>
                                        @endif --}}



                                    </div>
                                </div>
                            </ul>
                        </div>
                    </li>
                @endif
                {{--  @if (Auth::user()->hasPermission('view Iotelegram'))
                    <li class="nav-item {{ request()->routeIs('iotelegrams.list') ? 'active' : '' }}">
                        <a href="{{ route('iotelegrams.list') }}">
                            <img src="{{ asset('frontend/images/imports.svg') }}" alt="logo">
                            <h6>الوارد</h6>
                        </a>
                    </li>
                @endif --}}
                {{--   @if (Auth::user()->hasPermission('view outgoings'))
                    <li class="nav-item {{ request()->routeIs('Export.index') ? 'active' : '' }}">
                        <a href="{{ route('Export.index') }}">
                            <img src="{{ asset('frontend/images/exports.svg') }}" alt="logo">
                            <h6>الصادر</h6>
                        </a>
                    </li>
                @endif --}}

            </ul>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Close dropdowns on page load
        document.getElementById('dropdownMenu').style.display = 'none';
        document.getElementById('dropdownMenu2').style.display = 'none';
        document.getElementById('dropdownMenu4').style.display = 'none';
        document.getElementById('dropdownMenu5').style.display = 'none';

        // Optional: Close dropdowns if they are open on page load
        function closeDropdowns() {
            let dropdowns = document.querySelectorAll(
                '.dropdown-menu, .dropdown-menu2, .dropdown-menu4, .dropdown-menu5');
            dropdowns.forEach(function(dropdown) {
                dropdown.style.display = 'none';
            });
        }

        // Attach closeDropdowns function to window events
        window.addEventListener('load', closeDropdowns);
    });
</script>
<script>
    function toggleDropdown() {
        var dropdown = document.getElementById('dropdownMenu');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    }

    function toggleDropdown2() {
        var dropdown = document.getElementById('dropdownMenu2');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    }

    function toggleDropdown3(event) {
        var dropdown = document.getElementById('dropdownMenu3');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        event.stopPropagation(); // Prevent closing other dropdowns
    }

    function toggleDropdown4(event) {
        var dropdown = document.getElementById('dropdownMenu4');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        event.stopPropagation(); // Prevent closing other dropdowns
    }

    function toggleDropdown5(event) {
        var dropdown = document.getElementById('dropdownMenu5');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        event.stopPropagation(); // Prevent closing other dropdowns
    }

    // Close dropdowns if clicked outside
    document.addEventListener('click', function(event) {
        let dropdowns = document.querySelectorAll(
            '.dropdown-menu, .dropdown-menu2, .dropdown-menu4, .dropdown-menu5');
        dropdowns.forEach(function(dropdown) {
            if (!dropdown.contains(event.target) && !event.target.closest('.btn')) {
                dropdown.style.display = 'none';
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#search-btn').on('click', function() {
            var query = $('#q').val();
            var search = $('#search').val();
            console.log(query);
            // Perform an AJAX request to search

            document.location = "{{ url('search') }}/" + search + "/" + query;
            /*  $.ajax({
                // Replace with your search endpoint
                 type: 'GET',
                 data: { q: query,search:search },
                 success: function(data) {
                     // Assuming data is the HTML or JSON response with search results
                     $('#searchResults').html(data);
                 },
                 error: function(xhr, status, error) {
                     console.log('Search failed: ', error);
                 }
             }); */
        });

        // Optional: Trigger search on 'Enter' key press
        $('#q').on('keypress', function(e) {
            if (e.which === 13) { // 13 is the Enter key code
                $('#search-btn').click();
            }
        });
    });
</script>
