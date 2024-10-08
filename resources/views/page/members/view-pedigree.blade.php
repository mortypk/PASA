@include('layout.header')
@include('layout.sidebar')
<!-- app-content start-->
<div class="app-content main-content">
    <div class="side-app">
        <style>
            .row {
                margin-bottom: 1rem;
            }

            .form-control-label {
                font-size: 16px;
                font-weight: 500;
            }

            .form-control[:read-only] {
                font-size: 16px;
            }

            .col-md-2 {
                margin-top: 1rem;
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

                        <div class="card-header justify-content-between">
                            <h3 class="card-title">Pioneer Member's Pedigree Chart</h3>
                            <div>
                                <button class="btn btn-info" onclick="downloadExcel()">
                                    <i class="fa fa-file-excel-o" style="font-size:20px;"> Download</i>
                                </button>
                                @if (Auth::user()->name == 'Admin')
                                    <a class="btn btn-danger" href="{{ route('members.index') }}">
                                        <i class="fa fa-home" style="font-size:20px;"> Home</i>
                                    </a>
                                @endif
                                @if (count($member->pedigree) > 0)
                                    <a class="btn btn-success mr-2"
                                        href="{{ route('members.editPedigree', $member->id) }}">
                                        <i class="pe-7s-pen btn-icon-wrapper" style="font-size:20px;"> Edit</i>
                                    </a>
                                @else
                                    <a class="btn btn-success mr-2"
                                        href="{{ route('members.addPedigree', $member->id) }}">
                                        <i class="pe-7s-pen btn-icon-wrapper" style="font-size:20px;"> Add</i>
                                    </a>
                                @endif
                                <a class="btn btn-info" href="{{ url()->current() }}" id="view-members">
                                    <i class="fa fa-arrow-circle-left" style="font-size:20px;"> Back</i>
                                </a>
                            </div>
                        </div>
                        <div id="print-content" class="print-area">
                            @if (count($member->pedigree) > 0)
                                <div class="card-body p-0">
                                    <div class="card-body">
                                        @php
                                            $gen_seq = 1;
                                        @endphp
                                        @foreach ($member->pedigree as $pedigree)
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <a class="form-control-label"
                                                        style="color: #022ff8; font-size:16px">Generation x
                                                        {{ $gen_seq }}</a>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Father Name</label>
                                                    <input class="form-control" value="{{ $pedigree->f_name }}"
                                                        type="text" readonly disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Birth Date</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->date_of_birth ?? '' }}" readonly disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Birth Place</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->place_of_birth ?? '' }}" readonly
                                                        disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Death Date</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->date_of_death ?? '' }}" readonly disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Death Place</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->place_of_death ?? '' }}" readonly
                                                        disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Marriage Date</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->date_of_marriage ?? '' }}" readonly
                                                        disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Mother Name</label>
                                                    <input class="form-control" id="email"
                                                        value="{{ $pedigree->m_name }}" type="text" readonly
                                                        disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Birth Date</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->m_birth_date ?? '' }}" readonly disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Birth Place</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->m_birth_place ?? '' }}" readonly disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Death Date</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->m_death_date ?? '' }}" readonly disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Death Place</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->m_death_place ?? '' }}" readonly
                                                        disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Marriage Place</label>
                                                    <input class="form-control" type="text"
                                                        value="{{ $pedigree->place_of_marriage ?? '' }}" readonly
                                                        disabled>
                                                </div>
                                                <div><br></div>
                                                <div class="col-md-12">
                                                    <label class="form-control-label">Additional Notes</label>
                                                    <textarea class="form-control" rows="4" readonly disabled>{{ $pedigree->notes ?? '' }}</textarea>
                                                </div>
                                            </div>
                                            <br>
                                            <br>
                                            @php
                                                $gen_seq++;
                                            @endphp
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="card">
                                    <div class="card-header justify-content-between">
                                        <h3 class="card-title">No Pedigrees Found</h3>
                                    </div>
                                </div>
                            @endif
                        </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        // Function to export pedigree data to Excel
        function downloadExcel() {
            // Prepare data for Excel
            var data = [
                ["Generation", "Father Name", "Birth Date", "Birth Place", "Death Date", "Death Place", "Marriage Date",
                    "Mother Name", "Mother Birth Date", "Mother Birth Place", "Mother Death Date", "Mother Death Place",
                    "Marriage Place", "Additional Notes"
                ],
                @foreach ($member->pedigree as $pedigree)
                    [
                        "Generation {{ $loop->iteration }}",
                        "{{ $pedigree->f_name }}",
                        "{{ $pedigree->date_of_birth ?? '' }}",
                        "{{ $pedigree->place_of_birth ?? '' }}",
                        "{{ $pedigree->date_of_death ?? '' }}",
                        "{{ $pedigree->place_of_death ?? '' }}",
                        "{{ $pedigree->date_of_marriage ?? '' }}",
                        "{{ $pedigree->m_name }}",
                        "{{ $pedigree->m_birth_date ?? '' }}",
                        "{{ $pedigree->m_birth_place ?? '' }}",
                        "{{ $pedigree->m_death_date ?? '' }}",
                        "{{ $pedigree->m_death_place ?? '' }}",
                        "{{ $pedigree->place_of_marriage ?? '' }}",
                        "{{ $pedigree->notes ?? '' }}"
                    ],
                @endforeach
            ];

            // Create a worksheet from the data
            var ws = XLSX.utils.aoa_to_sheet(data);

            // Create a new workbook and append the worksheet
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Pedigree");

            // Generate Excel file and trigger download
            XLSX.writeFile(wb, "pedigree_data.xlsx");
        }

        document.getElementById('view-members').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default behavior of the link

            // Extract the current URL and the id from it
            var currentUrl = window.location.href;
            var id = currentUrl.substring(currentUrl.lastIndexOf('/') + 1);

            // Construct the new URL for editing
            var newUrl = currentUrl.replace('/view-pedigree/', '/view-member/');

            // Redirect to the new URL
            window.location.href = newUrl;
        });
    </script>
@endsection

<!-- app-content end-->
@include('layout.footer')
