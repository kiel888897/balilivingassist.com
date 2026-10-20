@extends('admin.layout')

@section('title', 'Contacts')
@section('page_title', 'Customer contacts')
@section('page_description', 'Manage customer names, phone numbers, email addresses and saved addresses.')

@section('page_actions')
<a class="btn" href="{{ route('admin.contacts.create') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add customer</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-heading">
        <h2>Contacts</h2>
        <span class="muted">{{ $customers->count() }} customers</span>
    </div>
    <div class="data-wrap">
        <table class="data-table" data-searchable>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Addresses</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                <tr>
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td>{{ $customer->phone ?: '—' }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->addresses_count }}</td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-light btn-icon" href="{{ route('admin.contacts.edit', $customer) }}" aria-label="Edit customer: {{ $customer->name }}" title="Edit customer">
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>
                            <form action="{{ route('admin.contacts.destroy', $customer) }}" method="POST" onsubmit="return confirm('Delete this customer account and its saved addresses? Existing quotation records will be retained.')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-icon" type="submit" aria-label="Delete customer: {{ $customer->name }}" title="Delete customer">
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="muted">No customer contacts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
