<div class="all-nav">
    <div class="upper-navbar d-flex justify-content-between px-md-5">
        <div class="second-section d-flex ">
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
                <div id="dropdownMenu" class="dropdown-menu ">
                    <a class="text-danger" href="{{ route('logout') }}">
                        <h5> خروج تسجيل <i class="fa-solid fa-right-from-bracket"></i></h5>
                    </a>
                </div>
                @else
                <button class="btn btn-2 mt-3">
                    <a href="{{ route('login') }}" style="color: #ffffff; text-decoration:none;">سجل الدخول <i
                            class="fa-solid fa-user mx-2"></i></a>
                </button>
                @endif
            </div>

        </div>
        <div class="first-section d-flex justify-content-between ">
            <h2 style="color: #FFFFFF">{{ showUserDepartment() }} -</h2>
            <h2> شئون القوة</h2>
            <img class="mt-2" src="{{ asset('frontend/images/logo.svg') }}" alt="">
        </div>
    </div>
    <div class="navbar navbar-expand-md px-md-5 mb-4" role="navigation" dir="rtl">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars side-nav"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav d-flex justify-content-between w-100">
                <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <a href="{{ route('home') }}">

                        <h5> <img src="{{ asset('frontend/images/home.png') }}" alt="logo">الرئيسية</h5>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('user.employees') ? 'active' : '' }} btn3  @isset($search) @if ($search == 'emps') active @endif @endisset"
                    onclick="toggleDropdown3(event)">

                    @if (Auth::user()->rule_id == 2)
                    <a href="{{ route('user.employees', ['employee']) }}">

                        <h5 class="btn3"> <img src="{{ asset('frontend/images/division.png') }}" alt="logo">موظفين
                            الوزارة</h5>
                    </a>
                    @endif
                    @if (Auth::user()->rule_id != 2)
                    <a href="{{ route('user.employees', ['employee']) }}">
                        <!-- <img src="{{ asset('frontend/images/employees.svg') }}" alt="logo"> -->
                        <h5 class="btn3">موظفين القوة</h5>
                    </a>
                    @endif


                </li>

                <li class="nav-item {{ request()->routeIs('reservation_allowances') || request()->routeIs('reservation_allowances.index') || request()->routeIs('reservation_allowances.create') || request()->routeIs('ReservationStaticsCredit.index') ? 'active' : '' }}"
                    onclick="toggleDropdown5(event)">
                    <a href="#">

                        <h5 class="btn5"> <img src="{{ asset('frontend/images/reservation.png') }}" alt="logo">بدل حجز
                            <i class="fa-solid fa-angle-down"></i></h5>
                    </a>
                    <div id="dropdownMenu5" class="dropdown-menu5">
                        <ul>
                            <div class="row col-12">
                                <div class="col-6">
                                    <li
                                        class="{{ request()->routeIs('reservation_allowances.create') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo">
                                        <a href="{{ route('reservation_allowances.search_employee_new') }}">اضافة بدل
                                            حجز اختيارى</a>
                                    </li>
                                    <li
                                        class="{{ request()->routeIs('reservation_allowances.create') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo">
                                        <a href="{{ route('reservation_allowances.create.all') }}">اضافة بدل حجز
                                            بالهويات</a>
                                    </li>
                                    <li
                                        class="{{ request()->routeIs('reservation_allowances.create') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo">
                                        <a href="{{ route('reservation_allowances.index') }}">عرض موظفين بدل الحجز</a>
                                    </li>


                                </div>
                                <div class="col-6">
                                    @if (auth()->check() && in_array(auth()->user()->rule_id, [2, 3, 4]))
                                    <li class="{{ request()->routeIs('reservation_fetch.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo">
                                        <a href="{{ route('reservation_fetch.index') }}">بحث بدل حجز</a>
                                    </li>
                                    @endif


                                    @if (auth()->check() && in_array(auth()->user()->rule_id, [2, 4]))
                                    <li class="{{ request()->routeIs('reservation_fetch.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo">
                                        <a href="{{ route('Reserv_statistic_sector.index') }}">احصائيات بدل حجز</a>
                                    </li>
                                    @endif

                                    @if (auth()->check() && in_array(auth()->user()->rule_id, [2, 4]))
                                    <li class="{{ request()->routeIs('reservation_report.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo">
                                        <a href="{{ route('reserv_report.index') }}">تقارير بدل حجز</a>
                                    </li>
                                    @endif
                                </div>
                            </div>
                        </ul>
                    </div>
                </li>

                @if (Auth::user()->rule->id == 1 || Auth::user()->rule->id == 2 || Auth::user()->rule->id == 4)
                <li
                    class="nav-item {{ request()->routeIs('sectors.index') ? 'active' : '' }} @isset($search) @if ($search == 'dept') active @endif @endisset">
                    <a href="{{ route('sectors.index') }}">

                        <h5> <img src="{{ asset('frontend/images/statistics.png') }}" alt="logo">القطاعات</h5>
                    </a>
                </li>
                <li
                    class="nav-item {{ request()->routeIs('user.departments') ? 'active' : '' }} @isset($search) @if ($search == 'emps') active @endif @endisset">
                    <a href="{{ route('user.departments', 'user') }}">
                        <h5> <img src="{{ asset('frontend/images/group.png') }}" alt="logo"> المستخدمين
                            والصلاحيات</h5>
                    </a>
                </li>
                @else
                <li
                    class="nav-item {{ request()->routeIs('departments.index') ? 'active' : '' }} @isset($search) @if ($search == 'dept') active @endif @endisset">
                    <a href="{{ route('departments.index', Auth::user()->department->uuid) }}">
                        <!-- <img src="{{ asset('frontend/images/managements.svg') }}" alt="logo"> -->
                        <h5>الأدارات</h5>
                    </a>
                </li>
                @endif

                @if (Auth::user()->hasPermission('view job') ||
                Auth::user()->hasPermission('view grade') ||
                Auth::user()->hasPermission('view Government') ||
                Auth::user()->hasPermission('view Rule') ||
                Auth::user()->hasPermission('view Permission'))
                <li class="nav-item {{ request()->routeIs('grads.index') || request()->routeIs('job.index') || request()->routeIs('qualifications.index') || request()->routeIs('government.all') || request()->routeIs('regions.index') || request()->routeIs('points.index') || request()->routeIs('violations.index') || request()->routeIs('rule.index') || request()->routeIs('permission.index') || request()->routeIs('working_time.index') || request()->routeIs('working_trees.list') || request()->routeIs('absence.index') ? 'active' : '' }}"
                    onclick="toggleDropdown4(event)">
                    <a href="#">
                        <h5 class="btn4"> <img src="{{ asset('frontend/images/settings.png') }}" alt="logo">الإعدادات <i
                                class="fa-solid fa-angle-down"></i></h5>
                    </a>
                    <div id="dropdownMenu4" class="dropdown-menu4">

                        <ul>
                            <div class="row col-12">
                                <div class="col-6">

                                    @if (Auth::user()->hasPermission('view Setting'))
                                    <li class="{{ request()->routeIs('settings.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo">
                                        <a href="{{ route('settings.index') }}">الاعدادات</a>
                                    </li>
                                    @endif
                                    @if (Auth::user()->hasPermission('view grade'))
                                    <li class="{{ request()->routeIs('grads.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/police.svg') }}" alt="logo">
                                        <a href="{{ route('grads.index') }}">الرتب العسكرية</a>
                                    </li>
                                    @endif
                                    @if (Auth::user()->hasPermission('view job'))
                                    <li class="{{ request()->routeIs('job.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/jobs.svg') }}" alt="logo">
                                        <a href="{{ route('job.index') }}">المسمى الوظيفى
                                        </a>
                                    </li>
                                    @endif


                                </div>
                                <div class="col-6">
                                    @if (Auth::user()->hasPermission('view Government'))
                                    <li class="{{ request()->routeIs('government.all') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/governorates.svg') }}" alt="logo">
                                        <a href="{{ route('government.all') }}">المحافظات</a>
                                    </li>
                                    @endif
                                    <li class="{{ request()->routeIs('nationality.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/holidays.svg') }}" alt="logo">
                                        <a href="{{ route('nationality.index') }}">الدول والجنسيات</a>
                                    </li>

                                    @if (Auth::user()->hasPermission('view Rule'))
                                    <li class="{{ request()->routeIs('rule.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/task.svg') }}" alt="logo">
                                        <a href="{{ route('rule.index') }}">المهام</a>
                                    </li>
                                    @endif
                                    @if (Auth::user()->hasPermission('view Permission'))
                                    <li class="{{ request()->routeIs('permission.index') ? 'active' : '' }}">
                                        <img src="{{ asset('frontend/images/permission.svg') }}" alt="logo">
                                        <a href="{{ route('permission.index') }}">الصلاحيات</a>
                                    </li>
                                    @endif
                        </ul>
                    </div>
        </div>
        </li>
        @endif
        </ul>
    </div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Close dropdowns on page load
   
    document.getElementById('dropdownMenu4').style.display = 'none';
    document.getElementById('dropdownMenu5').style.display = 'none';
    // Optional: Close dropdowns if they are open on page load
    function closeDropdowns() {
        let dropdowns = document.querySelectorAll(
            ' .dropdown-menu4, .dropdown-menu5');
        dropdowns.forEach(function(dropdown) {
            dropdown.style.display = 'none';
        });
    }
    // Attach closeDropdowns function to window events
    window.addEventListener('load', closeDropdowns);
});





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
        '.dropdown-menu4, .dropdown-menu5');
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
    });
    // Optional: Trigger search on 'Enter' key press
    $('#q').on('keypress', function(e) {
        if (e.which === 13) { // 13 is the Enter key code
            $('#search-btn').click();
        }
    });
});
</script>