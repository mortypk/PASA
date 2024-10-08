@extends('layouts.member-form')

@section('title', '')
@section('head')
    <script src="https://js.stripe.com/v3/"></script>
@endsection
@section('content')
    <div class="container">
        <div class="row ">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('submitMembershipAccount') }}" id="membership_form" onsubmit="return false;">
                            <div id="wizard2">
                                @include('page.member-form-wizard.account')
                                @include('page.member-form-wizard.pedigree')
                                @include('page.member-form-wizard.ancestor')
                                @include('page.member-form-wizard.payment')
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                $('.getValid').on('blur', function() {
                    var inputElement = $(this);
                    var inputValue = inputElement.val();
                    var fieldName = inputElement.attr('name');
                    var fieldConfirm = inputElement.attr('name').includes('_confirmation');
                    if (fieldConfirm) {
                        var parentName =inputElement.attr('name').replace('_confirmation','');
                        var parentField = $('[name="'+parentName+'"]');
                    }
                    inputElement.removeClass('is-invalid');
                    inputElement.next('.invalid-feedback').remove();
                    $.ajax({
                        url: '{{ route('validate.field') }}',
                        type: 'POST',
                        data: (function() {
                            let data = {
                                [fieldName]: inputValue, 
                                _token: '{{ csrf_token() }}' 
                            };
                            
                            if (fieldConfirm) {
                                data[parentName] = parentField.val(); 
                            }
                            return data;
                        })(),
                        success: function(response) {
                            if (response.errors) {
                                inputElement.addClass('is-invalid');
                                inputElement.next('.invalid-feedback').remove();
                                if(fieldConfirm){
                                    inputElement.after('<div class="invalid-feedback">' + response.errors[parentName][0] + '</div>');
                                }else{
                                    inputElement.after('<div class="invalid-feedback">' + response.errors[fieldName][0] + '</div>');
                                }
                            } else {
                                inputElement.removeClass('is-invalid');
                                inputElement.next('.invalid-feedback').remove();
                            }
                        },
                        error: function(xhr) {
                            console.log("An error occurred: " + xhr.status + " " + xhr.statusText);
                        }
                    });
                });
                $("#state").select2();
                $("#title").select2();
                $("#name_of_the_ship").select2();
                $("#place_of_arrival").select2();
                $("#gender").select2();
                $("#gender_ancestor").select2();
                $("#journal_preferred_delivery").select2();
                $("#country").select2();
            }, 1500);
        })
        var return_url = "{{ route('confirmPaymentIntent') }}"
    </script>
    <script src="{{ asset('js/form-wizard2.js') }}"></script>
@endsection