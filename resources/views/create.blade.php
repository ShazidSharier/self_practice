@extends('layouts.app')
@section('link')
{{-- <link rel="stylesheet" href="/DataTables/datatables.css" /> --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }} {{ $form_type }}</div>
                <a href="{{ route('dashboard') }}">Index </a>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
                <div class="row">
                    <form action="{{  isset($student_details) ? route('update', $student_details->id)  : route('store') }}" method="post">
                        @if (isset($student_details))
                        @method('PUT')
                        @endif
                        @csrf
                        <div>
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" value="{{ isset($student_details) ? $student_details->name : '' }}" name="name" placeholder="Enter name">
                                @error('name')
                                <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                              <label for="email">Email</label>
                              <input type="email" class="form-control" value="{{ isset($student_details) ? $student_details->email : '' }}" name="email" id="email" placeholder="Enter email">
                              @error('email')
                               <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                              @enderror

                            </div>
                            <div class="form-group">
                                <label for="mobile">Mobile</label>
                                <input type="number" class="form-control" value="{{ isset($student_details) ? $student_details->mobile : '' }}" name="mobile" id="mobile" placeholder="Enter mobile">
                                @error('mobile')
                                <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                              <label for="dob">DOB</label>
                              <input type="date" class="form-control" value="{{ isset($student_details) ? $student_details->dob : '' }}" name="dob" id="dob">
                              @error('dob')
                              <span class="text-danger" style="font-size: 0.875rem;">{{ $message }}</span>
                             @enderror
                            </div>
                        </div>
                        <hr/>
                        <div class="input-group">
                            <div class="form-group col-md-4">
                                <label for="AddressType">Address Type</label>
                                <select name="address_types_id" id="addressTypes" >
                                    <option value="">-- Select Address Type --</option>
                                    @foreach ($address_types as $type)
                                     <option value="{{ $type->id }}">{{ $type->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="addressDetails">Address Details</label>
                                <textarea type="text" class="form-control" name="address_details" id="addressDetails"></textarea>
                            </div>
                            <div class="col-md-1">
                                <label for="row">Row</label>
                                <button type="button" id="row" class="btn btn-sm btn-primary">Row</button>
                            </div>
                            <div class="col-md-12 table-group">
                                <table id="myTable" class="table">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Address Type</th>
                                            <th>Address Details</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($student_details) && isset($student_addresses))
                                        @foreach ($student_addresses as $address)
                                            <tr>
                                                <td>
                                                    {{ $address->address_types_id }}
                                                </td>
                                                <td>
                                                    {{ $address->type }}
                                                    <input type="hidden" value="{{ $address->address_types_id }}" name="address_types_id[]">
                                                </td>
                                                <td>
                                                    {{ $address->address_details }}
                                                    <input type="hidden" value="{{ $address->address_details }}" name="address_details[]">
                                                </td>
                                                <td>
                                                    <button class="btn remove-btn">Remove</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm {{ isset($student_details) ? 'btn-success' : 'btn-primary' }}">{{ isset($student_details) ? 'Update' : 'Create' }}</button>
                      </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    let rowCount = 0;

    $('#row').click(function (e) {
        e.preventDefault();
        const addressTypeId = $('#addressTypes').val();
        const addressType = $('#addressTypes option:selected').text();
        const addressDetails = $('#addressDetails').val();

        const table = $('#myTable').DataTable();
        let isDuplicate = false;
        table.rows().every(function () {
            const row = this.node();
            const existingAddressTypeId = $(row).find('input[name="address_types_id[]"]').val();

            if (existingAddressTypeId === addressTypeId) {
                isDuplicate = true;
                return false;
            }
        });

        if (isDuplicate) {
            alert('This address type already exists in the table.');
            return;
        }
        table.row.add([
            table.row().count() + 1,
            `<diV><input type="hidden" value="${addressTypeId}" class="form-control" name="address_types_id[]"/>${addressType}</diV>`,
            `<diV><input type="hidden" value="${addressDetails}" class="form-control" name="address_details[]"/>${addressDetails}</diV>`,
            `<button class="btn remove-btn">Remove</button>`
        ]).draw(false);

        $('#addressTypes').val('');
        $('#addressDetails').val('');
    });

    $('#myTable').on('click', '.remove-btn', function () {
        const table = $('#myTable').DataTable();
        table.row($(this).parents('tr')).remove().draw();
    });

});
</script>
@endsection
