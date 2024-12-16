@extends('layouts.app')
@section('link')
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
@endsection
@section('content')
<div class="container">
    <div class="row col-md-12">
        <div class="card">
            <div class="card-header">Student Information</div>
            <div class="card-body">
                <a href="{{ route('student.create') }}">Create</a>
                <p style="color: green">{{ session('message') }}</p>
                <table class="table" id="student">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Dob</th>
                            <th>Created At</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
<script>
    function format(d) {
        return (
            '<dl>' +
            '<dt>Address Details:</dt>' +
            '<dd>' + d.address_details + '</dd>' +
            '</dl>'
        );
    }

    let table = new DataTable('#student', {
        ajax: '{{ route('get-student-data') }}',
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: ''
            },
            { data: 'name' },
            { data: 'code' },
            { data: 'email' },
            { data: 'mobile' },
            { data: 'dob' },
            { data: 'created_at' },
            // { data: 'address_details' },
            {
                data: null,
                render: function (data, type, row) {
                    const editUrl = '{{ route("student.edit", ":id") }}'.replace(":id", data.id);
                    const deleteUrl = '{{ route("student.destroy", ":id") }}' .replace(":id", data.id);
                    const csrfToken = '{{ csrf_token() }}';
                    return `
                        <a href="${editUrl}" class="btn btn-edit" data-id="${data.id}">Edit</a>
                         <form action="${deleteUrl}" method="POST" style="display:inline;">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn delete-btn" data-id="${data.id}">Delete</button>
                         </form>
                    `;
                }
            }
        ],
        order: [[1, 'asc']],
    });
        table.on('click', 'tbody td.dt-control', function (e) {
            let tr = e.target.closest('tr');
            let row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
            }
            else {
                row.child(format(row.data())).show();
            }
        });

    </script>

@endsection
