 <!-- External CSS -->
 <link href="https://unpkg.com/tabulator-tables@5.1.5/dist/css/tabulator.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

 <!-- External JavaScript Libraries -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script src="https://unpkg.com/tabulator-tables@5.1.5/dist/js/tabulator.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
 <script type="text/javascript" src="https://oss.sheetjs.com/sheetjs/xlsx.full.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

 @include('layout.header')
 @include('layout.sidebar')
 <!-- app-content start-->
 <div class="app-content main-content">
     <style>
         .tabulator-toolbar {
             background-color: white;
             padding: 15px;
             border-radius: 5px;
             margin-bottom: 10px;
         }

         .tabulator {
             font-size: 15px;
             border: none;
         }

         .tabulator-col-title {
             text-align: center;
             font-size: 16px;
             padding: 8px;
             background-color: #D3D3D3;
             border: none;
         }

         .tabulator-row .tabulator-cell {
             border-right: none;
         }

         .tabulator-header-filter input {
             text-align: center;
             font-size: 14px;
         }

         .tabulator-tableholder {
             background-color: white;
         }

         .custom-button {
             padding: 10px;
             width: 150px;
             height: 45px;
             border: none;
             border-radius: 5px;
             background-color: #0d6efd;
             color: white;
             cursor: pointer;
         }

         .custom-button:hover {
             background-color: #45a049;
         }

         .button-container {
             display: flex;
         }

         .button-container button {
             padding: 12px 12px;
             margin: 5px;
             cursor: pointer;
             border: none;
             border-radius: 5px;
             font-size: 14px;
         }

         .button-container button.edit-button {
             background-color: #3498db;
             /* Blue color for Edit button */
             color: #fff;
         }

         .button-container button.view-button {
             background-color: #2ecc71;
             /* Green color for View button */
             color: #fff;
         }

         .button-container button:hover {
             opacity: 0.8;
             /* Reduce opacity on hover */
         }
     </style>
     <div class="side-app">
         <div class="container-fluid main-container">
             <div class="page-header">
                 <div class="page-leftheader">
                     <h3 class="page-title">Cities</h3>
                 </div>
                 <div class="card-header d-flex justify-content-between align-items-center">
                     <a class="btn btn-primary" href="javascript:void(0)" id="create-record">
                         <i class="fa fa-plus-circle" style="font-size:24px;"></i>
                     </a>
                 </div>
             </div>
             <div class="row">
                 <div class="col-md-12 p-12">
                     @if ($errors->any() || session('error') || session('success'))
                     <div class="card">
                         <div class="card-body">
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
                         </div>
                     </div>
                     @endif
                     <div class="card">
                         <div class="card-body p-2">
                             <div class="tabulator-toolbar">
                                 Show <select style="padding:10px;" id="pageSizeDropdown">
                                     <option value="25">25</option>
                                     <option value="50">50</option>
                                     <option value="100">100</option>
                                     <option value="1000000">ALL</option>
                                 </select>
                                 <button class="custom-button" type="button" id="printTable" onclick="printData()">Print</button>
                                 <button class="custom-button" id="download-csv">Download CSV</button>
                                 <button class="custom-button" id="download-xlsx">Download EXCEL</button>
                                 <button class="custom-button" id="download-pdf">Download PDF</button>
                                 <button class="custom-button" id="reset-button">Reset Filter</button>
                             </div>
                             <div class="table-responsive">
                                 <div id="cities-table"></div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>
 <!-- MODAL EFFECTS -->
 <div id="crud"></div>
 @include('plugins.select2')
 @section('scripts')
 <script>
     var myData = @json($data);
     var country = @json($country);
     var county = @json($county);

     var table = new Tabulator("#cities-table", {
         data: myData,
         layout: "fitColumns",
         columns: [
            //{
            //     title: 'ID',
            //     field: 'id',
            //     headerFilter: 'input',
            //     hozAlign: 'center',
            //     vertAlign: "middle",
            //     headerFilterPlaceholder: 'Search by ID'
            // },
             {
                 title: 'City',
                 field: 'name',
                 headerFilter: 'input',
                 hozAlign: 'center',
                 vertAlign: "middle",
                 headerFilterPlaceholder: 'Search by City'
             },
             {
                 title: 'State / County',
                 field: 'county.name',
                 hozAlign: 'center',
                 vertAlign: "middle",
                 headerFilter: "select",
                 headerFilterPlaceholder: 'Search by County',
                 headerFilterParams: {
                     values: county
                 }
             },
             {
                 title: 'Country',
                 field: 'county.country.name',
                 hozAlign: 'center',
                 vertAlign: "middle",
                 headerFilter: "select",
                 headerFilterPlaceholder: 'Search by Country',
                 headerFilterParams: {
                     values: country
                 }
             },
             {
                 title: "Action",
                 field: "actions",
                 hozAlign: "center",
                 vertAlign: "middle",
                 width: "8%",
                 formatter: function(cell, formatterParams, onRendered) {
                     var id = cell.getData().id;

                     // Add buttons for each row
                     return '<div class="button-container">' +
                         '<button class="fa fa-eye view-button" id="view-record" data-id="' + id +
                         '"></button>' +
                         '<button class="fa fa-edit edit-button" data-id="' + id + '"></button>' +
                         '</div>';
                 }
             }
         ],
         pagination: 'local',
         paginationSize: 10,
         placeholder: "No Data Available"
     });

     $('#create-record').click(function() {
         $.get("{{ route('cities.create') }}", form => {
             $('#crud').html(form.html);
             initCountiesSelect();
             $('#crud').find(".modal").modal('show');
         });
     });

     // Attach the event listener directly to the table element
     document.getElementById('cities-table').addEventListener("click", function(e) {
         if (e.target.classList.contains("view-button")) {
             var cityId = e.target.getAttribute("data-id");
             openViewModal(cityId);
         } else if (e.target.classList.contains("edit-button")) {
             var cityId = e.target.getAttribute("data-id");
             openUpdateModal(cityId);
         }
     });

     function openUpdateModal(cityId) {
         $.get("{{ route('cities.edit', ['city' => '__cityId__']) }}".replace('__cityId__', cityId), function(response) {
             $('#crud').html(response.html);
             initCountiesSelect();
             $('#crud').find(".modal").modal('show');
         });
     }

     // Function to open the view modal
     function openViewModal(cityId) {
         $.get("{{ route('cities.show', ['city' => '__city__']) }}".replace('__city__', cityId), function(response) {
             $('#crud').html(response.html);
             $('#crudModel').modal('show');
         });
     }

     function printData() {
         table.print(false, true);
     }

     // Add a reset button
     var resetButton = document.getElementById("reset-button");

     resetButton.addEventListener("click", function() {
         table.clearFilter();
         table.clearHeaderFilter();
     });

     $("#pageSizeDropdown").on("change", function() {
         var selectedPageSize = parseInt($(this).val(), 10);
         table.setPageSize(selectedPageSize);
     });

     //trigger download of data.csv file
     document.getElementById("download-csv").addEventListener("click", function() {
         table.download("csv", "Ancestor List.csv");
     });

     //trigger download of data.xlsx file
     document.getElementById("download-xlsx").addEventListener("click", function() {
         table.download("xlsx", "Ancestor List.xlsx", {
             sheetName: "PASA01",
         });
     });

     //trigger download of data.pdf file
     document.getElementById("download-pdf").addEventListener("click", function() {
         table.download("pdf", "Ancestor List.pdf", {
             orientation: "landscape",
             title: "Ancestor List",
         });
     });
 </script>
 @endsection
 <!-- app-content end-->
 @include('layout.footer')