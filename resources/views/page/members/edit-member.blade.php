@include('layout.header')
@include('layout.sidebar')
<!-- app-content start-->
<div class="app-content main-content">
    <div class="side-app">
        <style>
            /* Increase the size of the day cells */
            .ui-datepicker-calendar td {
                font-size: 22px;
                /* Adjust the padding as needed */
            }

            .ui-datepicker-calendar a {
                font-size: 22px !important;

                /* Adjust the padding as needed */
            }

            /* Increase the size of the month/year dropdowns */
            .ui-datepicker select.ui-datepicker-year,
            .ui-datepicker select.ui-datepicker-month {
                font-size: 22px;
                /* Adjust the font size as needed */
            }

            .ui-datepicker-calendar {
                width: 300px;
                height: 300px;
                /* Set the height to 100% */
            }

            .form-control {
                background-color: white;
                font-size: medium;
                color: black;
            }

            .form-label {
                font-size: medium;
            }

            .select2 {
                background-color: white;
                font-size: medium;
                color: black;
            }
        </style>
        <div class="container-fluid main-container">

            <!--Page header-->
            <div class="page-header">
                <div class="page-leftheader">
                    <h4 class="page-title"></h4>
                </div>
            </div>
            <!--End Page header-->

            <!-- Row -->
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <div class="card">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @elseif(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @elseif(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        <form action="{{ route('members.detail-update', ['id' => $member->id]) }}" method="POST">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Member Personal Details</h3>
                                <div>
                                    <button type='submit' class='btn btn-primary'>
                                        Save Member Details
                                    </button>
                                        <a class="btn btn-info"
                                            href="{{ route('members.view-member', ['id' => $member->id]) }}">
                                            <i class="fa fa-arrow-circle-left" style="font-size:20px;"> Back</i>
                                        </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="card-body">
                                    <div class="row">
                                        @csrf
                                        <div class="col-lg-6">
                                            <div class="mb-3 row">
                                                <label class="col-md-4 form-label">Title <span
                                                        class="tx-danger">*</span></label>
                                                <div class="col-md-3">
                                                    <select name="title" class="form-control form-select select2"
                                                        id="title">
                                                        @forelse ($data['titles'] as $title)
                                                            <option value="{{ $title?->id }}"
                                                                @if ($title?->id == $member?->title_id) selected @endif>
                                                                {{ $title?->name }}
                                                            </option>
                                                        @empty
                                                            <option value="">Select Title</option>
                                                        @endforelse
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input class="form-control" type="text" id="title_detail"
                                                        placeholder="Type Title Here" name="title_detail"
                                                        value="{{ $member?->title_detail }}">
                                                </div>
                                            </div>

                                            <div class="mb-3 row">
                                                <label class="col-md-4 form-label">Family Name <span
                                                        class="tx-danger">*</span></label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="text" placeholder="Family Name"
                                                        name="family_name" value="{{ $member?->family_name }}">
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label class="col-md-4 form-label">Given Name <span
                                                        class="tx-danger">*</span></label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="text" placeholder="Given Name"
                                                        value="{{ $member?->given_name }}" name="given_name">
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label class="col-md-4 form-label">Preferred Name </label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="text"
                                                        placeholder="Preferred Name"
                                                        value="{{ $member?->preferred_name }}" name="preferred_name">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-3 row">
                                                <label class="col-md-4 form-label">Initials</label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="text"
                                                        placeholder="Enter Initials" value="{{ $member?->initials }}"
                                                        name="initials" {{ auth()->user()->role_id == 1 ? '' : 'readonly' }}>
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label class="col-md-4 form-label">Post Nominal</label>
                                                <div class="col-md-8">
                                                    <input class="form-control" type="text"
                                                        placeholder="Enter Post Nominal"
                                                        value="{{ $member?->post_nominal }}" name="post_nominal" {{ auth()->user()->role_id == 1 ? '' : 'readonly' }}>
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label class="col-md-4 form-label">Birth Date </label>
                                                <div class="col-md-8">
                                                    <div class="row">
                                                        <div class="col-4 pr-1">
                                                            <input class="form-control"
                                                                value="{{ $member?->year_of_birth }}" type="text"
                                                                name="year_of_birth">
                                                        </div>
                                                        <div class="col-4 px-1">
                                                            <select class="form-control month-select"
                                                                id="month_of_birth" name="month_of_birth"></select>
                                                        </div>
                                                        <div class="col-4 px-1">
                                                            <select class="form-control day-select" id="date_of_birth"
                                                                name="date_of_birth"></select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-header justify-content-between">
                                <h3 class="card-title">Member Contact Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Country </label>
                                            <div class="col-md-8">
                                                <select class="form-control select2" id="countries_select2"
                                                    name="country_id">
                                                    @if (!empty($member?->address?->country?->id))
                                                        <option value="{{ $member?->address?->country?->id }}"
                                                            selected>{{ $member?->address?->country?->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">State / County</label>
                                            <div class="col-md-8">
                                                <select class="form-control select2" id="counties_select2"
                                                    name="county_id">
                                                    @if (!empty($member?->address?->state?->id))
                                                        <option value="{{ $member?->address?->state?->id }}" selected>
                                                            {{ $member?->address?->state?->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">City / Town / Suburb </label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="text"
                                                    placeholder="City / Town / Suburb"
                                                    value="{{ $member?->address?->suburb }}" name="city_id">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Address Line 1</label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="text"
                                                    placeholder="Address Line 1"
                                                    value="{{ $member?->address?->unit_no }}" name="unit_no">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Address Line 2</label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="text" placeholder="Address Line 2"
                                                    value="{{ $member?->address?->number_street }}"
                                                    name="number_street">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Post Code </label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control" placeholder="Post Code"
                                                    value="{{ $member?->address?->post_code }}" name="post_code">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Home Phone (including Area Code)</label>
                                            <div class="col-md-8">
                                                <div class="input-group">
                                                    <input class="form-control" type="text"
                                                        placeholder="Area Code"
                                                        value="{{ $member?->contact?->area_code }}" name="area_code"
                                                        style="width: 25%; margin-right: 10px;">
                                                    <input class="form-control" type="text"
                                                        value="{{ $member?->contact?->phone }}" placeholder="Phone"
                                                        name="phone" style="width: calc(75% - 10px);">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Mobile Phone</label>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control" placeholder="Mobile Phone"
                                                    value="{{ $member?->contact?->mobile }}" name="mobile">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Email Address </label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="text"
                                                    placeholder="Email Address"
                                                    value="{{ $member?->contact?->email }}" name="email">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">General
                                                Notes</label>
                                            <div class="col-md-8">
                                                <textarea class="form-control" name="general_notes" rows="5" placeholder="General Notes">{{ $member?->additionalInfo?->general_notes }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-header justify-content-between">
                                <h3 class="card-title">Membership Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Membership
                                                Number</label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="text"
                                                    placeholder="Membership Number"
                                                    value="{{ $member?->additionalInfo?->membership_number }}"
                                                    name="membership_number" {{ auth()->user()->role_id == 1 ? '' : 'readonly' }}>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Username </label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="text" placeholder="User Name"
                                                    value="{{ $member?->username }}" name="username" {{ auth()->user()->role_id == 1 ? '' : 'readonly' }}>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            
                                            <label class="col-md-4 form-label">Membership Type </label>
                                            <div class="col-md-8">
                                                <select name="member_type_id" class="form-control form-select select2"
                                                    id="member_type_id" {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}>
                                                    @forelse ($data['membership_types'] as $type)
                                                        <option value="{{ $type?->id }}"
                                                            @if ($type?->id == $member?->member_type_id) selected @endif>
                                                            {{ $type?->name }}
                                                        </option>
                                                    @empty
                                                        <option value="">Select Membership Type
                                                        </option>
                                                    @endforelse
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Membership Status
                                            </label>
                                            <div class="col-md-8">
                                                <select name="member_status_id"
                                                    class="form-control form-select select2" id="member_status_id" {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}>
                                                    @forelse ($data['membership_status'] as $status)
                                                        <option value="{{ $status?->id }}"
                                                            @if ($status?->id == $member?->member_status_id) selected @endif>
                                                            {{ $status?->name }}
                                                        </option>
                                                    @empty
                                                        <option value="">Select Membership Status
                                                        </option>
                                                    @endforelse
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Approval Date </label>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4 pr-1">
                                                        <input class="form-control"
                                                            value="{{ $member?->additionalInfo?->year_membership_approved }}"
                                                            type="text" name="year_membership_approved" {{ auth()->user()->role_id == 1 ? '' : 'readonly' }}>
                                                    </div>
                                                    <div class="col-4 px-1">
                                                        <select class="form-control month-select"
                                                            id="month_membership_approved"
                                                            name="month_membership_approved" {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}></select>
                                                    </div>
                                                    <div class="col-4 px-1">
                                                        <select class="form-control day-select"
                                                            id="date_membership_approved"
                                                            name="date_membership_approved" {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}></select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Date Membership Ended </label>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-4 pr-1">
                                                        <input class="form-control"
                                                            value="{{ $member?->additionalInfo?->year_membership_end }}"
                                                            type="text" name="year_membership_end" {{ auth()->user()->role_id == 1 ? '' : 'readonly' }}>
                                                    </div>
                                                    <div class="col-4 px-1">
                                                        <select class="form-control month-select"
                                                            id="month_membership_end"
                                                            name="month_membership_end" {{ auth()->user()->role_id == 1 ? '' : 'disabled' }} ></select>
                                                    </div>
                                                    <div class="col-4 px-1">
                                                        <select class="form-control day-select"
                                                            id="date_membership_end"
                                                            name="date_membership_end" {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}></select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">End Status
                                                Notes</label>
                                            <div class="col-md-8">
                                                <textarea class="form-control" name="end_status_notes" rows="3" placeholder="End Status Notes" {{ auth()->user()->role_id == 1 ? '' : 'readonly' }}>{{ $member?->additionalInfo?->end_status_notes }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3 row">
                                            <label class="col-md-3 form-label">Journal<span
                                                    class="tx-danger">*</span></label>
                                            <div class="col-md-4">
                                                <div class="mb-3 mb-sm-0 d-flex align-items-center">
                                                    <label class="form-label mb-0 me-2">Emailed</label>
                                                    <input id="emailed" type="radio" class="radio-input"
                                                        name="journal" value="0"
                                                        @if ($member?->journal == 0) checked @endif>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3 mb-sm-0 d-flex align-items-center">
                                                    <label class="form-label mb-0 me-2">Posted</label>
                                                    <input id="posted" type="radio" class="radio-input"
                                                        name="journal" value="1"
                                                        @if ($member?->journal == 1) checked @endif>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-6 form-label">Registration form
                                                received</label>
                                            <div class="col-md-6">
                                                <input id="registration_form_received" type="checkbox"
                                                    class="checkbox-input" name="registration_form_received"
                                                    value='1' @if ($member?->additionalInfo?->registration_form_received == 1) checked @endif {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-6 form-label">Signed Confidentiality
                                                agreement
                                                received</label>
                                            <div class="col-md-6">
                                                <input id="signed_agreement" type="checkbox" class="checkbox-input"
                                                    name="signed_agreement" value='1'
                                                    @if ($member?->additionalInfo?->signed_agreement == 1) checked @endif {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Key Holder</label>
                                            <div class="col-md-8">
                                                <input id="key_holder" type="checkbox" class="checkbox-input"
                                                    name="key_holder" value='1'
                                                    @if ($member?->additionalInfo?->key_holder == 1) checked @endif {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}>
                                            </div>
                                        </div>
                                        <div class="mb-3 row key_held" style="display: none;">
                                            <label class="col-md-4 form-label">Key Held</label>
                                            <div class="col-md-8">
                                                <textarea class="form-control" name="key_held" rows="3" placeholder="Key Held" {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}>{{ $member?->additionalInfo?->key_held }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="mb-3 row">
                                            <label class="col-md-4 form-label">Volunteer</label>
                                            <div class="col-md-8">
                                                <input id="volunteer" type="checkbox" class="checkbox-input"
                                                    name="volunteer" value='1'
                                                    @if ($member?->additionalInfo?->volunteer == 1) checked @endif {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="volunteer_details" style="display: none;">
                                            <div class="mb-3 row">
                                                <label class="col-md-4 form-label">Volunteer Experience</label>
                                                <div class="col-md-12">
                                                    <textarea class="form-control" name="experience" rows="10" {{ auth()->user()->role_id == 1 ? '' : 'disabled' }}>{{ $member?->volunteerDetails?->experience }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL EFFECTS -->
<div id="crud"></div>
@section('scripts')
    @include('plugins.select2')
    <script>
        const existingMonthOfBirth = "{{ $member->month_of_birth }}";
        const existingDayOfBirth = "{{ $member->date_of_birth }}";

        const existingMonthOfMembershipApproval = "{{ $member?->additionalInfo?->month_membership_approved }}";
        const existingDayofMembershipApproval = "{{ $member?->additionalInfo?->date_membership_approved }}";

        const existingMonthOfMembershipEnd = "{{ $member?->additionalInfo?->month_membership_end }}";
        const existingDayofMembershipEnd = "{{ $member?->additionalInfo?->date_membership_end }}";

        $(document).ready(function() {

            initMonthSelect2($('#month_of_birth'), existingMonthOfBirth);
            initDaySelect2($('#date_of_birth'), existingDayOfBirth);

            initMonthSelect2($('#month_membership_approved'), existingMonthOfMembershipApproval);
            initDaySelect2($('#date_membership_approved'), existingDayofMembershipApproval);

            initMonthSelect2($('#month_membership_end'), existingMonthOfMembershipEnd);
            initDaySelect2($('#date_membership_end'), existingDayofMembershipEnd);

            $("#title_detail").hide();

            var otherValue = "Other";

            $("#title").change(function() {
                if ($(this).val() === otherValue) {
                    $("#title_detail").show();
                } else {
                    $("#title_detail").hide();
                }
            });

            $("#title").trigger("change");
        });


        $("#title").select2();
        $("#state").select2();
        $("#member_type_id").select2();
        $("#member_status_id").select2();
        $("#country").select2();
        var dt_ship_elem = $("#ship-table"),
            dt_ship = "";
        window.addEventListener("DOMContentLoaded", function() {
            if (typeof initShipSelect !== "undefined") {
                initShipSelect();
            }
            if (typeof initCountiesSelect !== "undefined") {
                initCountiesSelect();
            }
            if (typeof initPortsSelect !== "undefined") {
                initPortsSelect();
            }

            $(document).on("change", "#daterange-btn", function() {
                initDataTable()
            });
            $(document).on("submit", "#crud form", function() {
                initDataTable()
            });
            $('#volunteer').change(function() {
                if ($(this).prop('checked')) {
                    $('.volunteer_details').show();
                } else {
                    $('.volunteer_details').hide();
                }
            });
            if ($("#volunteer").prop('checked')) {
                $('.volunteer_details').show();
            } else {
                $('.volunteer_details').hide();
            }

            $('#key_holder').change(function() {
                if ($(this).prop('checked')) {
                    $('.key_held').show();
                } else {
                    $('.key_held').hide();
                }
            });

            if ($("#key_holder").prop('checked')) {
                $('.key_held').show();
            } else {
                $('.key_held').hide();
            }

        });
    </script>
    @include('page.mode-of-arrivals.scripts')
@endsection
<!-- app-content end-->
@include('layout.footer')
