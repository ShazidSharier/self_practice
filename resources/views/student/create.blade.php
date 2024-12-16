@extends('layouts.app')
@section('link')
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
@endsection
@section('content')
<div class="container">
    <div class="row col-md-12">
        <div class="card">
            <div class="card-header">Student Information {{ $form_type }}</div>
            <div class="card-body">
                <a href="{{ route('student.index') }}">Index</a>
                <p style="color: green">{{ session('message') }}</p>
                <form action="{{  isset($student_details) ? route('student.update', $student_details->id) : route('student.store') }}" method="POST">
                    @if (isset($student_details))
                    @method('PUT')
                    @endif
                    @csrf
                    <div>
                        <div class="input-group">
                            <div class="form-group col-md-6">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" value="{{ isset($student_details) ? $student_details->name : ''}}" class="form-control" placeholder="Enter Name" />
                                @error('name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="mobile">Mobile</label>
                                <input type="number" name="mobile" id="mobile" value="{{ isset($student_details) ? $student_details->mobile : '' }}" class="form-control" placeholder="Enter mobile" />
                                @error('mobile')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" value="{{ isset($student_details) ? $student_details->email : '' }}" class="form-control" placeholder="Enter email" />
                                @error('email')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="dob">DOB</label>
                                <input type="date" name="dob" id="dob" value="{{ isset($student_details) ? $student_details->dob : '' }}" class="form-control" placeholder="Enter dob" />
                                @error('dob')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <hr/>
                        <div class="input-group">
                            <div class="form-group col-md-4">
                                <label for="addressTypes">Address Types</label>
                                <select name="address_types_id" id="addressTypes">
                                    <option value=""> ---Select Type---</option>
                                    @foreach ($address_types as $value)
                                    <option value="{{ $value->id }}">{{ $value->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="addressDetails">Address Details</label>
                                <textarea type="text" name="address_details" class="form-control" id="addressDetails"></textarea>
                            </div>
                            <div class="form-group col-md-1">
                                <label for="row">Row</label>
                                <button id="row">Add</button>
                            </div>
                        </div>
                        <div class='col-md-12'>
                            <table id="student" class="table">
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
                                    @foreach ($student_addresses as $address )
                                    <tr>
                                        <td>{{ $address->address_types_id }}</td>
                                        <td>
                                            {{ $address->type }}
                                            <input type="hidden" name="address_types[]" value ="{{ $address->address_types_id }}"/>
                                        </td>
                                        <td>{{ $address->address_details }}
                                            <input type="hidden" name="address_details[]" value ="{{ $address->address_details }}"/>
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
                        <button type="submit" class="btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script>
    $(document).ready(function () {
        let rowCount = 0;
        const table = $('#student').DataTable();
        const savedRows = JSON.parse(localStorage.getItem('tableRows')) || [];
        savedRows.forEach((row) => {
            table.row.add(row).draw(false);
        });

        $('#row').click(function (e) {
            e.preventDefault();
            const addressTypesId = $('#addressTypes option:selected').val();
            const addressTypes = $('#addressTypes option:selected').text();
            const addressDetails = $('#addressDetails').val();
            const newRow = [
                `<div><input type="hidden" name="address_types_id[]" value="${addressTypesId}" class="form-control"/>${addressTypesId}</div>`,
                `<div><input type="hidden" name="address_types[]" value="${addressTypesId}" class="form-control"/>${addressTypes}</div>`,
                `<div><input type="hidden" name="address_details[]" value="${addressDetails}" class="form-control"/>${addressDetails}</div>`,
                `<button class="btn remove-btn">Remove</button>`
            ];
            table.row.add(newRow).draw(false);
            savedRows.push(newRow);
            localStorage.setItem('tableRows', JSON.stringify(savedRows));

            $('#addressTypes').val('');
            $('#addressDetails').val('');
        });

        $('#student').on('click', '.remove-btn', function () {
            const table = $('#student').DataTable();
            table.row($(this).parents('tr')).remove().draw(false);

            const allRows = [];
            table.rows().every(function () {
                allRows.push(this.data());
            });
            localStorage.setItem('tableRows', JSON.stringify(allRows));
        });

    });
</script>

@endsection
