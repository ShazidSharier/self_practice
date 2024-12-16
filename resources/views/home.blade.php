@extends('layouts.app')
@section('link')
{{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"> --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>
                <a href="{{ route('create') }}">Create</a>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
                <p style="color:green">{{ session('message') }}</p>
                <div class="col-md-12">
                    <table class="Display" id="myTable"  >
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Dob</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
{{-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> --}}
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

let table = new DataTable('#myTable', {
    ajax: '{{ route('get-data') }}',
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
        {
            data: null,
            render: function (data, type, row) {
                const editUrl = '{{ route("edit", ":id") }}'.replace(":id", data.id);
                return `
                    <a href="${editUrl}" class="btn btn-edit" data-id="${data.id}">Edit</a>
                     <button type="submit" class="btn delete-btn" data-id="${data.id}">Delete</button>
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
    $('#myTable').on('click', '.delete-btn', function () {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to delete ID: ' + id + '?')) {
            $.ajax({
                url: '{{ route('delete', ':id') }}'.replace(':id', id),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    alert('Deleted successfully!');
                    $('#myTable').DataTable().ajax.reload();
                },
                error: function (xhr, status, error) {
                    alert('Error deleting the record: ' + error);
                }
            });
        }
    });

</script>
@endsection
