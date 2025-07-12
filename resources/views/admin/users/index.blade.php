{{-- filepath: c:\xampp\htdocs\projectcafelaravel10\resources\views\admin\users\index.blade.php --}}
@extends('admin.layouts.admin')

@section('title', 'จัดการผู้ใช้')

@push('styles')
<link href="{{ asset('css/admin/users.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">จัดการผู้ใช้</h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-user-plus"></i> เพิ่มผู้ใช้ใหม่
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">ผู้ใช้ทั้งหมด</h6>
            <div class="d-flex gap-2">
                {{-- ฟิลเตอร์สถานะ --}}
                <select class="form-select" id="status-filter">
                    <option value="">ทุกสถานะ</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>ใช้งาน</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>ไม่ใช้งาน</option>
                </select>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>ชื่อ</th>
                            <th>อีเมล</th>
                            <th>เบอร์โทร</th>
                            <th>สถานะ</th>
                            <th>วันที่สมัคร</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                                        {{ $user->status === 'active' ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="btn btn-sm btn-info" title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="btn btn-sm btn-primary" title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">ไม่พบข้อมูลผู้ใช้</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{-- Bootstrap Pagination (ใช้ตรงนี้) --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $users->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ฟิลเตอร์สถานะผู้ใช้ (เปลี่ยน dropdown แล้วเปลี่ยนหน้า)
    $(document).ready(function() {
        $('#status-filter').change(function() {
            let url = "{{ route('admin.users.index') }}";
            let params = [];
            if($(this).val() !== '') {
                params.push('status=' + $(this).val());
            }
            // preserve other query params if needed
            window.location.href = url + (params.length ? '?' + params.join('&') : '');
        });
    });
</script>
@endpush